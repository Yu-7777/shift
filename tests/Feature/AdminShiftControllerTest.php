<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Role;
use App\Models\Shift;
use App\Models\ShiftSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminShiftControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $member;
    protected $group;
    protected $adminRole;
    protected $memberRole;

    protected function setUp(): void
    {
        parent::setUp();

        // ロール作成
        $this->adminRole = Role::create(['name' => '管理者']);
        $this->memberRole = Role::create(['name' => 'メンバー']);

        // ユーザー作成
        $this->admin = User::factory()->create();
        $this->member = User::factory()->create();

        // グループ作成
        $this->group = Group::factory()->create();

        // グループメンバー追加  
        GroupMember::create([
            'user_id' => $this->admin->id,
            'group_id' => $this->group->id,
            'role_id' => $this->adminRole->id
        ]);

        GroupMember::create([
            'user_id' => $this->member->id,
            'group_id' => $this->group->id,
            'role_id' => $this->memberRole->id
        ]);
    }

    public function test_admin_can_access_shift_creation_form()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.shifts.create', $this->group));

        $response->assertStatus(200);
        $response->assertViewIs('admin.shifts.create');
        $response->assertViewHas('group', $this->group);
    }

    public function test_non_admin_cannot_access_shift_creation_form()
    {
        $this->actingAs($this->member);

        $response = $this->get(route('admin.shifts.create', $this->group));

        $response->assertStatus(403);
    }

    public function test_admin_can_search_available_users()
    {
        $this->actingAs($this->admin);

        // 可用性を作成
        ShiftSubmission::create([
            'user_id' => $this->member->id,
            'group_id' => $this->group->id,
            'date' => '2025-07-01',
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => 'active',
        ]);

        $response = $this->post(route('admin.search-users', $this->group), [
            'date' => '2025-07-01',
            'start_time' => '10:00',
            'end_time' => '16:00',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'availableUsers' => [
                [
                    'id' => $this->member->id,
                    'name' => $this->member->name,
                    'available_start_time' => '09:00:00',
                    'available_end_time' => '17:00:00',
                ]
            ]
        ]);
    }

    public function test_search_available_users_validation()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.search-users', $this->group), [
            'date' => 'invalid-date',
            'start_time' => '25:00',
            'end_time' => '08:00', // end_time is before start_time
        ]);

        $response->assertStatus(302); // Validation error redirect
        $response->assertSessionHasErrors(['date', 'start_time', 'end_time']);
    }

    public function test_admin_can_create_shift_successfully()
    {
        $this->actingAs($this->admin);

        // 可用性を作成
        ShiftSubmission::create([
            'user_id' => $this->member->id,
            'group_id' => $this->group->id,
            'date' => '2025-07-01',
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => 'active',
        ]);

        $response = $this->post(route('admin.shifts.store', $this->group), [
            'date' => '2025-07-01',
            'start_time' => '10:00',
            'end_time' => '16:00',
            'user_id' => $this->member->id,
        ]);

        $response->assertRedirect(route('groups.show', $this->group));
        $response->assertSessionHas('success', 'シフトを作成しました');

        $this->assertDatabaseHas('shifts', [
            'group_id' => $this->group->id,
            'user_id' => $this->member->id,
            'start_time' => '2025-07-01 10:00:00',
            'end_time' => '2025-07-01 16:00:00',
        ]);
    }

    public function test_shift_creation_fails_when_user_not_available()
    {
        $this->actingAs($this->admin);

        // 異なる時間帯の可用性を作成
        ShiftSubmission::create([
            'user_id' => $this->member->id,
            'group_id' => $this->group->id,
            'date' => '2025-07-01',
            'available_start_time' => '14:00:00',
            'available_end_time' => '17:00:00',
            'status' => 'active',
        ]);

        $response = $this->post(route('admin.shifts.store', $this->group), [
            'date' => '2025-07-01',
            'start_time' => '10:00',
            'end_time' => '13:00',
            'user_id' => $this->member->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', '選択されたユーザーはその時間帯に利用できません');

        $this->assertDatabaseMissing('shifts', [
            'group_id' => $this->group->id,
            'user_id' => $this->member->id,
            'start_time' => '2025-07-01 10:00:00',
            'end_time' => '2025-07-01 13:00:00',
        ]);
    }

    public function test_shift_creation_fails_with_time_conflict()
    {
        $this->actingAs($this->admin);

        // 既存のシフトを作成
        Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->member->id,
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
        ]);

        // 可用性を作成
        ShiftSubmission::create([
            'user_id' => $this->member->id,
            'group_id' => $this->group->id,
            'date' => '2025-07-01',
            'available_start_time' => '08:00:00',
            'available_end_time' => '20:00:00',
            'status' => 'active',
        ]);

        $response = $this->post(route('admin.shifts.store', $this->group), [
            'date' => '2025-07-01',
            'start_time' => '10:00',
            'end_time' => '16:00',
            'user_id' => $this->member->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', '選択されたユーザーは指定時間に既に他のシフトが入っています');
    }

    public function test_shift_creation_validation()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.shifts.store', $this->group), [
            'date' => 'invalid-date',
            'start_time' => '',
            'end_time' => '', // 空にしてrequiredエラーを発生させる
            'user_id' => 'invalid-user',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['date', 'start_time', 'end_time', 'user_id']);
    }

    public function test_admin_can_view_shifts_index()
    {
        $this->actingAs($this->admin);

        $futureShift = Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->member->id,
            'start_time' => now()->addDays(1)->setTime(10, 0),
            'end_time' => now()->addDays(1)->setTime(16, 0),
        ]);

        $pastShift = Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->member->id,
            'start_time' => now()->subDays(1)->setTime(10, 0),
            'end_time' => now()->subDays(1)->setTime(16, 0),
        ]);

        $response = $this->get(route('admin.shifts.index', $this->group));

        $response->assertStatus(200);
        $response->assertViewIs('admin.shifts.index');
        $response->assertViewHas('shifts');
        
        // 未来のシフトのみ表示されることを確認
        $shifts = $response->viewData('shifts');
        $this->assertTrue($shifts->contains('id', $futureShift->id));
        $this->assertFalse($shifts->contains('id', $pastShift->id));
    }

    public function test_admin_can_view_availabilities()
    {
        $this->actingAs($this->admin);

        $today = now()->format('Y-m-d');
        $yesterday = now()->subDays(1)->format('Y-m-d');

        $todayAvailability = ShiftSubmission::create([
            'user_id' => $this->member->id,
            'group_id' => $this->group->id,
            'date' => $today,
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => 'active',
        ]);

        $pastAvailability = ShiftSubmission::create([
            'user_id' => $this->member->id,
            'group_id' => $this->group->id,
            'date' => $yesterday,
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => 'active',
        ]);

        $response = $this->get(route('admin.availabilities.index', $this->group));

        $response->assertStatus(200);
        $response->assertViewIs('admin.availabilities.index');
        
        // 今日以降の可用性のみ表示されることを確認
        $availabilities = $response->viewData('availabilities');
        $this->assertArrayHasKey($today, $availabilities->toArray());
        $this->assertArrayNotHasKey($yesterday, $availabilities->toArray());
    }

    public function test_admin_can_delete_shift()
    {
        $this->actingAs($this->admin);

        $shift = Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->member->id,
            'start_time' => '2025-07-01 10:00:00',
            'end_time' => '2025-07-01 16:00:00',
        ]);

        $response = $this->delete(route('admin.shifts.destroy', [$this->group, $shift]));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'シフトを削除しました');
        $this->assertDatabaseMissing('shifts', ['id' => $shift->id]);
    }

    public function test_cannot_delete_shift_from_different_group()
    {
        $this->actingAs($this->admin);

        $otherGroup = Group::factory()->create();
        $shift = Shift::create([
            'group_id' => $otherGroup->id,
            'user_id' => $this->member->id,
            'start_time' => '2025-07-01 10:00:00',
            'end_time' => '2025-07-01 16:00:00',
        ]);

        $response = $this->delete(route('admin.shifts.destroy', [$this->group, $shift]));

        $response->assertStatus(404);
        $this->assertDatabaseHas('shifts', ['id' => $shift->id]);
    }

    public function test_non_admin_cannot_access_admin_routes()
    {
        $this->actingAs($this->member);

        $routes = [
            ['GET', route('admin.shifts.index', $this->group)],
            ['GET', route('admin.shifts.create', $this->group)],
            ['POST', route('admin.shifts.store', $this->group)],
            ['GET', route('admin.availabilities.index', $this->group)],
            ['POST', route('admin.search-users', $this->group)],
        ];

        foreach ($routes as [$method, $url]) {
            $response = $this->call($method, $url, [
                'date' => '2025-07-01',
                'start_time' => '10:00',
                'end_time' => '16:00',
                'user_id' => $this->member->id,
            ]);

            $response->assertStatus(403, "Failed for {$method} {$url}");
        }
    }

    public function test_guest_cannot_access_admin_routes()
    {
        $routes = [
            ['GET', route('admin.shifts.index', $this->group)],
            ['GET', route('admin.shifts.create', $this->group)],
            ['POST', route('admin.shifts.store', $this->group)],
            ['GET', route('admin.availabilities.index', $this->group)],
            ['POST', route('admin.search-users', $this->group)],
        ];

        foreach ($routes as [$method, $url]) {
            $response = $this->call($method, $url, [
                'date' => '2025-07-01',
                'start_time' => '10:00',
                'end_time' => '16:00',
                'user_id' => $this->member->id,
            ]);

            $response->assertRedirect('/login');
        }
    }

    public function test_search_users_filters_by_availability_time()
    {
        $this->actingAs($this->admin);

        // 利用可能な時間が短いユーザー
        $earlyUser = User::factory()->create();
        GroupMember::create([
            'user_id' => $earlyUser->id,
            'group_id' => $this->group->id,
            'role_id' => $this->memberRole->id,
        ]);

        ShiftSubmission::create([
            'user_id' => $earlyUser->id,
            'group_id' => $this->group->id,
            'date' => '2025-07-01',
            'available_start_time' => '09:00:00',
            'available_end_time' => '12:00:00',
            'status' => 'active',
        ]);

        // 利用可能な時間が長いユーザー
        ShiftSubmission::create([
            'user_id' => $this->member->id,
            'group_id' => $this->group->id,
            'date' => '2025-07-01',
            'available_start_time' => '08:00:00',
            'available_end_time' => '18:00:00',
            'status' => 'active',
        ]);

        $response = $this->post(route('admin.search-users', $this->group), [
            'date' => '2025-07-01',
            'start_time' => '10:00',
            'end_time' => '15:00',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        
        // 時間帯が合う方のユーザーのみ返される
        $this->assertCount(1, $data['availableUsers']);
        $this->assertEquals($this->member->id, $data['availableUsers'][0]['id']);
    }

    public function test_search_users_excludes_inactive_submissions()
    {
        $this->actingAs($this->admin);

        // アクティブな可用性
        ShiftSubmission::create([
            'user_id' => $this->member->id,
            'group_id' => $this->group->id,
            'date' => '2025-07-01',
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => 'active',
        ]);

        // 非アクティブな可用性
        $inactiveUser = User::factory()->create();
        GroupMember::create([
            'user_id' => $inactiveUser->id,
            'group_id' => $this->group->id,
            'role_id' => $this->memberRole->id,
        ]);

        ShiftSubmission::create([
            'user_id' => $inactiveUser->id,
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

        $response->assertStatus(200);
        $data = $response->json();
        
        // アクティブなユーザーのみ返される
        $this->assertCount(1, $data['availableUsers']);
        $this->assertEquals($this->member->id, $data['availableUsers'][0]['id']);
    }
}
