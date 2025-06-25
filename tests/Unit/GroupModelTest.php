<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Role;
use App\Models\Shift;
use App\Models\ShiftRequest;
use App\Models\ShiftSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_group_has_fillable_attributes()
    {
        $fillable = ['name'];
        $group = new Group();
        
        $this->assertEquals($fillable, $group->getFillable());
    }

    public function test_group_has_many_group_members()
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();
        
        GroupMember::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'role_id' => GroupMember::ROLE_ADMIN,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $group->groupMembers);
        $this->assertCount(1, $group->groupMembers);
    }

    public function test_group_has_many_shifts()
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();
        
        Shift::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $group->shifts);
        $this->assertCount(1, $group->shifts);
    }

    public function test_group_has_many_shift_requests()
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();
        
        ShiftRequest::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'title' => 'テストシフト募集',
            'description' => 'テスト用のシフト募集です',
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
            'requested_people' => 1,
            'application_deadline' => '2025-06-30 23:59:59',
            'status' => 'open',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $group->shiftRequests);
        $this->assertCount(1, $group->shiftRequests);
    }

    public function test_group_has_many_shift_submissions()
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();
        
        ShiftSubmission::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'date' => '2025-07-01',
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => 'active',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $group->shiftSubmissions);
        $this->assertCount(1, $group->shiftSubmissions);
    }

    public function test_group_members_relationship_includes_user_and_role_data()
    {
        $group = Group::factory()->create();
        $user = User::factory()->create(['name' => 'テストユーザー']);
        
        GroupMember::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'role_id' => GroupMember::ROLE_ADMIN,
        ]);

        $members = $group->groupMembers()->with(['user', 'role'])->get();
        
        $this->assertCount(1, $members);
        $this->assertEquals('テストユーザー', $members->first()->user->name);
        $this->assertEquals('アドミン', $members->first()->role->name);
    }

    public function test_group_can_be_created_with_name()
    {
        $group = Group::create([
            'name' => 'テストグループ',
        ]);

        $this->assertDatabaseHas('groups', [
            'name' => 'テストグループ',
        ]);
    }

    public function test_group_name_is_required()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Group::create([]);
    }

    public function test_group_can_be_created_with_just_name()
    {
        $group = Group::create([
            'name' => 'テストグループ',
        ]);

        $this->assertDatabaseHas('groups', [
            'name' => 'テストグループ',
        ]);
    }

    public function test_group_can_have_multiple_members_with_different_roles()
    {
        $group = Group::factory()->create();
        $admin = User::factory()->create();
        $member = User::factory()->create();
        
        GroupMember::create([
            'user_id' => $admin->id,
            'group_id' => $group->id,
            'role_id' => GroupMember::ROLE_ADMIN,
        ]);
        
        GroupMember::create([
            'user_id' => $member->id,
            'group_id' => $group->id,
            'role_id' => GroupMember::ROLE_MEMBER,
        ]);

        $this->assertCount(2, $group->groupMembers);
    }
}