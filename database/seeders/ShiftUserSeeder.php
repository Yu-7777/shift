<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('shift_users')->insert([
            'user_id' => 1,
            'shift_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
