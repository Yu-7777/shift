<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftSubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shiftSubmissions = [
            [
                'user_id' => 2, // 田中太郎
                'group_id' => 1, // カフェスタッフ
                'start_time' => '2025-06-19 08:00:00',
                'end_time' => '2025-06-19 16:00:00',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'user_id' => 5, // 山田美香
                'group_id' => 1, // カフェスタッフ
                'start_time' => '2025-06-19 08:00:00',
                'end_time' => '2025-06-19 16:00:00',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'user_id' => 3, // 佐藤花子
                'group_id' => 2, // レストランホール
                'start_time' => '2025-06-18 17:00:00',
                'end_time' => '2025-06-18 23:00:00',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'user_id' => 4, // 鈴木一郎
                'group_id' => 3, // キッチンスタッフ
                'start_time' => '2025-06-18 16:00:00',
                'end_time' => '2025-06-19 00:00:00',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'user_id' => 6, // 渡辺健太
                'group_id' => 4, // コンビニ夜勤
                'start_time' => '2025-06-17 22:00:00',
                'end_time' => '2025-06-18 06:00:00',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
        ];

        DB::table('shift_submissions')->insert($shiftSubmissions);
    }
}