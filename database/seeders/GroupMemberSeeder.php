<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groupMembers = [
            // 管理者は全グループのアドミン
            ['user_id' => 1, 'group_id' => 1, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 1, 'group_id' => 2, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 1, 'group_id' => 3, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            
            // 田中太郎 - カフェとレストランのメンバー
            ['user_id' => 2, 'group_id' => 1, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 2, 'group_id' => 2, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            
            // 佐藤花子 - レストランホールのアドミン、キッチンのメンバー
            ['user_id' => 3, 'group_id' => 2, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 3, 'group_id' => 3, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            
            // 鈴木一郎 - キッチンスタッフとコンビニ夜勤
            ['user_id' => 4, 'group_id' => 3, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 4, 'group_id' => 4, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            
            // 山田美香 - 事務アシスタントのアドミン、カフェメンバー
            ['user_id' => 5, 'group_id' => 5, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 5, 'group_id' => 1, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            
            // 渡辺健太 - コンビニ夜勤のメンバー
            ['user_id' => 6, 'group_id' => 4, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('group_members')->insert($groupMembers);
    }
}
