<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Role;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $group;
    protected $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->group = Group::factory()->create();
        $this->role = Role::factory()->create();
        
        GroupMember::factory()
            ->withUser($this->user)
            ->withGroup($this->group)
            ->withRole($this->role)
            ->create();
    }

    public function test_guest_cannot_access_group_show()
    {
        $response = $this->get(route('groups.show', $this->group));

        $response->assertRedirect(route('login'));
    }

    public function test_non_member_cannot_access_group()
    {
        $nonMember = User::factory()->create();

        $this->actingAs($nonMember)
            ->get(route('groups.show', $this->group))
            ->assertStatus(403);
    }

    public function test_member_can_access_group_show()
    {
        $this->actingAs($this->user)
            ->get(route('groups.show', $this->group))
            ->assertStatus(200)
            ->assertSee($this->group->name)
            ->assertViewIs('groups.show')
            ->assertViewHasAll([
                'group',
                'shifts',
                'members',
                'calendar',
            ]);
    }

    public function test_group_show_displays_current_month_shifts()
    {
        $this->actingAs($this->user);

        // Create shifts for current month
        $currentMonthShift = Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'start_time' => Carbon::now()->startOfMonth()->addDays(10)->setTime(9, 0),
            'end_time' => Carbon::now()->startOfMonth()->addDays(10)->setTime(17, 0),
        ]);

        // Create shift for different month (should not appear)
        $differentMonthShift = Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'start_time' => Carbon::now()->addMonth()->setTime(9, 0),
            'end_time' => Carbon::now()->addMonth()->setTime(17, 0),
        ]);

        $response = $this->get(route('groups.show', $this->group));

        $shifts = $response->viewData('shifts');
        $this->assertCount(1, $shifts);
        $this->assertEquals($currentMonthShift->id, $shifts->first()->id);
    }

    public function test_group_show_displays_all_members()
    {
        $this->actingAs($this->user);

        // Add more members to the group
        $member2 = User::factory()->create();
        $member3 = User::factory()->create();

        GroupMember::factory()
            ->withUser($member2)
            ->withGroup($this->group)
            ->withRole($this->role)
            ->create();

        GroupMember::factory()
            ->withUser($member3)
            ->withGroup($this->group)
            ->withRole($this->role)
            ->create();

        $response = $this->get(route('groups.show', $this->group));

        $members = $response->viewData('members');
        $this->assertCount(3, $members);
        
        $memberIds = $members->pluck('id')->toArray();
        $this->assertContains($this->user->id, $memberIds);
        $this->assertContains($member2->id, $memberIds);
        $this->assertContains($member3->id, $memberIds);
    }

    public function test_group_show_includes_calendar_data()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('groups.show', $this->group));

        $calendar = $response->viewData('calendar');
        $this->assertIsArray($calendar);
        $this->assertArrayHasKey('weeks', $calendar);
        $this->assertArrayHasKey('targetYear', $calendar);
        $this->assertArrayHasKey('targetMonth', $calendar);
        $this->assertArrayHasKey('currentDate', $calendar);
        $this->assertArrayHasKey('displayMonth', $calendar);
    }

    public function test_group_show_loads_shift_relationships()
    {
        $this->actingAs($this->user);

        $shift = Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'start_time' => Carbon::now()->setTime(9, 0),
            'end_time' => Carbon::now()->setTime(17, 0),
        ]);

        $response = $this->get(route('groups.show', $this->group));

        $shifts = $response->viewData('shifts');
        $this->assertTrue($shifts->first()->relationLoaded('user'));
        $this->assertEquals($this->user->name, $shifts->first()->user->name);
    }

    public function test_group_show_filters_shifts_by_year_and_month()
    {
        $this->actingAs($this->user);

        // Create shift for current year/month
        $currentShift = Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'start_time' => Carbon::now()->setTime(9, 0),
            'end_time' => Carbon::now()->setTime(17, 0),
        ]);

        // Create shift for different year
        $differentYearShift = Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'start_time' => Carbon::now()->subYear()->setTime(9, 0),
            'end_time' => Carbon::now()->subYear()->setTime(17, 0),
        ]);

        // Create shift for different month in same year
        $differentMonthShift = Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'start_time' => Carbon::now()->subMonth()->setTime(9, 0),
            'end_time' => Carbon::now()->subMonth()->setTime(17, 0),
        ]);

        $response = $this->get(route('groups.show', $this->group));

        $shifts = $response->viewData('shifts');
        $this->assertCount(1, $shifts);
        $this->assertEquals($currentShift->id, $shifts->first()->id);
    }

    public function test_group_show_handles_empty_shifts()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('groups.show', $this->group));

        if ($response->status() !== 200) {
            dump($response->getContent());
        }

        $response->assertStatus(200);
        $shifts = $response->viewData('shifts');
        $this->assertCount(0, $shifts);
    }

    public function test_group_show_handles_group_without_members()
    {
        $emptyGroup = Group::factory()->create();
        
        // Add user to empty group
        GroupMember::factory()
            ->withUser($this->user)
            ->withGroup($emptyGroup)
            ->withRole($this->role)
            ->create();

        $this->actingAs($this->user);

        $response = $this->get(route('groups.show', $emptyGroup));

        $response->assertStatus(200);
        $members = $response->viewData('members');
        $this->assertCount(1, $members); // Only the authenticated user
    }

    public function test_multiple_users_access_same_group()
    {
        $user2 = User::factory()->create();
        GroupMember::factory()
            ->withUser($user2)
            ->withGroup($this->group)
            ->withRole($this->role)
            ->create();

        // Test first user can access
        $response1 = $this->actingAs($this->user)
            ->get(route('groups.show', $this->group));
        $response1->assertStatus(200);

        // Test second user can access
        $response2 = $this->actingAs($user2)
            ->get(route('groups.show', $this->group));
        $response2->assertStatus(200);

        // Both should see the same group data
        $this->assertEquals($response1->viewData('group')->id, $response2->viewData('group')->id);
    }

    public function test_user_with_multiple_groups_access()
    {
        $group2 = Group::factory()->create();
        GroupMember::factory()
            ->withUser($this->user)
            ->withGroup($group2)
            ->withRole($this->role)
            ->create();

        $this->actingAs($this->user);

        // User should be able to access both groups
        $response1 = $this->get(route('groups.show', $this->group));
        $response1->assertStatus(200);

        $response2 = $this->get(route('groups.show', $group2));
        $response2->assertStatus(200);

        $this->assertNotEquals($response1->viewData('group')->id, $response2->viewData('group')->id);
    }

    public function test_group_show_permission_check_works_correctly()
    {
        $unauthorizedUser = User::factory()->create();
        $otherGroup = Group::factory()->create();

        // Add unauthorized user to other group, not this group
        GroupMember::factory()
            ->withUser($unauthorizedUser)
            ->withGroup($otherGroup)
            ->withRole($this->role)
            ->create();

        $this->actingAs($unauthorizedUser);

        $response = $this->get(route('groups.show', $this->group));
        $response->assertStatus(403);
    }

    /**
     * Legacy test compatibility
     */
    public function test_lack_of_authority(): void
    {
        $users = User::factory()->count(2)->create();
        $group = Group::factory()->create();
        $role = Role::factory()->create();
        GroupMember::factory()
            ->withUser($users[0])
            ->withGroup($group)
            ->withRole($role)
            ->create();

        $this->actingAs($users[1])
            ->get('/groups/'.$group->id)
            ->assertStatus(403);
    }

    public function test_correct_display(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $role = Role::factory()->create();
        GroupMember::factory()
            ->withUser($user)
            ->withGroup($group)
            ->withRole($role)
            ->create();

        $this->actingAs($user)
            ->get('/groups/'.$group->id)
            ->assertStatus(200)
            ->assertSee($group->name)
            ->assertViewHasAll([
                'group',
                'shifts',
                'members',
            ]);
    }
}
