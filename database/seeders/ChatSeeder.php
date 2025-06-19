<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chats = [
            [
                'id' => 1,
                'name' => 'カフェスタッフ全体',
                'type' => 'group',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'レストランホール',
                'type' => 'group',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => '田中-佐藤DM',
                'type' => 'dm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('chats')->insert($chats);

        // チャットメンバー
        $chatMembers = [
            // カフェスタッフ全体チャット
            ['user_id' => 1, 'chat_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 2, 'chat_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 5, 'chat_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            
            // レストランホールチャット
            ['user_id' => 1, 'chat_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 2, 'chat_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 3, 'chat_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            
            // DM
            ['user_id' => 2, 'chat_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 3, 'chat_id' => 3, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('chat_members')->insert($chatMembers);
    }
}