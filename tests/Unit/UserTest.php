<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Role;
use App\Models\Shift;
use App\Models\ShiftSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_fillable_attributes()
    {
        $fillable = ['name', 'email', 'password'];
        $user = new User();
        
        $this->assertEquals($fillable, $user->getFillable());
    }

    public function test_user_has_hidden_attributes()
    {
        $hidden = ['password', 'remember_token'];
        $user = new User();
        
        $this->assertEquals($hidden, $user->getHidden());
    }

    public function test_user_password_is_cast_to_hashed()
    {
        $user = new User();
        $casts = $user->getCasts();
        
        $this->assertEquals('hashed', $casts['password']);
    }

    public function test_user_can_have_multiple_groups()
    {
        $user = User::factory()->create();
        $group1 = Group::factory()->create();
        $group2 = Group::factory()->create();
        $role = Role::create(['name' => 'メンバー']);
        
        GroupMember::create([
            'user_id' => $user->id,
            'group_id' => $group1->id,
            'role_id' => $role->id,
        ]);
        
        GroupMember::create([
            'user_id' => $user->id,
            'group_id' => $group2->id,
            'role_id' => $role->id,
        ]);

        $this->assertCount(2, $user->groupMembers);
    }

    public function test_user_can_have_shifts()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        
        Shift::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
        ]);

        $this->assertCount(1, $user->shifts);
        $this->assertInstanceOf(Shift::class, $user->shifts->first());
    }

    public function test_user_can_have_shift_submissions()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        
        ShiftSubmission::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'date' => '2025-07-01',
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => 'active',
        ]);

        $this->assertCount(1, $user->shiftSubmissions);
        $this->assertInstanceOf(ShiftSubmission::class, $user->shiftSubmissions->first());
    }

    public function test_get_shift_groups_method_returns_paginated_groups()
    {
        $user = User::factory()->create();
        $groups = Group::factory()->count(15)->create();
        $role = Role::create(['name' => 'メンバー']);
        
        // ユーザーを15個のグループに追加
        foreach ($groups as $group) {
            GroupMember::create([
                'user_id' => $user->id,
                'group_id' => $group->id,
                'role_id' => $role->id,
            ]);
        }

        $shiftGroups = $user->getShiftGroups();

        // ページネーションで10件取得されることを確認
        $this->assertCount(10, $shiftGroups);
        
        // グループメンバー数が設定されていることを確認
        foreach ($shiftGroups as $group) {
            $this->assertNotNull($group->group_members_count);
            $this->assertEquals(1, $group->group_members_count);
        }
    }

    public function test_user_can_be_created_with_factory()
    {
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_email_is_unique()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        User::factory()->create(['email' => 'test@example.com']);
    }

    public function test_user_relationship_with_group_members_includes_role()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $adminRole = Role::create(['name' => '管理者']);
        
        GroupMember::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'role_id' => $adminRole->id,
        ]);

        $groupMember = $user->groupMembers()->with('role')->first();
        
        $this->assertEquals('管理者', $groupMember->role->name);
    }

    public function test_user_can_have_different_roles_in_different_groups()
    {
        $user = User::factory()->create();
        $group1 = Group::factory()->create();
        $group2 = Group::factory()->create();
        $adminRole = Role::create(['name' => '管理者']);
        $memberRole = Role::create(['name' => 'メンバー']);
        
        GroupMember::create([
            'user_id' => $user->id,
            'group_id' => $group1->id,
            'role_id' => $adminRole->id,
        ]);
        
        GroupMember::create([
            'user_id' => $user->id,
            'group_id' => $group2->id,
            'role_id' => $memberRole->id,
        ]);

        $groupMembers = $user->groupMembers()->with('role')->get();
        
        $this->assertCount(2, $groupMembers);
        $this->assertTrue($groupMembers->contains('role.name', '管理者'));
        $this->assertTrue($groupMembers->contains('role.name', 'メンバー'));
    }
}
