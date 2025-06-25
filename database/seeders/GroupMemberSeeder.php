<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 既存のユーザーとグループが存在するかチェック
        $existingUsers = DB::table('users')->pluck('id')->toArray();
        $existingGroups = DB::table('groups')->pluck('id')->toArray();
        $existingRoles = DB::table('roles')->pluck('id')->toArray();

        $groupMembers = [];

        // 管理者は全グループのアドミン（存在する場合のみ）
        if (in_array(1, $existingUsers) && in_array(1, $existingRoles)) {
            foreach ([1, 2, 3] as $groupId) {
                if (in_array($groupId, $existingGroups)) {
                    $groupMembers[] = ['user_id' => 1, 'group_id' => $groupId, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()];
                }
            }
        }

        // 他のユーザーの関係も同様にチェック（存在する場合のみ追加）
        $userGroupMappings = [
            2 => [[1, 2], [2, 2]], // 田中太郎
            3 => [[2, 1], [3, 2]], // 佐藤花子  
            4 => [[3, 2], [4, 2]], // 鈴木一郎
            5 => [[5, 1], [1, 2]], // 山田美香
            6 => [[4, 2]], // 渡辺健太
        ];

        foreach ($userGroupMappings as $userId => $mappings) {
            if (in_array($userId, $existingUsers)) {
                foreach ($mappings as [$groupId, $roleId]) {
                    if (in_array($groupId, $existingGroups) && in_array($roleId, $existingRoles)) {
                        $groupMembers[] = ['user_id' => $userId, 'group_id' => $groupId, 'role_id' => $roleId, 'created_at' => now(), 'updated_at' => now()];
                    }
                }
            }
        }

        if (!empty($groupMembers)) {
            DB::table('group_members')->insert($groupMembers);
        }
    }
}
