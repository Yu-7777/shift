<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            // カフェスタッフのシフト
            [
                'group_id' => 1,
                'start_time' => '2025-06-20 08:00:00',
                'end_time' => '2025-06-20 16:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group_id' => 1,
                'start_time' => '2025-06-21 08:00:00',
                'end_time' => '2025-06-21 16:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // レストランホールのシフト
            [
                'group_id' => 2,
                'start_time' => '2025-06-20 17:00:00',
                'end_time' => '2025-06-20 23:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group_id' => 2,
                'start_time' => '2025-06-21 17:00:00',
                'end_time' => '2025-06-21 23:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // キッチンスタッフのシフト
            [
                'group_id' => 3,
                'start_time' => '2025-06-20 16:00:00',
                'end_time' => '2025-06-21 00:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // コンビニ夜勤のシフト
            [
                'group_id' => 4,
                'start_time' => '2025-06-20 22:00:00',
                'end_time' => '2025-06-21 06:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('shifts')->insert($shifts);
    }
}
