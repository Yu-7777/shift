<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->user = User::factory()->create();
    }

    public function test_guest_cannot_access_home()
    {
        $response = $this->get(route('home'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_home()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewIs('home');
        $response->assertViewHasAll(['user', 'groups']);
    }

    public function test_home_displays_authenticated_user()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('home'));

        $viewUser = $response->viewData('user');
        $this->assertEquals($this->user->id, $viewUser->id);
        $this->assertEquals($this->user->name, $viewUser->name);
        $this->assertEquals($this->user->email, $viewUser->email);
    }

    public function test_home_displays_user_groups()
    {
        $this->actingAs($this->user);

        // Create groups and add user to them
        
        $group1 = Group::factory()->create(['name' => 'グループ1']);
        $group2 = Group::factory()->create(['name' => 'グループ2']);
        $group3 = Group::factory()->create(['name' => 'グループ3']);

        GroupMember::create([
            'user_id' => $this->user->id,
            'group_id' => $group1->id,
            'role_id' => GroupMember::ROLE_MEMBER,
        ]);

        GroupMember::create([
            'user_id' => $this->user->id,
            'group_id' => $group2->id,
            'role_id' => GroupMember::ROLE_MEMBER,
        ]);

        GroupMember::create([
            'user_id' => $this->user->id,
            'group_id' => $group3->id,
            'role_id' => GroupMember::ROLE_MEMBER,
        ]);

        $response = $this->get(route('home'));

        $groups = $response->viewData('groups');
        $this->assertCount(3, $groups);
        
        $groupNames = $groups->pluck('name')->toArray();
        $this->assertContains('グループ1', $groupNames);
        $this->assertContains('グループ2', $groupNames);
        $this->assertContains('グループ3', $groupNames);
    }

    public function test_home_shows_empty_groups_for_user_with_no_groups()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('home'));

        $groups = $response->viewData('groups');
        $this->assertCount(0, $groups);
    }

    public function test_home_only_shows_users_own_groups()
    {
        $this->actingAs($this->user);

        $otherUser = User::factory()->create();
        
        $userGroup = Group::factory()->create(['name' => 'ユーザーのグループ']);
        $otherGroup = Group::factory()->create(['name' => '他人のグループ']);

        // Add this user to userGroup
        GroupMember::create([
            'user_id' => $this->user->id,
            'group_id' => $userGroup->id,
            'role_id' => GroupMember::ROLE_MEMBER,
        ]);

        // Add other user to otherGroup
        GroupMember::create([
            'user_id' => $otherUser->id,
            'group_id' => $otherGroup->id,
            'role_id' => GroupMember::ROLE_MEMBER,
        ]);

        $response = $this->get(route('home'));

        $groups = $response->viewData('groups');
        $this->assertCount(1, $groups);
        $this->assertEquals('ユーザーのグループ', $groups->first()->name);
    }

    public function test_home_groups_are_properly_loaded()
    {
        $this->actingAs($this->user);

        
        // Create many groups for this user
        for ($i = 1; $i <= 15; $i++) {
            $group = Group::factory()->create(['name' => "グループ{$i}"]);
            GroupMember::create([
                'user_id' => $this->user->id,
                'group_id' => $group->id,
                'role_id' => GroupMember::ROLE_MEMBER,
            ]);
        }

        $response = $this->get(route('home'));

        $groups = $response->viewData('groups');
        
        // Check if groups are properly loaded (getShiftGroups returns paginated results)
        $this->assertTrue($groups->count() > 0);
        $this->assertLessThanOrEqual(15, $groups->count());
        
        // Verify that it's either a collection or paginated instance
        $this->assertTrue(
            $groups instanceof \Illuminate\Support\Collection || 
            $groups instanceof \Illuminate\Pagination\LengthAwarePaginator
        );
    }

    public function test_home_includes_group_member_role_information()
    {
        $this->actingAs($this->user);

        
        $adminGroup = Group::factory()->create(['name' => '管理グループ']);
        $memberGroup = Group::factory()->create(['name' => 'メンバーグループ']);

        GroupMember::create([
            'user_id' => $this->user->id,
            'group_id' => $adminGroup->id,
            'role_id' => GroupMember::ROLE_ADMIN,
        ]);

        GroupMember::create([
            'user_id' => $this->user->id,
            'group_id' => $memberGroup->id,
            'role_id' => GroupMember::ROLE_MEMBER,
        ]);

        $response = $this->get(route('home'));

        $groups = $response->viewData('groups');
        $this->assertCount(2, $groups);

        // Check that pivot data (role information) is included
        foreach ($groups as $group) {
            $this->assertNotNull($group->pivot);
            $this->assertNotNull($group->pivot->role_id);
        }
    }

    public function test_home_route_requires_authentication_middleware()
    {
        // Test that the route has auth middleware by checking redirect to login
        $response = $this->get(route('home'));
        $response->assertRedirect(route('login'));
    }

    public function test_home_handles_user_with_different_roles_in_multiple_groups()
    {
        $this->actingAs($this->user);

        
        $group1 = Group::factory()->create();
        $group2 = Group::factory()->create();

        // User is admin in group1 and member in group2
        GroupMember::create([
            'user_id' => $this->user->id,
            'group_id' => $group1->id,
            'role_id' => GroupMember::ROLE_ADMIN,
        ]);

        GroupMember::create([
            'user_id' => $this->user->id,
            'group_id' => $group2->id,
            'role_id' => GroupMember::ROLE_MEMBER,
        ]);

        $response = $this->get(route('home'));

        $groups = $response->viewData('groups');
        $this->assertCount(2, $groups);

        // Verify different roles are properly loaded
        $group1Data = $groups->firstWhere('id', $group1->id);
        $group2Data = $groups->firstWhere('id', $group2->id);

        $this->assertEquals(GroupMember::ROLE_ADMIN, $group1Data->pivot->role_id);
        $this->assertEquals(GroupMember::ROLE_MEMBER, $group2Data->pivot->role_id);
    }

    public function test_home_page_content_includes_user_name()
    {
        $user = User::factory()->create(['name' => 'テストユーザー']);
        $this->actingAs($user);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
    }
}