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
        DB::table('shifts')->insert([
            'group_id' => 1,
            'start_time' => '2023-10-01 09:00:00',
            'end_time' => '2023-10-01 17:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
