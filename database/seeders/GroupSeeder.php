<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                'name' => 'カフェスタッフ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'レストランホール',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'キッチンスタッフ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'コンビニ夜勤',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '事務アシスタント',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('groups')->insert($groups);
    }
}
