<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupMemberModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_group_member_has_fillable_attributes()
    {
        $fillable = ['user_id', 'group_id', 'role_id'];
        $groupMember = new GroupMember();
        
        $this->assertEquals($fillable, $groupMember->getFillable());
    }

    public function test_group_member_has_role_constants()
    {
        $this->assertEquals(1, GroupMember::ROLE_ADMIN);
        $this->assertEquals(2, GroupMember::ROLE_MEMBER);
    }

    public function test_group_member_belongs_to_user()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        
        $groupMember = GroupMember::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'role_id' => GroupMember::ROLE_ADMIN,
        ]);

        $this->assertInstanceOf(User::class, $groupMember->user);
        $this->assertEquals($user->id, $groupMember->user->id);
    }

    public function test_group_member_belongs_to_group()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        
        $groupMember = GroupMember::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'role_id' => GroupMember::ROLE_ADMIN,
        ]);

        $this->assertInstanceOf(Group::class, $groupMember->group);
        $this->assertEquals($group->id, $groupMember->group->id);
    }

    public function test_group_member_belongs_to_role()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        
        $groupMember = GroupMember::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'role_id' => GroupMember::ROLE_ADMIN,
        ]);

        $this->assertInstanceOf(Role::class, $groupMember->role);
        $this->assertEquals(GroupMember::ROLE_ADMIN, $groupMember->role->id);
    }

    public function test_group_member_can_be_created_with_all_attributes()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        
        $groupMember = GroupMember::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'role_id' => GroupMember::ROLE_ADMIN,
        ]);

        $this->assertDatabaseHas('group_members', [
            'user_id' => $user->id,
            'group_id' => $group->id,
            'role_id' => GroupMember::ROLE_ADMIN,
        ]);
    }

    public function test_unique_user_group_combination()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        
        // 最初のGroupMemberを作成
        GroupMember::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'role_id' => GroupMember::ROLE_ADMIN,
        ]);

        // 同じuser_id, group_idの組み合わせで作成を試行
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        GroupMember::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'role_id' => GroupMember::ROLE_ADMIN,
        ]);
    }

    public function test_role_constants_are_valid_integers()
    {
        $this->assertIsInt(GroupMember::ROLE_ADMIN);
        $this->assertIsInt(GroupMember::ROLE_MEMBER);
        $this->assertNotEquals(GroupMember::ROLE_ADMIN, GroupMember::ROLE_MEMBER);
    }
}