<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Role;
use App\Models\ShiftSubmission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $group;
    protected $memberRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->memberRole = Role::create(['name' => 'メンバー']);
        $this->user = User::factory()->create();
        $this->group = Group::factory()->create();

        GroupMember::create([
            'user_id' => $this->user->id,
            'group_id' => $this->group->id,
            'role_id' => $this->memberRole->id,
        ]);
    }

    public function test_member_can_access_availability_creation_form()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('availability.create', $this->group));

        $response->assertStatus(200);
        $response->assertViewIs('availability.create');
        $response->assertViewHas('group', $this->group);
        $response->assertViewHas('existingAvailabilities');
    }

    public function test_non_member_cannot_access_availability_creation_form()
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $response = $this->get(route('availability.create', $this->group));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_availability_creation_form()
    {
        $response = $this->get(route('availability.create', $this->group));

        $response->assertRedirect(route('login'));
    }

    public function test_member_can_create_new_availability()
    {
        $this->actingAs($this->user);

        $availabilityData = [
            'date' => Carbon::tomorrow()->format('Y-m-d'),
            'available_start_time' => '09:00',
            'available_end_time' => '17:00',
            'comment' => '朝から夕方まで対応可能です',
        ];

        $response = $this->post(route('availability.store', $this->group), $availabilityData);

        $response->assertRedirect(route('availability.create', $this->group));
        $response->assertSessionHas('success', '可用性を登録しました');

        $this->assertDatabaseHas('shift_submissions', [
            'user_id' => $this->user->id,
            'group_id' => $this->group->id,
            'date' => $availabilityData['date'],
            'available_start_time' => $availabilityData['available_start_time'] . ':00',
            'available_end_time' => $availabilityData['available_end_time'] . ':00',
            'comment' => $availabilityData['comment'],
            'status' => ShiftSubmission::STATUS_ACTIVE,
        ]);
    }

    public function test_member_can_update_existing_availability()
    {
        $this->actingAs($this->user);

        $existingAvailability = ShiftSubmission::create([
            'user_id' => $this->user->id,
            'group_id' => $this->group->id,
            'date' => Carbon::tomorrow(),
            'available_start_time' => '10:00:00',
            'available_end_time' => '16:00:00',
            'comment' => '初期コメント',
            'status' => ShiftSubmission::STATUS_ACTIVE,
        ]);

        $updatedData = [
            'date' => Carbon::tomorrow()->format('Y-m-d'),
            'available_start_time' => '08:00',
            'available_end_time' => '18:00',
            'comment' => '更新されたコメント',
        ];

        $response = $this->post(route('availability.store', $this->group), $updatedData);

        $response->assertRedirect(route('availability.create', $this->group));
        $response->assertSessionHas('success', '可用性を更新しました');

        $existingAvailability->refresh();
        $this->assertEquals('08:00:00', $existingAvailability->available_start_time);
        $this->assertEquals('18:00:00', $existingAvailability->available_end_time);
        $this->assertEquals('更新されたコメント', $existingAvailability->comment);
    }

    public function test_availability_store_validation_rules()
    {
        $this->actingAs($this->user);

        // Test required fields
        $response = $this->post(route('availability.store', $this->group), []);
        $response->assertSessionHasErrors(['date', 'available_start_time', 'available_end_time']);

        // Test past date validation
        $response = $this->post(route('availability.store', $this->group), [
            'date' => Carbon::yesterday()->format('Y-m-d'),
            'available_start_time' => '09:00',
            'available_end_time' => '17:00',
        ]);
        $response->assertSessionHasErrors(['date']);

        // Test time format validation
        $response = $this->post(route('availability.store', $this->group), [
            'date' => Carbon::tomorrow()->format('Y-m-d'),
            'available_start_time' => 'invalid',
            'available_end_time' => 'invalid',
        ]);
        $response->assertSessionHasErrors(['available_start_time', 'available_end_time']);

        // Test end time after start time validation
        $response = $this->post(route('availability.store', $this->group), [
            'date' => Carbon::tomorrow()->format('Y-m-d'),
            'available_start_time' => '17:00',
            'available_end_time' => '09:00',
        ]);
        $response->assertSessionHasErrors(['available_end_time']);

        // Test comment max length validation
        $response = $this->post(route('availability.store', $this->group), [
            'date' => Carbon::tomorrow()->format('Y-m-d'),
            'available_start_time' => '09:00',
            'available_end_time' => '17:00',
            'comment' => str_repeat('a', 501),
        ]);
        $response->assertSessionHasErrors(['comment']);
    }

    public function test_member_can_view_availability_index()
    {
        $this->actingAs($this->user);

        // Create some availabilities
        ShiftSubmission::create([
            'user_id' => $this->user->id,
            'group_id' => $this->group->id,
            'date' => Carbon::tomorrow(),
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => ShiftSubmission::STATUS_ACTIVE,
        ]);

        ShiftSubmission::create([
            'user_id' => $this->user->id,
            'group_id' => $this->group->id,
            'date' => Carbon::tomorrow()->addDay(),
            'available_start_time' => '10:00:00',
            'available_end_time' => '16:00:00',
            'status' => ShiftSubmission::STATUS_ACTIVE,
        ]);

        $response = $this->get(route('availability.index', $this->group));

        $response->assertStatus(200);
        $response->assertViewIs('availability.index');
        $response->assertViewHas('group', $this->group);
        $response->assertViewHas('availabilities');

        // Check that only future availabilities are shown
        $availabilities = $response->viewData('availabilities');
        $this->assertCount(2, $availabilities);
    }

    public function test_availability_index_excludes_past_dates()
    {
        $this->actingAs($this->user);

        // Create past availability
        ShiftSubmission::create([
            'user_id' => $this->user->id,
            'group_id' => $this->group->id,
            'date' => Carbon::yesterday(),
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => ShiftSubmission::STATUS_ACTIVE,
        ]);

        // Create future availability
        ShiftSubmission::create([
            'user_id' => $this->user->id,
            'group_id' => $this->group->id,
            'date' => Carbon::tomorrow(),
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => ShiftSubmission::STATUS_ACTIVE,
        ]);

        $response = $this->get(route('availability.index', $this->group));

        $availabilities = $response->viewData('availabilities');
        $this->assertCount(1, $availabilities); // Only future availability
    }

    public function test_member_can_delete_their_own_availability()
    {
        $this->actingAs($this->user);

        $availability = ShiftSubmission::create([
            'user_id' => $this->user->id,
            'group_id' => $this->group->id,
            'date' => Carbon::tomorrow(),
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => ShiftSubmission::STATUS_ACTIVE,
        ]);

        $response = $this->delete(route('availability.destroy', [$this->group, $availability]));

        $response->assertRedirect(route('availability.index', $this->group));
        $response->assertSessionHas('success', '可用性を削除しました');

        $this->assertDatabaseMissing('shift_submissions', [
            'id' => $availability->id,
        ]);
    }

    public function test_member_cannot_delete_other_users_availability()
    {
        $this->actingAs($this->user);

        $otherUser = User::factory()->create();
        GroupMember::create([
            'user_id' => $otherUser->id,
            'group_id' => $this->group->id,
            'role_id' => $this->memberRole->id,
        ]);

        $otherAvailability = ShiftSubmission::create([
            'user_id' => $otherUser->id,
            'group_id' => $this->group->id,
            'date' => Carbon::tomorrow(),
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => ShiftSubmission::STATUS_ACTIVE,
        ]);

        $response = $this->delete(route('availability.destroy', [$this->group, $otherAvailability]));

        $response->assertStatus(403);

        $this->assertDatabaseHas('shift_submissions', [
            'id' => $otherAvailability->id,
        ]);
    }

    public function test_non_member_cannot_access_availability_endpoints()
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $availability = ShiftSubmission::create([
            'user_id' => $this->user->id,
            'group_id' => $this->group->id,
            'date' => Carbon::tomorrow(),
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => ShiftSubmission::STATUS_ACTIVE,
        ]);

        // Test index
        $response = $this->get(route('availability.index', $this->group));
        $response->assertStatus(403);

        // Test store
        $response = $this->post(route('availability.store', $this->group), [
            'date' => Carbon::tomorrow()->format('Y-m-d'),
            'available_start_time' => '09:00',
            'available_end_time' => '17:00',
        ]);
        $response->assertStatus(403);

        // Test destroy
        $response = $this->delete(route('availability.destroy', [$this->group, $availability]));
        $response->assertStatus(403);
    }

    public function test_availability_create_shows_existing_availabilities()
    {
        $this->actingAs($this->user);

        // Create availability within the 30-day range
        $futureDate = Carbon::today()->addDays(15);
        $availability = ShiftSubmission::create([
            'user_id' => $this->user->id,
            'group_id' => $this->group->id,
            'date' => $futureDate,
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => ShiftSubmission::STATUS_ACTIVE,
        ]);

        $response = $this->get(route('availability.create', $this->group));

        $existingAvailabilities = $response->viewData('existingAvailabilities');
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $existingAvailabilities);
        $this->assertGreaterThan(0, $existingAvailabilities->count());
        
        // Check if the availability exists by looking for the created availability
        $found = $existingAvailabilities->contains(function ($item) use ($availability) {
            return $item->id === $availability->id;
        });
        $this->assertTrue($found, 'Created availability should be found in existingAvailabilities');
    }

    public function test_availability_comment_is_optional()
    {
        $this->actingAs($this->user);

        $availabilityData = [
            'date' => Carbon::tomorrow()->format('Y-m-d'),
            'available_start_time' => '09:00',
            'available_end_time' => '17:00',
        ];

        $response = $this->post(route('availability.store', $this->group), $availabilityData);

        $response->assertRedirect(route('availability.create', $this->group));
        $response->assertSessionHas('success', '可用性を登録しました');

        $this->assertDatabaseHas('shift_submissions', [
            'user_id' => $this->user->id,
            'group_id' => $this->group->id,
            'date' => $availabilityData['date'],
            'comment' => null,
        ]);
    }
}