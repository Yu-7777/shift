<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\ShiftRequest;
use App\Models\ShiftSubmission;
use Illuminate\Http\Request;

class ShiftRequestController extends Controller
{
    /**
     * シフト募集作成フォーム表示
     */
    public function create(Group $group)
    {
        // 管理者権限チェック
        $this->checkAdminPermission($group);

        return view('shift-requests.create', compact('group'));
    }

    /**
     * シフト募集作成処理
     */
    public function store(Request $request, Group $group)
    {
        $this->checkAdminPermission($group);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'requested_people' => 'required|integer|min:1',
            'application_deadline' => 'required|date|before:start_time',
        ]);

        ShiftRequest::create([
            'user_id' => auth()->id(),
            'group_id' => $group->id,
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'requested_people' => $request->requested_people,
            'application_deadline' => $request->application_deadline,
        ]);

        return redirect()->route('groups.show', $group)
            ->with('success', 'シフト募集を作成しました');
    }

    /**
     * シフト募集詳細表示
     */
    public function show(Group $group, ShiftRequest $shiftRequest)
    {
        $this->checkMemberPermission($group);

        // 自動クローズチェック
        $shiftRequest->autoCloseIfDeadlinePassed();

        $userSubmission = null;
        if (auth()->check()) {
            $userSubmission = $shiftRequest->submissions()
                ->where('user_id', auth()->id())
                ->first();
        }

        return view('shift-requests.show', compact('group', 'shiftRequest', 'userSubmission'));
    }

    /**
     * シフト調整画面（管理者用）
     */
    public function assign(Group $group, ShiftRequest $shiftRequest)
    {
        $this->checkAdminPermission($group);

        // 応募一覧を取得
        $submissions = $shiftRequest->submissions()
            ->with('user')
            ->where('status', ShiftSubmission::STATUS_PENDING)
            ->get();

        return view('shift-requests.assign', compact('group', 'shiftRequest', 'submissions'));
    }

    /**
     * シフト選択処理
     */
    public function processAssignment(Request $request, Group $group, ShiftRequest $shiftRequest)
    {
        $this->checkAdminPermission($group);

        $request->validate([
            'actual_start_time' => 'required|date_format:Y-m-d H:i',
            'actual_end_time' => 'required|date_format:Y-m-d H:i|after:actual_start_time',
            'selected_submissions' => 'required|array',
            'selected_submissions.*' => 'exists:shift_submissions,id',
        ]);

        // 選択されたアプリケーションが時間範囲内かチェック
        $actualStartTime = $request->actual_start_time;
        $actualEndTime = $request->actual_end_time;

        $selectedSubmissions = ShiftSubmission::whereIn('id', $request->selected_submissions)->get();

        foreach ($selectedSubmissions as $submission) {
            if (! $submission->isAvailableForTime($actualStartTime, $actualEndTime)) {
                return redirect()->back()
                    ->with('error', "{$submission->user->name}さんは指定時間で対応できません");
            }

            // 時間重複チェック
            if (\App\Models\Shift::hasTimeConflict($submission->user_id, $actualStartTime, $actualEndTime)) {
                return redirect()->back()
                    ->with('error', "{$submission->user->name}さんは指定時間に既に他のシフトが入っています");
            }
        }

        // 選択されたアプリケーションを更新
        ShiftSubmission::whereIn('id', $request->selected_submissions)
            ->update(['status' => ShiftSubmission::STATUS_SELECTED]);

        // 選択されなかったアプリケーションを却下
        $shiftRequest->submissions()
            ->whereNotIn('id', $request->selected_submissions)
            ->update(['status' => ShiftSubmission::STATUS_REJECTED]);

        // 実際のシフト作成（1:1関係なので各ユーザーごとに個別のシフトを作成）
        $selectedUserIds = $selectedSubmissions->pluck('user_id');
        
        foreach ($selectedUserIds as $userId) {
            $group->shifts()->create([
                'user_id' => $userId,
                'start_time' => $actualStartTime,
                'end_time' => $actualEndTime,
            ]);
        }

        // シフト募集のステータスを更新
        $shiftRequest->update(['status' => ShiftRequest::STATUS_ASSIGNED]);

        return redirect()->route('groups.show', $group)
            ->with('success', 'シフトが決定されました');
    }

    /**
     * 管理者権限チェック
     */
    private function checkAdminPermission(Group $group)
    {
        $isMember = $group->users()
            ->where('users.id', auth()->id())
            ->wherePivot('role_id', \App\Models\GroupMember::ROLE_ADMIN)
            ->exists();

        if (! $isMember) {
            abort(403, '管理者権限が必要です');
        }
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
