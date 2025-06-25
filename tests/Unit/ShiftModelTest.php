<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_shift_has_fillable_attributes()
    {
        $fillable = ['group_id', 'user_id', 'start_time', 'end_time'];
        $shift = new Shift();
        
        $this->assertEquals($fillable, $shift->getFillable());
    }

    public function test_shift_casts_dates_properly()
    {
        $shift = new Shift([
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00'
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $shift->start_time);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $shift->end_time);
    }

    public function test_shift_belongs_to_group()
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();
        
        $shift = Shift::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
        ]);

        $this->assertInstanceOf(Group::class, $shift->group);
        $this->assertEquals($group->id, $shift->group->id);
    }

    public function test_shift_belongs_to_user()
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();
        
        $shift = Shift::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
        ]);

        $this->assertInstanceOf(User::class, $shift->user);
        $this->assertEquals($user->id, $shift->user->id);
    }

    public function test_has_time_conflict_detects_overlapping_shifts()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();

        // 既存のシフト: 9:00-17:00
        Shift::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
        ]);

        // テストケース1: 開始時間が重複
        $hasConflict = Shift::hasTimeConflict(
            $user->id,
            '2025-07-01 08:00:00',
            '2025-07-01 10:00:00'
        );
        $this->assertTrue($hasConflict, 'Should detect conflict when start time overlaps');

        // テストケース2: 終了時間が重複
        $hasConflict = Shift::hasTimeConflict(
            $user->id,
            '2025-07-01 16:00:00',
            '2025-07-01 18:00:00'
        );
        $this->assertTrue($hasConflict, 'Should detect conflict when end time overlaps');

        // テストケース3: 完全に内包される
        $hasConflict = Shift::hasTimeConflict(
            $user->id,
            '2025-07-01 10:00:00',
            '2025-07-01 16:00:00'
        );
        $this->assertTrue($hasConflict, 'Should detect conflict when completely inside existing shift');

        // テストケース4: 完全に包含する
        $hasConflict = Shift::hasTimeConflict(
            $user->id,
            '2025-07-01 08:00:00',
            '2025-07-01 18:00:00'
        );
        $this->assertTrue($hasConflict, 'Should detect conflict when completely encompasses existing shift');

        // テストケース5: 同じ開始時間
        $hasConflict = Shift::hasTimeConflict(
            $user->id,
            '2025-07-01 09:00:00',
            '2025-07-01 12:00:00'
        );
        $this->assertTrue($hasConflict, 'Should detect conflict when start times are same');

        // テストケース6: 同じ終了時間
        $hasConflict = Shift::hasTimeConflict(
            $user->id,
            '2025-07-01 14:00:00',
            '2025-07-01 17:00:00'
        );
        $this->assertTrue($hasConflict, 'Should detect conflict when end times are same');
    }

    public function test_has_time_conflict_no_overlap()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();

        // 既存のシフト: 9:00-17:00
        Shift::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
        ]);

        // テストケース1: 前の時間帯
        $hasConflict = Shift::hasTimeConflict(
            $user->id,
            '2025-07-01 07:00:00',
            '2025-07-01 09:00:00'
        );
        $this->assertFalse($hasConflict, 'Should not detect conflict for earlier time slot');

        // テストケース2: 後の時間帯
        $hasConflict = Shift::hasTimeConflict(
            $user->id,
            '2025-07-01 17:00:00',
            '2025-07-01 19:00:00'
        );
        $this->assertFalse($hasConflict, 'Should not detect conflict for later time slot');

        // テストケース3: 別の日
        $hasConflict = Shift::hasTimeConflict(
            $user->id,
            '2025-07-02 09:00:00',
            '2025-07-02 17:00:00'
        );
        $this->assertFalse($hasConflict, 'Should not detect conflict for different date');

        // テストケース4: 別のユーザー
        $otherUser = User::factory()->create();
        $hasConflict = Shift::hasTimeConflict(
            $otherUser->id,
            '2025-07-01 09:00:00',
            '2025-07-01 17:00:00'
        );
        $this->assertFalse($hasConflict, 'Should not detect conflict for different user');
    }

    public function test_has_time_conflict_excludes_specific_shift()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();

        $existingShift = Shift::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 17:00:00',
        ]);

        // 同じ時間帯だが、既存シフトを除外する場合
        $hasConflict = Shift::hasTimeConflict(
            $user->id,
            '2025-07-01 09:00:00',
            '2025-07-01 17:00:00',
            $existingShift->id
        );
        $this->assertFalse($hasConflict, 'Should not detect conflict when excluding the specific shift');
    }

    public function test_has_time_conflict_with_multiple_existing_shifts()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();

        // 複数の既存シフト
        Shift::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'start_time' => '2025-07-01 09:00:00',
            'end_time' => '2025-07-01 12:00:00',
        ]);

        Shift::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'start_time' => '2025-07-01 14:00:00',
            'end_time' => '2025-07-01 17:00:00',
        ]);

        // 午前のシフトと重複
        $hasConflict = Shift::hasTimeConflict(
            $user->id,
            '2025-07-01 10:00:00',
            '2025-07-01 13:00:00'
        );
        $this->assertTrue($hasConflict, 'Should detect conflict with morning shift');

        // 午後のシフトと重複
        $hasConflict = Shift::hasTimeConflict(
            $user->id,
            '2025-07-01 13:00:00',
            '2025-07-01 15:00:00'
        );
        $this->assertTrue($hasConflict, 'Should detect conflict with afternoon shift');

        // 間の時間帯（重複なし）
        $hasConflict = Shift::hasTimeConflict(
            $user->id,
            '2025-07-01 12:00:00',
            '2025-07-01 14:00:00'
        );
        $this->assertFalse($hasConflict, 'Should not detect conflict in gap between shifts');
    }

    public function test_has_time_conflict_edge_cases()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();

        // 1分のシフト
        Shift::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'start_time' => '2025-07-01 12:00:00',
            'end_time' => '2025-07-01 12:01:00',
        ]);

        // 1秒重複
        $hasConflict = Shift::hasTimeConflict(
            $user->id,
            '2025-07-01 12:00:30',
            '2025-07-01 12:05:00'
        );
        $this->assertTrue($hasConflict, 'Should detect conflict even with minimal overlap');

        // 境界値テスト
        $hasConflict = Shift::hasTimeConflict(
            $user->id,
            '2025-07-01 12:01:00',
            '2025-07-01 12:30:00'
        );
        $this->assertFalse($hasConflict, 'Should not detect conflict when times touch exactly');
    }
}
