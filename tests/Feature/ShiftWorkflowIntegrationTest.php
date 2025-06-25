<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Role;
use App\Models\Shift;
use App\Models\ShiftSubmission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftWorkflowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $member1;
    protected $member2;
    protected $group;

    protected function setUp(): void
    {
        parent::setUp();

        // シーダーを実行してRoleを作成
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->member1 = User::factory()->create(['email' => 'member1@test.com']);
        $this->member2 = User::factory()->create(['email' => 'member2@test.com']);
        $this->group = Group::factory()->create();

        GroupMember::create([
            'user_id' => $this->admin->id,
            'group_id' => $this->group->id,
            'role_id' => GroupMember::ROLE_ADMIN
        ]);

        GroupMember::create([
            'user_id' => $this->member1->id,
            'group_id' => $this->group->id,
            'role_id' => GroupMember::ROLE_MEMBER
        ]);

        GroupMember::create([
            'user_id' => $this->member2->id,
            'group_id' => $this->group->id,
            'role_id' => GroupMember::ROLE_MEMBER
        ]);
    }

    public function test_complete_shift_assignment_workflow()
    {
        $this->actingAs($this->admin);

        // 1. メンバーが可用性を提出
        ShiftSubmission::create([
            'user_id' => $this->member1->id,
            'group_id' => $this->group->id,
            'date' => '2025-07-01',
            'available_start_time' => '09:00:00',
            'available_end_time' => '18:00:00',
            'status' => 'active',
            'comment' => '朝早くから対応可能です',
        ]);

        ShiftSubmission::create([
            'user_id' => $this->member2->id,
            'group_id' => $this->group->id,
            'date' => '2025-07-01',
            'available_start_time' => '12:00:00',
            'available_end_time' => '20:00:00',
            'status' => 'active',
            'comment' => '午後から対応可能です',
        ]);

        // 2. 管理者が利用可能なユーザーを検索
        $response = $this->post(route('admin.search-users', $this->group), [
            'date' => '2025-07-01',
            'start_time' => '10:00',
            'end_time' => '16:00',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        
        // member1のみが利用可能（09:00-18:00は10:00-16:00を完全にカバー）
        // member2は利用不可（12:00-20:00は10:00-16:00の開始時間をカバーできない）
        $this->assertCount(1, $data['availableUsers']);
        $userIds = collect($data['availableUsers'])->pluck('id')->toArray();
        $this->assertContains($this->member1->id, $userIds);

        // 3. 管理者がメンバー1にシフトを割り当て
        $response = $this->post(route('admin.shifts.store', $this->group), [
            'date' => '2025-07-01',
            'start_time' => '10:00',
            'end_time' => '16:00',
            'user_id' => $this->member1->id,
        ]);

        $response->assertRedirect(route('groups.show', $this->group));
        $response->assertSessionHas('success', 'シフトを作成しました');

        // 4. シフトがデータベースに保存されていることを確認
        $this->assertDatabaseHas('shifts', [
            'group_id' => $this->group->id,
            'user_id' => $this->member1->id,
            'start_time' => '2025-07-01 10:00:00',
            'end_time' => '2025-07-01 16:00:00',
        ]);

        // 5. 同じユーザーに重複する時間のシフトは割り当てできない
        $response = $this->post(route('admin.shifts.store', $this->group), [
            'date' => '2025-07-01',
            'start_time' => '14:00',
            'end_time' => '18:00',
            'user_id' => $this->member1->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', '選択されたユーザーは指定時間に既に他のシフトが入っています');

        // 6. 別のユーザーには重複しない時間で割り当て可能
        $response = $this->post(route('admin.shifts.store', $this->group), [
            'date' => '2025-07-01',
            'start_time' => '17:00',
            'end_time' => '19:00',
            'user_id' => $this->member2->id,
        ]);

        $response->assertRedirect(route('groups.show', $this->group));
        $response->assertSessionHas('success', 'シフトを作成しました');
    }

    public function test_availability_filtering_workflow()
    {
        $this->actingAs($this->admin);

        // 異なる時間帯の可用性を設定
        ShiftSubmission::create([
            'user_id' => $this->member1->id,
            'group_id' => $this->group->id,
            'date' => '2025-07-01',
            'available_start_time' => '09:00:00',
            'available_end_time' => '13:00:00',
            'status' => 'active',
        ]);

        ShiftSubmission::create([
            'user_id' => $this->member2->id,
            'group_id' => $this->group->id,
            'date' => '2025-07-01',
            'available_start_time' => '14:00:00',
            'available_end_time' => '18:00:00',
            'status' => 'active',
        ]);

        // 午前の時間帯で検索
        $response = $this->post(route('admin.search-users', $this->group), [
            'date' => '2025-07-01',
            'start_time' => '10:00',
            'end_time' => '12:00',
        ]);

        $data = $response->json();
        $this->assertCount(1, $data['availableUsers']);
        $this->assertEquals($this->member1->id, $data['availableUsers'][0]['id']);

        // 午後の時間帯で検索
        $response = $this->post(route('admin.search-users', $this->group), [
            'date' => '2025-07-01',
            'start_time' => '15:00',
            'end_time' => '17:00',
        ]);

        $data = $response->json();
        $this->assertCount(1, $data['availableUsers']);
        $this->assertEquals($this->member2->id, $data['availableUsers'][0]['id']);

        // 跨ぐ時間帯で検索（該当なし）
        $response = $this->post(route('admin.search-users', $this->group), [
            'date' => '2025-07-01',
            'start_time' => '12:00',
            'end_time' => '15:00',
        ]);

        $data = $response->json();
        $this->assertCount(0, $data['availableUsers']);
    }

    public function test_shift_management_permissions()
    {
        // メンバーでは管理機能にアクセスできない
        $this->actingAs($this->member1);

        $response = $this->get(route('admin.shifts.create', $this->group));
        $response->assertStatus(403);

        $response = $this->post(route('admin.search-users', $this->group), [
            'date' => '2025-07-01',
            'start_time' => '10:00',
            'end_time' => '16:00',
        ]);
        $response->assertStatus(403);

        $response = $this->post(route('admin.shifts.store', $this->group), [
            'date' => '2025-07-01',
            'start_time' => '10:00',
            'end_time' => '16:00',
            'user_id' => $this->member2->id,
        ]);
        $response->assertStatus(403);
    }

    public function test_shift_deletion_workflow()
    {
        $this->actingAs($this->admin);

        // シフトを作成
        $shift = Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->member1->id,
            'start_time' => '2025-07-01 10:00:00',
            'end_time' => '2025-07-01 16:00:00',
        ]);

        // 管理者はシフトを削除できる
        $response = $this->delete(route('admin.shifts.destroy', [$this->group, $shift]));
        $response->assertRedirect();
        $response->assertSessionHas('success', 'シフトを削除しました');

        $this->assertDatabaseMissing('shifts', ['id' => $shift->id]);
    }

    public function test_inactive_submissions_are_not_searchable()
    {
        $this->actingAs($this->admin);

        // アクティブな可用性
        ShiftSubmission::create([
            'user_id' => $this->member1->id,
            'group_id' => $this->group->id,
            'date' => '2025-07-01',
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => 'active',
        ]);

        // 非アクティブな可用性
        ShiftSubmission::create([
            'user_id' => $this->member2->id,
            'group_id' => $this->group->id,
            'date' => '2025-07-01',
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => 'inactive',
        ]);

        $response = $this->post(route('admin.search-users', $this->group), [
            'date' => '2025-07-01',
            'start_time' => '10:00',
            'end_time' => '16:00',
        ]);

        $data = $response->json();
        $this->assertCount(1, $data['availableUsers']);
        $this->assertEquals($this->member1->id, $data['availableUsers'][0]['id']);
    }

    public function test_past_availabilities_are_not_displayed()
    {
        $this->actingAs($this->admin);

        // 今日の可用性
        ShiftSubmission::create([
            'user_id' => $this->member1->id,
            'group_id' => $this->group->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => 'active',
        ]);

        // 過去の可用性
        ShiftSubmission::create([
            'user_id' => $this->member2->id,
            'group_id' => $this->group->id,
            'date' => Carbon::yesterday()->format('Y-m-d'),
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => 'active',
        ]);

        $response = $this->get(route('admin.availabilities.index', $this->group));
        $response->assertStatus(200);

        $availabilities = $response->viewData('availabilities');
        $this->assertTrue($availabilities->has(Carbon::today()->format('Y-m-d')));
        $this->assertFalse($availabilities->has(Carbon::yesterday()->format('Y-m-d')));
    }
}