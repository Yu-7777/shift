<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            // カフェスタッフのシフト - 田中太郎
            [
                'group_id' => 1,
                'user_id' => 2,
                'start_time' => '2025-06-26 08:00:00',
                'end_time' => '2025-06-26 16:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // カフェスタッフのシフト - 山田美香
            [
                'group_id' => 1,
                'user_id' => 5,
                'start_time' => '2025-06-27 09:00:00',
                'end_time' => '2025-06-27 17:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // レストランホールのシフト - 佐藤花子
            [
                'group_id' => 2,
                'user_id' => 3,
                'start_time' => '2025-06-28 17:00:00',
                'end_time' => '2025-06-28 23:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // キッチンスタッフのシフト - 鈴木一郎
            [
                'group_id' => 3,
                'user_id' => 4,
                'start_time' => '2025-06-29 16:00:00',
                'end_time' => '2025-06-29 22:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // コンビニ夜勤のシフト - 渡辺健太
            [
                'group_id' => 4,
                'user_id' => 6,
                'start_time' => '2025-06-30 22:00:00',
                'end_time' => '2025-07-01 06:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('shifts')->insert($shifts);
    }
}
