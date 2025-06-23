<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Shift;
use Carbon\Carbon;

class GroupController extends Controller
{
    // バイトグループの一覧を取得
    public function index()
    {

    }

    // グループホーム画面
    public function show(Group $group)
    {
        // ユーザーがこのグループのメンバーかチェック
        $isMember = $group->belongsToMany(\App\Models\User::class, 'group_members')
            ->where('users.id', auth()->id())
            ->exists();

        if (!$isMember) {
            abort(403, 'このグループにアクセスする権限がありません');
        }

        // 今月のシフトを取得
        $shifts = $group->shifts()
            ->whereYear('start_time', Carbon::now()->year)
            ->whereMonth('start_time', Carbon::now()->month)
            ->with('users')
            ->get();

        // グループメンバーを取得
        $members = $group->belongsToMany(\App\Models\User::class, 'group_members')
            ->withPivot('role_id')
            ->get();

        return view('groups.show', compact('group', 'shifts', 'members'));
    }
}
