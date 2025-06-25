<?php

namespace App\Http\Controllers;

use App\Models\Group;

class GroupMemberController extends Controller
{
    // グループに所属しているバイトメンバーを一覧取得
    public function index(Group $group)
    {
        $members = $group->members();

        return view('groups.index', compact('group', 'members'));
    }
}
