<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shiftUsers = [
            // カフェシフト1に田中、山田がアサイン
            ['user_id' => 2, 'shift_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 5, 'shift_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            
            // カフェシフト2に田中がアサイン
            ['user_id' => 2, 'shift_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            
            // レストランシフト1に田中、佐藤がアサイン
            ['user_id' => 2, 'shift_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 3, 'shift_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            
            // キッチンシフトに佐藤、鈴木がアサイン
            ['user_id' => 3, 'shift_id' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 4, 'shift_id' => 5, 'created_at' => now(), 'updated_at' => now()],
            
            // コンビニ夜勤に鈴木、渡辺がアサイン
            ['user_id' => 4, 'shift_id' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 6, 'shift_id' => 6, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('shift_users')->insert($shiftUsers);
    }
}
