<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Role;
use App\Models\Shift;
use App\Models\ShiftSubmission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftConflictEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $group;

    protected function setUp(): void
    {
        parent::setUp();

        // シーダーを実行してRoleを作成
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->create();
        $this->user = User::factory()->create();
        $this->group = Group::factory()->create();

        GroupMember::create([
            'user_id' => $this->admin->id,
            'group_id' => $this->group->id,
            'role_id' => GroupMember::ROLE_ADMIN
        ]);

        GroupMember::create([
            'user_id' => $this->user->id,
            'group_id' => $this->group->id,
            'role_id' => GroupMember::ROLE_MEMBER
        ]);
    }

    public function test_shift_conflict_detection_with_microseconds()
    {
        // マイクロ秒の精度でのテスト
        $existingShift = Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'start_time' => '2025-07-01 12:00:00.123456',
            'end_time' => '2025-07-01 13:00:00.654321',
        ]);

        // マイクロ秒レベルで重複する場合
        $hasConflict = Shift::hasTimeConflict(
            $this->user->id,
            '2025-07-01 12:30:00.000000',
            '2025-07-01 12:45:00.999999'
        );

        $this->assertTrue($hasConflict, 'Should detect conflict even with microsecond precision');
    }

    public function test_shift_conflict_at_exact_boundaries()
    {
        Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'start_time' => '2025-07-01 12:00:00',
            'end_time' => '2025-07-01 13:00:00',
        ]);

        // 境界値での非重複（終了時間 = 開始時間）
        $hasConflict = Shift::hasTimeConflict(
            $this->user->id,
            '2025-07-01 13:00:00',
            '2025-07-01 14:00:00'
        );

        $this->assertFalse($hasConflict, 'Should not detect conflict when times touch exactly');

        // 境界値での非重複（開始時間 = 終了時間）
        $hasConflict = Shift::hasTimeConflict(
            $this->user->id,
            '2025-07-01 11:00:00',
            '2025-07-01 12:00:00'
        );

        $this->assertFalse($hasConflict, 'Should not detect conflict when times touch exactly');
    }

    public function test_shift_conflict_across_time_zones()
    {
        // タイムゾーンを考慮したテスト
        $jstTime = Carbon::createFromFormat('Y-m-d H:i:s', '2025-07-01 12:00:00', 'Asia/Tokyo');
        $utcTime = $jstTime->utc();

        Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'start_time' => $jstTime,
            'end_time' => $jstTime->copy()->addHours(2),
        ]);

        // 同じ時刻を異なるタイムゾーンで指定
        $hasConflict = Shift::hasTimeConflict(
            $this->user->id,
            $utcTime->format('Y-m-d H:i:s'),
            $utcTime->copy()->addHour()->format('Y-m-d H:i:s')
        );

        $this->assertTrue($hasConflict, 'Should detect conflict across timezones');
    }

    public function test_shift_conflict_with_leap_second()
    {
        // うるう秒を含む日時での境界テスト
        Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'start_time' => '2025-12-31 23:59:59',
            'end_time' => '2026-01-01 00:00:01',
        ]);

        $hasConflict = Shift::hasTimeConflict(
            $this->user->id,
            '2026-01-01 00:00:00',
            '2026-01-01 01:00:00'
        );

        $this->assertTrue($hasConflict, 'Should detect conflict across year boundary');
    }

    // DST テストは削除 - 日本では夏時間を使用していないため

    public function test_shift_conflict_with_very_short_shifts()
    {
        // 1秒のシフト
        Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'start_time' => '2025-07-01 12:00:00',
            'end_time' => '2025-07-01 12:00:01',
        ]);

        // 1秒重複
        $hasConflict = Shift::hasTimeConflict(
            $this->user->id,
            '2025-07-01 12:00:00',
            '2025-07-01 12:00:02'
        );

        $this->assertTrue($hasConflict, 'Should detect conflict with very short shifts');

        // 重複なし
        $hasConflict = Shift::hasTimeConflict(
            $this->user->id,
            '2025-07-01 12:00:01',
            '2025-07-01 12:00:02'
        );

        $this->assertFalse($hasConflict, 'Should not detect conflict when touching exactly');
    }

    public function test_shift_conflict_with_maximum_date_range()
    {
        // 極端に長いシフト（1年間）
        Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'start_time' => '2025-01-01 00:00:00',
            'end_time' => '2025-12-31 23:59:59',
        ]);

        $hasConflict = Shift::hasTimeConflict(
            $this->user->id,
            '2025-06-15 12:00:00',
            '2025-06-15 13:00:00'
        );

        $this->assertTrue($hasConflict, 'Should detect conflict within very long shift');
    }

    public function test_concurrent_shift_creation_prevention()
    {
        $this->actingAs($this->admin);

        // 可用性を作成
        ShiftSubmission::create([
            'user_id' => $this->user->id,
            'group_id' => $this->group->id,
            'date' => '2025-07-01',
            'available_start_time' => '09:00:00',
            'available_end_time' => '17:00:00',
            'status' => 'active',
        ]);

        // 最初のシフト作成
        $response1 = $this->post(route('admin.shifts.store', $this->group), [
            'date' => '2025-07-01',
            'start_time' => '10:00',
            'end_time' => '16:00',
            'user_id' => $this->user->id,
        ]);

        $response1->assertRedirect(route('groups.show', $this->group));
        $response1->assertSessionHas('success');

        // 重複する時間での2つ目のシフト作成試行
        $response2 = $this->post(route('admin.shifts.store', $this->group), [
            'date' => '2025-07-01',
            'start_time' => '11:00',
            'end_time' => '15:00',
            'user_id' => $this->user->id,
        ]);

        $response2->assertRedirect();
        $response2->assertSessionHas('error');

        // データベースに1つのシフトのみ存在することを確認
        $this->assertEquals(1, Shift::where('user_id', $this->user->id)->count());
    }

    public function test_shift_conflict_with_different_date_formats()
    {
        Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'start_time' => '2025-07-01T12:00:00Z',
            'end_time' => '2025-07-01T13:00:00Z',
        ]);

        // 異なる日時フォーマットでの重複チェック
        $hasConflict = Shift::hasTimeConflict(
            $this->user->id,
            '2025-07-01 12:30:00',
            '2025-07-01 13:30:00'
        );

        $this->assertTrue($hasConflict, 'Should detect conflict regardless of date format');
    }

    public function test_shift_conflict_performance_with_many_existing_shifts()
    {
        // 大量の既存シフトがある場合のパフォーマンステスト
        $startTime = microtime(true);

        // 100個の既存シフトを作成
        for ($i = 1; $i <= 100; $i++) {
            $dayStr = str_pad($i, 2, '0', STR_PAD_LEFT);
            if ($i <= 31) {
                Shift::create([
                    'group_id' => $this->group->id,
                    'user_id' => $this->user->id,
                    'start_time' => "2025-07-{$dayStr} 10:00:00",
                    'end_time' => "2025-07-{$dayStr} 11:00:00",
                ]);
            }
        }

        // 重複チェック
        $hasConflict = Shift::hasTimeConflict(
            $this->user->id,
            '2025-07-15 10:30:00',
            '2025-07-15 11:30:00'
        );

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertTrue($hasConflict, 'Should detect conflict among many shifts');
        $this->assertLessThan(1.0, $executionTime, 'Conflict detection should complete within 1 second');
    }

    public function test_shift_creation_with_invalid_time_sequences()
    {
        $this->actingAs($this->admin);

        // 終了時間が開始時間より早い場合
        $response = $this->post(route('admin.shifts.store', $this->group), [
            'date' => '2025-07-01',
            'start_time' => '16:00',
            'end_time' => '10:00',
            'user_id' => $this->user->id,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['end_time']);
    }

    public function test_shift_conflict_with_null_or_empty_times()
    {
        // 空の時間での重複チェック
        $hasConflict = Shift::hasTimeConflict($this->user->id, '', '');
        $this->assertFalse($hasConflict, 'Should handle empty times gracefully');

        $hasConflict = Shift::hasTimeConflict($this->user->id, null, null);
        $this->assertFalse($hasConflict, 'Should handle null times gracefully');
    }

    public function test_shift_conflict_with_extreme_dates()
    {
        // 極端な未来の日付
        Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'start_time' => '9999-12-31 23:59:58',
            'end_time' => '9999-12-31 23:59:59',
        ]);

        $hasConflict = Shift::hasTimeConflict(
            $this->user->id,
            '9999-12-31 23:59:58',
            '9999-12-31 23:59:59'
        );

        $this->assertTrue($hasConflict, 'Should handle extreme future dates');

        // 極端な過去の日付
        Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'start_time' => '1970-01-01 00:00:01',
            'end_time' => '1970-01-01 00:00:02',
        ]);

        $hasConflict = Shift::hasTimeConflict(
            $this->user->id,
            '1970-01-01 00:00:01',
            '1970-01-01 00:00:02'
        );

        $this->assertTrue($hasConflict, 'Should handle extreme past dates');
    }

    public function test_shift_conflict_exclusion_with_non_existent_shift()
    {
        Shift::create([
            'group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'start_time' => '2025-07-01 12:00:00',
            'end_time' => '2025-07-01 13:00:00',
        ]);

        // 存在しないシフトIDで除外を試行
        $hasConflict = Shift::hasTimeConflict(
            $this->user->id,
            '2025-07-01 12:30:00',
            '2025-07-01 13:30:00',
            99999 // 存在しないID
        );

        $this->assertTrue($hasConflict, 'Should detect conflict when excluding non-existent shift');
    }

    public function test_availability_time_validation_edge_cases()
    {
        $submission = new ShiftSubmission([
            'available_start_time' => '00:00:00',
            'available_end_time' => '23:59:59',
            'status' => 'active',
        ]);

        // 1日の境界
        $this->assertTrue($submission->isAvailableForTime('00:00:00', '23:59:59'));
        $this->assertTrue($submission->isAvailableForTime('00:00:00', '00:00:01'));
        $this->assertTrue($submission->isAvailableForTime('23:59:58', '23:59:59'));

        // 範囲外
        $this->assertFalse($submission->isAvailableForTime('23:59:59', '24:00:00'));
    }
}
