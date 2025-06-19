<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shiftRequests = [
            [
                'user_id' => 2, // 田中太郎
                'group_id' => 1, // カフェスタッフ
                'start_time' => '2025-06-22 08:00:00',
                'end_time' => '2025-06-22 16:00:00',
                'requested_people' => 2,
                'status' => true, // 承認済み
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(1),
            ],
            [
                'user_id' => 5, // 山田美香
                'group_id' => 5, // 事務アシスタント
                'start_time' => '2025-06-23 09:00:00',
                'end_time' => '2025-06-23 17:00:00',
                'requested_people' => 1,
                'status' => true, // 承認済み
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subHours(12),
            ],
            [
                'user_id' => 4, // 鈴木一郎
                'group_id' => 4, // コンビニ夜勤
                'start_time' => '2025-06-24 22:00:00',
                'end_time' => '2025-06-25 06:00:00',
                'requested_people' => 2,
                'status' => false, // 未承認
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6),
            ],
            [
                'user_id' => 6, // 渡辺健太
                'group_id' => 4, // コンビニ夜勤
                'start_time' => '2025-06-25 22:00:00',
                'end_time' => '2025-06-26 06:00:00',
                'requested_people' => 1,
                'status' => false, // 未承認
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
        ];

        DB::table('shift_requests')->insert($shiftRequests);
    }
}