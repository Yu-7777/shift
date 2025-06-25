<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftSubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $availabilities = [
            [
                'user_id' => 2, // 田中太郎
                'group_id' => 1, // カフェスタッフ
                'date' => '2025-06-26',
                'available_start_time' => '08:00',
                'available_end_time' => '16:00',
                'comment' => '朝から午後まで対応可能',
                'status' => 'active',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'user_id' => 5, // 山田美香
                'group_id' => 1, // カフェスタッフ
                'date' => '2025-06-27',
                'available_start_time' => '09:00',
                'available_end_time' => '17:00',
                'comment' => '通常時間で勤務可能',
                'status' => 'active',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'user_id' => 3, // 佐藤花子
                'group_id' => 2, // レストランホール
                'date' => '2025-06-28',
                'available_start_time' => '17:00',
                'available_end_time' => '23:00',
                'comment' => 'ディナータイム対応',
                'status' => 'active',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'user_id' => 4, // 鈴木一郎
                'group_id' => 3, // キッチンスタッフ
                'date' => '2025-06-29',
                'available_start_time' => '16:00',
                'available_end_time' => '22:00',
                'comment' => '夕方から夜まで対応可能',
                'status' => 'active',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'user_id' => 6, // 渡辺健太
                'group_id' => 4, // コンビニ夜勤
                'date' => '2025-06-30',
                'available_start_time' => '22:00',
                'available_end_time' => '06:00',
                'comment' => '深夜勤務対応可能',
                'status' => 'active',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
        ];

        DB::table('shift_submissions')->insert($availabilities);
    }
}
