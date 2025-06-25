<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_users_index_displays_all_users()
    {
        // Create additional users
        $user2 = User::factory()->create(['name' => 'ユーザー2']);
        $user3 = User::factory()->create(['name' => 'ユーザー3']);

        $response = $this->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('users.index');
        $response->assertViewHas('users');

        $users = $response->viewData('users');
        $this->assertCount(3, $users); // Including setUp user

        $userNames = $users->pluck('name')->toArray();
        $this->assertContains($this->user->name, $userNames);
        $this->assertContains('ユーザー2', $userNames);
        $this->assertContains('ユーザー3', $userNames);
    }

    public function test_users_index_handles_no_users()
    {
        // Delete the setUp user
        $this->user->delete();

        $response = $this->get(route('users.index'));

        $response->assertStatus(200);
        $users = $response->viewData('users');
        $this->assertCount(0, $users);
    }

    public function test_user_show_displays_specific_user()
    {
        $testUser = User::factory()->create([
            'name' => 'テスト表示ユーザー',
            'email' => 'test@example.com',
        ]);

        $response = $this->get(route('users.show', $testUser));

        $response->assertStatus(200);
        $response->assertViewIs('users.show');
        $response->assertViewHas('user');

        $viewUser = $response->viewData('user');
        $this->assertEquals($testUser->id, $viewUser->id);
        $this->assertEquals($testUser->name, $viewUser->name);
        $this->assertEquals($testUser->email, $viewUser->email);
    }

    public function test_user_show_with_nonexistent_user_returns_404()
    {
        $response = $this->get('/users/999999');

        $response->assertStatus(404);
    }

    public function test_user_show_groups_displays_user_groups()
    {
        $role = Role::create(['name' => 'メンバー']);
        
        $group1 = Group::factory()->create(['name' => 'グループ1']);
        $group2 = Group::factory()->create(['name' => 'グループ2']);

        // Add user to groups
        GroupMember::create([
            'user_id' => $this->user->id,
            'group_id' => $group1->id,
            'role_id' => $role->id,
        ]);

        GroupMember::create([
            'user_id' => $this->user->id,
            'group_id' => $group2->id,
            'role_id' => $role->id,
        ]);

        $response = $this->get(route('users.groups', $this->user));

        $response->assertStatus(200);
        $response->assertViewIs('users.groups');
        $response->assertViewHasAll(['user', 'groups']);

        $viewUser = $response->viewData('user');
        $groups = $response->viewData('groups');

        $this->assertEquals($this->user->id, $viewUser->id);
        $this->assertCount(2, $groups);

        $groupNames = $groups->pluck('name')->toArray();
        $this->assertContains('グループ1', $groupNames);
        $this->assertContains('グループ2', $groupNames);
    }

    public function test_user_show_groups_handles_user_with_no_groups()
    {
        $response = $this->get(route('users.groups', $this->user));

        $response->assertStatus(200);
        $groups = $response->viewData('groups');
        $this->assertCount(0, $groups);
    }

    public function test_user_show_groups_includes_role_information()
    {
        $adminRole = Role::create(['name' => '管理者']);
        $memberRole = Role::create(['name' => 'メンバー']);
        
        $group1 = Group::factory()->create();
        $group2 = Group::factory()->create();

        GroupMember::create([
            'user_id' => $this->user->id,
            'group_id' => $group1->id,
            'role_id' => $adminRole->id,
        ]);

        GroupMember::create([
            'user_id' => $this->user->id,
            'group_id' => $group2->id,
            'role_id' => $memberRole->id,
        ]);

        $response = $this->get(route('users.groups', $this->user));

        $groups = $response->viewData('groups');
        $this->assertCount(2, $groups);

        // Check that pivot/role data is included
        foreach ($groups as $group) {
            $this->assertNotNull($group->pivot);
            $this->assertNotNull($group->pivot->role_id);
        }

        // Find specific groups and verify roles
        $group1Data = $groups->firstWhere('id', $group1->id);
        $group2Data = $groups->firstWhere('id', $group2->id);

        $this->assertEquals($adminRole->id, $group1Data->pivot->role_id);
        $this->assertEquals($memberRole->id, $group2Data->pivot->role_id);
    }

    public function test_user_show_groups_handles_many_groups()
    {
        $role = Role::create(['name' => 'メンバー']);
        
        // Create many groups for this user
        for ($i = 1; $i <= 15; $i++) {
            $group = Group::factory()->create(['name' => "大量グループ{$i}"]);
            GroupMember::create([
                'user_id' => $this->user->id,
                'group_id' => $group->id,
                'role_id' => $role->id,
            ]);
        }

        $response = $this->get(route('users.groups', $this->user));

        $groups = $response->viewData('groups');
        
        // Check that groups are properly loaded
        $this->assertTrue($groups->count() > 0);
        $this->assertLessThanOrEqual(15, $groups->count());
        
        // Verify that it's either a collection or paginated instance
        $this->assertTrue(
            $groups instanceof \Illuminate\Support\Collection || 
            $groups instanceof \Illuminate\Pagination\LengthAwarePaginator
        );
    }

    public function test_users_index_shows_page_content()
    {
        $testUser = User::factory()->create(['name' => '表示テストユーザー']);

        $response = $this->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertSee('表示テストユーザー');
    }

    public function test_user_show_displays_user_information()
    {
        $testUser = User::factory()->create([
            'name' => '詳細表示ユーザー',
            'email' => 'detail@example.com',
        ]);

        $response = $this->get(route('users.show', $testUser));

        $response->assertStatus(200);
        $response->assertSee('詳細表示ユーザー');
        $response->assertSee('detail@example.com');
    }

    public function test_user_show_groups_displays_user_and_group_names()
    {
        $role = Role::create(['name' => 'メンバー']);
        $group = Group::factory()->create(['name' => '表示テストグループ']);

        GroupMember::create([
            'user_id' => $this->user->id,
            'group_id' => $group->id,
            'role_id' => $role->id,
        ]);

        $response = $this->get(route('users.groups', $this->user));

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee('表示テストグループ');
    }

    public function test_user_routes_handle_different_users()
    {
        $user1 = User::factory()->create(['name' => 'ユーザー1']);
        $user2 = User::factory()->create(['name' => 'ユーザー2']);

        // Test that both users can be shown individually
        $response1 = $this->get(route('users.show', $user1));
        $response1->assertStatus(200);
        $response1->assertSee('ユーザー1');

        $response2 = $this->get(route('users.show', $user2));
        $response2->assertStatus(200);
        $response2->assertSee('ユーザー2');

        // Test that groups are shown correctly for each user
        $role = Role::create(['name' => 'メンバー']);
        $group = Group::factory()->create(['name' => 'テストグループ']);

        GroupMember::create([
            'user_id' => $user1->id,
            'group_id' => $group->id,
            'role_id' => $role->id,
        ]);

        $groupsResponse1 = $this->get(route('users.groups', $user1));
        $groupsResponse2 = $this->get(route('users.groups', $user2));

        $groups1 = $groupsResponse1->viewData('groups');
        $groups2 = $groupsResponse2->viewData('groups');

        $this->assertCount(1, $groups1); // user1 has 1 group
        $this->assertCount(0, $groups2); // user2 has 0 groups
    }

    public function test_user_controller_methods_dont_require_authentication()
    {
        // These are public routes for viewing user information
        $testUser = User::factory()->create();

        $response1 = $this->get(route('users.index'));
        $response1->assertStatus(200);

        $response2 = $this->get(route('users.show', $testUser));
        $response2->assertStatus(200);

        $response3 = $this->get(route('users.groups', $testUser));
        $response3->assertStatus(200);
    }
}