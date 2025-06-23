<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Shift;
use Carbon\Carbon;
use App\Helpers\CalendarHelper;

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
        $isMember = $group->users()
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
        $members = $group->users;
        
        // カレンダーデータを生成
        $calendar = CalendarHelper::generateMonthCalendar();

        return view('groups.show', compact('group', 'shifts', 'members', 'calendar'));
    }
}
