<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\ShiftSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftSubmissionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_shift_submission_has_fillable_attributes()
    {
        $fillable = [
            'user_id',
            'group_id',
            'date',
            'available_start_time',
            'available_end_time',
            'comment',
            'status',
        ];
        $submission = new ShiftSubmission();
        
        $this->assertEquals($fillable, $submission->getFillable());
    }

    public function test_shift_submission_casts_date_properly()
    {
        $submission = new ShiftSubmission([
            'date' => '2025-07-01'
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $submission->date);
    }

    public function test_shift_submission_has_status_constants()
    {
        $this->assertEquals('active', ShiftSubmission::STATUS_ACTIVE);
        $this->assertEquals('inactive', ShiftSubmission::STATUS_INACTIVE);
    }

    public function test_shift_submission_belongs_to_user()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        
        $submission = ShiftSubmission::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'date' => '2025-07-01',
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => ShiftSubmission::STATUS_ACTIVE,
        ]);

        $this->assertInstanceOf(User::class, $submission->user);
        $this->assertEquals($user->id, $submission->user->id);
    }

    public function test_shift_submission_belongs_to_group()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        
        $submission = ShiftSubmission::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'date' => '2025-07-01',
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => ShiftSubmission::STATUS_ACTIVE,
        ]);

        $this->assertInstanceOf(Group::class, $submission->group);
        $this->assertEquals($group->id, $submission->group->id);
    }

    public function test_is_available_for_time_returns_true_when_available()
    {
        $submission = new ShiftSubmission([
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => ShiftSubmission::STATUS_ACTIVE,
        ]);

        // 完全に時間内
        $this->assertTrue($submission->isAvailableForTime('10:00:00', '16:00:00'));
        
        // 開始時間が同じ
        $this->assertTrue($submission->isAvailableForTime('09:00:00', '16:00:00'));
        
        // 終了時間が同じ
        $this->assertTrue($submission->isAvailableForTime('10:00:00', '17:00:00'));
        
        // 全時間帯
        $this->assertTrue($submission->isAvailableForTime('09:00:00', '17:00:00'));
    }

    public function test_is_available_for_time_returns_false_when_not_available()
    {
        $submission = new ShiftSubmission([
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => ShiftSubmission::STATUS_ACTIVE,
        ]);

        // 開始時間が早すぎる
        $this->assertFalse($submission->isAvailableForTime('08:00:00', '16:00:00'));
        
        // 終了時間が遅すぎる
        $this->assertFalse($submission->isAvailableForTime('10:00:00', '18:00:00'));
        
        // 完全に時間外
        $this->assertFalse($submission->isAvailableForTime('18:00:00', '20:00:00'));
        
        // 時間はOKだが、前の時間帯
        $this->assertFalse($submission->isAvailableForTime('07:00:00', '08:00:00'));
    }

    public function test_is_available_for_time_returns_false_when_inactive()
    {
        $submission = new ShiftSubmission([
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => ShiftSubmission::STATUS_INACTIVE,
        ]);

        // 時間は問題ないが、ステータスがinactiveの場合
        $this->assertFalse($submission->isAvailableForTime('10:00:00', '16:00:00'));
    }

    public function test_is_active_returns_correct_status()
    {
        $activeSubmission = new ShiftSubmission([
            'status' => ShiftSubmission::STATUS_ACTIVE,
        ]);
        
        $inactiveSubmission = new ShiftSubmission([
            'status' => ShiftSubmission::STATUS_INACTIVE,
        ]);

        $this->assertTrue($activeSubmission->isActive());
        $this->assertFalse($inactiveSubmission->isActive());
    }

    public function test_is_available_for_time_edge_cases()
    {
        $submission = new ShiftSubmission([
            'available_start_time' => '12:00:00',
            'available_end_time' => '12:01:00',
            'status' => ShiftSubmission::STATUS_ACTIVE,
        ]);

        // 1分の可用性
        $this->assertTrue($submission->isAvailableForTime('12:00:00', '12:01:00'));
        
        // 1秒でも超えるとNG
        $this->assertFalse($submission->isAvailableForTime('12:00:00', '12:01:01'));
        
        // 1秒でも早いとNG
        $this->assertFalse($submission->isAvailableForTime('11:59:59', '12:01:00'));
    }

    public function test_submission_can_be_created_with_all_fields()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        
        $submission = ShiftSubmission::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'date' => '2025-07-01',
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'comment' => 'テストコメント',
            'status' => ShiftSubmission::STATUS_ACTIVE,
        ]);

        $this->assertDatabaseHas('shift_submissions', [
            'user_id' => $user->id,
            'group_id' => $group->id,
            'date' => '2025-07-01',
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'comment' => 'テストコメント',
            'status' => 'active',
        ]);
    }

    public function test_submission_defaults_to_active_status()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        
        $submission = ShiftSubmission::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'date' => '2025-07-01',
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            // statusを指定しない
        ]);

        // データベースのデフォルト値により'active'になるかテスト
        $this->assertEquals('active', $submission->fresh()->status);
    }
}
