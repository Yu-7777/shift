<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\ShiftSubmission;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    /**
     * 可用性入力フォーム表示
     */
    public function create(Group $group)
    {
        $this->checkMemberPermission($group);

        // 今日から30日後までの可用性を取得
        $existingAvailabilities = ShiftSubmission::where('user_id', auth()->id())
            ->where('group_id', $group->id)
            ->where('date', '>=', Carbon::today())
            ->where('date', '<=', Carbon::today()->addDays(30))
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        return view('availability.create', compact('group', 'existingAvailabilities'));
    }

    /**
     * 可用性登録処理
     */
    public function store(Request $request, Group $group)
    {
        $this->checkMemberPermission($group);

        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'available_start_time' => 'required|date_format:H:i',
            'available_end_time' => 'required|date_format:H:i|after:available_start_time',
            'comment' => 'nullable|string|max:500',
        ]);

        // 既存の可用性をチェック
        $existing = ShiftSubmission::where('user_id', auth()->id())
            ->where('group_id', $group->id)
            ->where('date', $request->date)
            ->first();

        if ($existing) {
            // 更新
            $existing->update([
                'available_start_time' => $request->available_start_time,
                'available_end_time' => $request->available_end_time,
                'comment' => $request->comment,
                'status' => ShiftSubmission::STATUS_ACTIVE,
            ]);
            $message = '可用性を更新しました';
        } else {
            // 新規作成
            ShiftSubmission::create([
                'user_id' => auth()->id(),
                'group_id' => $group->id,
                'date' => $request->date,
                'available_start_time' => $request->available_start_time,
                'available_end_time' => $request->available_end_time,
                'comment' => $request->comment,
                'status' => ShiftSubmission::STATUS_ACTIVE,
            ]);
            $message = '可用性を登録しました';
        }

        return redirect()->route('availability.create', $group)
            ->with('success', $message);
    }

    /**
     * 可用性一覧表示
     */
    public function index(Group $group)
    {
        $this->checkMemberPermission($group);

        $availabilities = ShiftSubmission::where('user_id', auth()->id())
            ->where('group_id', $group->id)
            ->where('date', '>=', Carbon::today())
            ->orderBy('date')
            ->get();

        return view('availability.index', compact('group', 'availabilities'));
    }

    /**
     * 可用性削除
     */
    public function destroy(Group $group, ShiftSubmission $availability)
    {
        $this->checkMemberPermission($group);

        if ($availability->user_id !== auth()->id()) {
            abort(403, '他のユーザーの可用性は削除できません');
        }

        $availability->delete();

        return redirect()->route('availability.index', $group)
            ->with('success', '可用性を削除しました');
    }

    /**
     * メンバー権限チェック
     */
    private function checkMemberPermission(Group $group)
    {
        $isMember = $group->users()
            ->where('users.id', auth()->id())
            ->exists();

        if (! $isMember) {
            abort(403, 'このグループにアクセスする権限がありません');
        }
    }
}
