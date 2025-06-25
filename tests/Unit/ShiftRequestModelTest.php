<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\ShiftRequest;
use App\Models\ShiftSubmission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftRequestModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_shift_request_has_fillable_attributes()
    {
        $fillable = [
            'user_id',
            'group_id',
            'title',
            'description',
            'start_time',
            'end_time',
            'requested_people',
            'application_deadline',
            'status',
        ];
        $shiftRequest = new ShiftRequest();
        
        $this->assertEquals($fillable, $shiftRequest->getFillable());
    }

    public function test_shift_request_casts_dates_properly()
    {
        $shiftRequest = new ShiftRequest([
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
            'application_deadline' => '2025-06-30 23:59:59',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $shiftRequest->start_time);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $shiftRequest->end_time);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $shiftRequest->application_deadline);
    }

    public function test_shift_request_has_status_constants()
    {
        $this->assertEquals('open', ShiftRequest::STATUS_OPEN);
        $this->assertEquals('closed', ShiftRequest::STATUS_CLOSED);
        $this->assertEquals('assigned', ShiftRequest::STATUS_ASSIGNED);
    }

    public function test_shift_request_belongs_to_creator()
    {
        $creator = User::factory()->create();
        $group = Group::factory()->create();
        
        $shiftRequest = ShiftRequest::create([
            'user_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'テストシフト',
            'description' => 'テスト用シフト',
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
            'requested_people' => 1,
            'application_deadline' => '2025-06-30 23:59:59',
            'status' => ShiftRequest::STATUS_OPEN,
        ]);

        $this->assertInstanceOf(User::class, $shiftRequest->creator);
        $this->assertEquals($creator->id, $shiftRequest->creator->id);
    }

    public function test_shift_request_belongs_to_group()
    {
        $creator = User::factory()->create();
        $group = Group::factory()->create();
        
        $shiftRequest = ShiftRequest::create([
            'user_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'テストシフト',
            'description' => 'テスト用シフト',
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
            'requested_people' => 1,
            'application_deadline' => '2025-06-30 23:59:59',
            'status' => ShiftRequest::STATUS_OPEN,
        ]);

        $this->assertInstanceOf(Group::class, $shiftRequest->group);
        $this->assertEquals($group->id, $shiftRequest->group->id);
    }

    public function test_can_apply_returns_true_when_open_and_before_deadline()
    {
        $creator = User::factory()->create();
        $group = Group::factory()->create();
        
        $shiftRequest = ShiftRequest::create([
            'user_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'テストシフト',
            'description' => 'テスト用シフト',
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
            'requested_people' => 1,
            'application_deadline' => Carbon::now()->addDays(1), // 明日が締切
            'status' => ShiftRequest::STATUS_OPEN,
        ]);

        $this->assertTrue($shiftRequest->canApply());
    }

    public function test_can_apply_returns_false_when_status_is_closed()
    {
        $creator = User::factory()->create();
        $group = Group::factory()->create();
        
        $shiftRequest = ShiftRequest::create([
            'user_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'テストシフト',
            'description' => 'テスト用シフト',
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
            'requested_people' => 1,
            'application_deadline' => Carbon::now()->addDays(1),
            'status' => ShiftRequest::STATUS_CLOSED,
        ]);

        $this->assertFalse($shiftRequest->canApply());
    }

    public function test_can_apply_returns_false_when_deadline_passed()
    {
        $creator = User::factory()->create();
        $group = Group::factory()->create();
        
        $shiftRequest = ShiftRequest::create([
            'user_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'テストシフト',
            'description' => 'テスト用シフト',
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
            'requested_people' => 1,
            'application_deadline' => Carbon::now()->subDays(1), // 昨日が締切
            'status' => ShiftRequest::STATUS_OPEN,
        ]);

        $this->assertFalse($shiftRequest->canApply());
    }

    public function test_is_deadline_passed_returns_true_when_deadline_passed()
    {
        $creator = User::factory()->create();
        $group = Group::factory()->create();
        
        $shiftRequest = ShiftRequest::create([
            'user_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'テストシフト',
            'description' => 'テスト用シフト',
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
            'requested_people' => 1,
            'application_deadline' => Carbon::now()->subHour(), // 1時間前が締切
            'status' => ShiftRequest::STATUS_OPEN,
        ]);

        $this->assertTrue($shiftRequest->isDeadlinePassed());
    }

    public function test_is_deadline_passed_returns_false_when_deadline_not_passed()
    {
        $creator = User::factory()->create();
        $group = Group::factory()->create();
        
        $shiftRequest = ShiftRequest::create([
            'user_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'テストシフト',
            'description' => 'テスト用シフト',
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
            'requested_people' => 1,
            'application_deadline' => Carbon::now()->addHour(), // 1時間後が締切
            'status' => ShiftRequest::STATUS_OPEN,
        ]);

        $this->assertFalse($shiftRequest->isDeadlinePassed());
    }

    public function test_auto_close_if_deadline_passed_closes_when_deadline_passed()
    {
        $creator = User::factory()->create();
        $group = Group::factory()->create();
        
        $shiftRequest = ShiftRequest::create([
            'user_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'テストシフト',
            'description' => 'テスト用シフト',
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
            'requested_people' => 1,
            'application_deadline' => Carbon::now()->subHour(),
            'status' => ShiftRequest::STATUS_OPEN,
        ]);

        $this->assertEquals(ShiftRequest::STATUS_OPEN, $shiftRequest->status);

        $shiftRequest->autoCloseIfDeadlinePassed();

        $this->assertEquals(ShiftRequest::STATUS_CLOSED, $shiftRequest->fresh()->status);
    }

    public function test_auto_close_if_deadline_passed_does_not_close_when_deadline_not_passed()
    {
        $creator = User::factory()->create();
        $group = Group::factory()->create();
        
        $shiftRequest = ShiftRequest::create([
            'user_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'テストシフト',
            'description' => 'テスト用シフト',
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
            'requested_people' => 1,
            'application_deadline' => Carbon::now()->addHour(),
            'status' => ShiftRequest::STATUS_OPEN,
        ]);

        $shiftRequest->autoCloseIfDeadlinePassed();

        $this->assertEquals(ShiftRequest::STATUS_OPEN, $shiftRequest->fresh()->status);
    }

    public function test_shift_request_can_be_created_with_all_fields()
    {
        $creator = User::factory()->create();
        $group = Group::factory()->create();
        
        $shiftRequest = ShiftRequest::create([
            'user_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'テストシフト募集',
            'description' => 'テスト用のシフト募集です',
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
            'requested_people' => 2,
            'application_deadline' => '2025-06-30 23:59:59',
            'status' => ShiftRequest::STATUS_OPEN,
        ]);

        $this->assertDatabaseHas('shift_requests', [
            'user_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'テストシフト募集',
            'description' => 'テスト用のシフト募集です',
            'requested_people' => 2,
            'status' => ShiftRequest::STATUS_OPEN,
        ]);
    }

    public function test_shift_request_status_transitions()
    {
        $creator = User::factory()->create();
        $group = Group::factory()->create();
        
        $shiftRequest = ShiftRequest::create([
            'user_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'テストシフト',
            'description' => 'テスト用シフト',
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
            'requested_people' => 1,
            'application_deadline' => Carbon::now()->addDays(1),
            'status' => ShiftRequest::STATUS_OPEN,
        ]);

        // OPEN -> CLOSED
        $shiftRequest->update(['status' => ShiftRequest::STATUS_CLOSED]);
        $this->assertEquals(ShiftRequest::STATUS_CLOSED, $shiftRequest->status);

        // CLOSED -> ASSIGNED
        $shiftRequest->update(['status' => ShiftRequest::STATUS_ASSIGNED]);
        $this->assertEquals(ShiftRequest::STATUS_ASSIGNED, $shiftRequest->status);
    }
}