<?php

namespace Database\Seeders;

use App\Models\GroupMember;
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
        if (in_array(1, $existingUsers) && in_array(GroupMember::ROLE_ADMIN, $existingRoles)) {
            foreach ([1, 2, 3] as $groupId) {
                if (in_array($groupId, $existingGroups)) {
                    $groupMembers[] = ['user_id' => 1, 'group_id' => $groupId, 'role_id' => GroupMember::ROLE_ADMIN, 'created_at' => now(), 'updated_at' => now()];
                }
            }
        }

        // 他のユーザーの関係も同様にチェック（存在する場合のみ追加）
        $userGroupMappings = [
            2 => [[1, GroupMember::ROLE_MEMBER], [2, GroupMember::ROLE_MEMBER]], // 田中太郎
            3 => [[2, GroupMember::ROLE_ADMIN], [3, GroupMember::ROLE_MEMBER]], // 佐藤花子  
            4 => [[3, GroupMember::ROLE_MEMBER], [4, GroupMember::ROLE_MEMBER]], // 鈴木一郎
            5 => [[5, GroupMember::ROLE_ADMIN], [1, GroupMember::ROLE_MEMBER]], // 山田美香
            6 => [[4, GroupMember::ROLE_MEMBER]], // 渡辺健太
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
