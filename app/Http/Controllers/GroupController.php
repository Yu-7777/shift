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
        $isMember = $group->group_members()
            ->where('user_id', auth()->id())
            ->exists();

        if (!$isMember) {
            abort(403, 'このグループにアクセスする権限がありません');
        }

        // 今月のシフトを取得
        $currentMonth = Carbon::now()->format('Y-m');
        $shifts = $group->shifts()
            ->whereRaw('DATE_FORMAT(start_time, "%Y-%m") = ?', [$currentMonth])
            ->with('users')
            ->get();

        // グループメンバーを取得
        $members = $group->belongsToMany(\App\Models\User::class, 'group_members')
            ->withPivot('role_id')
            ->get();

        return view('groups.show', compact('group', 'shifts', 'members'));
    }
}
