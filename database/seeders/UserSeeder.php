<?php

namespace Database\Seeders;

use DateTime;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'email' => 'admin@example.com',
                'name' => '管理者',
                'password' => Hash::make('password'),
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ],
            [
                'email' => 'tanaka@example.com',
                'name' => '田中太郎',
                'password' => Hash::make('password'),
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ],
            [
                'email' => 'sato@example.com',
                'name' => '佐藤花子',
                'password' => Hash::make('password'),
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ],
            [
                'email' => 'suzuki@example.com',
                'name' => '鈴木一郎',
                'password' => Hash::make('password'),
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ],
            [
                'email' => 'yamada@example.com',
                'name' => '山田美香',
                'password' => Hash::make('password'),
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ],
            [
                'email' => 'watanabe@example.com',
                'name' => '渡辺健太',
                'password' => Hash::make('password'),
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ],
        ];

        DB::table('users')->insert($users);
    }
}
