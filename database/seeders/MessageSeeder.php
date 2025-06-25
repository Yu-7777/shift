<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $messages = [
            // カフェスタッフ全体チャット(chat_id: 1)
            [
                'user_id' => 1,
                'chat_id' => 1,
                'body' => 'カフェスタッフの皆さん、おつかれさまです！今週のシフト確認をお願いします。',
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
            [
                'user_id' => 2,
                'chat_id' => 1,
                'body' => '田中です。今週のシフト確認しました！',
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subHours(1),
            ],
            [
                'user_id' => 5,
                'chat_id' => 1,
                'body' => '山田です。私も確認できています。よろしくお願いします。',
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(30),
            ],

            // レストランホールチャット(chat_id: 2)
            [
                'user_id' => 3,
                'chat_id' => 2,
                'body' => 'レストランの予約状況ですが、今日の夜は満席予定です。',
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ],
            [
                'user_id' => 2,
                'chat_id' => 2,
                'body' => '了解です。準備を進めますね！',
                'created_at' => now()->subHours(2)->subMinutes(30),
                'updated_at' => now()->subHours(2)->subMinutes(30),
            ],

            // DM(chat_id: 3)
            [
                'user_id' => 2,
                'chat_id' => 3,
                'body' => '佐藤さん、明日のシフトの件で相談があります。',
                'created_at' => now()->subHours(4),
                'updated_at' => now()->subHours(4),
            ],
            [
                'user_id' => 3,
                'chat_id' => 3,
                'body' => 'はい、何でしょうか？',
                'created_at' => now()->subHours(3)->subMinutes(45),
                'updated_at' => now()->subHours(3)->subMinutes(45),
            ],
            [
                'user_id' => 2,
                'chat_id' => 3,
                'body' => '時間を30分早めることは可能でしょうか？',
                'created_at' => now()->subHours(3)->subMinutes(30),
                'updated_at' => now()->subHours(3)->subMinutes(30),
            ],
            [
                'user_id' => 3,
                'chat_id' => 3,
                'body' => '大丈夫です！調整します。',
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ],
        ];

        DB::table('messages')->insert($messages);
    }
}
