<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_has_fillable_attributes()
    {
        $fillable = ['name'];
        $role = new Role();
        
        $this->assertEquals($fillable, $role->getFillable());
    }

    public function test_role_has_many_group_members()
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $role = Role::find(GroupMember::ROLE_ADMIN);
        $user = User::factory()->create();
        $group = Group::factory()->create();
        
        GroupMember::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'role_id' => GroupMember::ROLE_ADMIN,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $role->group_members);
        $this->assertCount(1, $role->group_members);
    }

    public function test_role_can_be_created_with_name()
    {
        $role = Role::create(['name' => 'テスト役割']);

        $this->assertDatabaseHas('roles', [
            'name' => 'テスト役割',
        ]);
    }

    public function test_role_name_is_required()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Role::create([]);
    }

    public function test_standard_roles_can_be_created()
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $adminRole = Role::find(GroupMember::ROLE_ADMIN);
        $memberRole = Role::find(GroupMember::ROLE_MEMBER);

        $this->assertDatabaseHas('roles', ['name' => 'アドミン']);
        $this->assertDatabaseHas('roles', ['name' => 'メンバー']);
        $this->assertNotEquals($adminRole->id, $memberRole->id);
    }

    public function test_role_relationships_work_correctly()
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $adminRole = Role::find(GroupMember::ROLE_ADMIN);
        $memberRole = Role::find(GroupMember::ROLE_MEMBER);
        
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $group = Group::factory()->create();
        
        GroupMember::create([
            'user_id' => $user1->id,
            'group_id' => $group->id,
            'role_id' => GroupMember::ROLE_ADMIN,
        ]);
        
        GroupMember::create([
            'user_id' => $user2->id,
            'group_id' => $group->id,
            'role_id' => GroupMember::ROLE_MEMBER,
        ]);

        // 管理者ロールに1人、メンバーロールに1人のユーザーが紐づいている
        $this->assertCount(1, $adminRole->group_members);
        $this->assertCount(1, $memberRole->group_members);
    }

    public function test_role_can_have_multiple_group_members()
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $role = Role::find(GroupMember::ROLE_MEMBER);
        $group = Group::factory()->create();
        
        // 同じロールで複数のユーザーを作成
        for ($i = 0; $i < 3; $i++) {
            $user = User::factory()->create();
            GroupMember::create([
                'user_id' => $user->id,
                'group_id' => $group->id,
                'role_id' => GroupMember::ROLE_MEMBER,
            ]);
        }

        $this->assertCount(3, $role->group_members);
    }

    public function test_role_deletion_affects_group_members()
    {
        $role = Role::create(['name' => 'テスト役割']);
        $user = User::factory()->create();
        $group = Group::factory()->create();
        
        $groupMember = GroupMember::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'role_id' => $role->id,
        ]);

        // ロールを削除する前にGroupMemberが存在することを確認
        $this->assertDatabaseHas('group_members', [
            'id' => $groupMember->id,
            'role_id' => $role->id,
        ]);

        // ロールを削除（外部キー制約によりエラーが発生することを確認）
        $this->expectException(\Illuminate\Database\QueryException::class);
        $role->delete();
    }
}