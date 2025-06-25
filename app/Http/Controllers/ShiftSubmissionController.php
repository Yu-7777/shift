<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\ShiftRequest;
use App\Models\ShiftSubmission;
use Illuminate\Http\Request;

class ShiftSubmissionController extends Controller
{
    /**
     * シフト応募作成フォーム表示
     */
    public function create(Group $group, ShiftRequest $shiftRequest)
    {
        $this->checkMemberPermission($group);

        // 応募可能かチェック
        if (! $shiftRequest->canApply()) {
            return redirect()->route('shift-requests.show', [$group, $shiftRequest])
                ->with('error', '応募期限が過ぎているか、募集が終了しています');
        }

        // 既に応募しているかチェック
        $existingSubmission = $shiftRequest->submissions()
            ->where('user_id', auth()->id())
            ->first();

        if ($existingSubmission) {
            return redirect()->route('shift-submissions.edit', [$group, $shiftRequest, $existingSubmission])
                ->with('info', '既に応募済みです。編集画面に移動しました');
        }

        return view('shift-submissions.create', compact('group', 'shiftRequest'));
    }

    /**
     * シフト応募作成処理
     */
    public function store(Request $request, Group $group, ShiftRequest $shiftRequest)
    {
        $this->checkMemberPermission($group);

        if (! $shiftRequest->canApply()) {
            return redirect()->route('shift-requests.show', [$group, $shiftRequest])
                ->with('error', '応募期限が過ぎているか、募集が終了しています');
        }

        $request->validate([
            'start_time' => 'required|date|after_or_equal:'.$shiftRequest->start_time,
            'end_time' => 'required|date|before_or_equal:'.$shiftRequest->end_time.'|after:start_time',
            'comment' => 'nullable|string|max:1000',
        ]);

        ShiftSubmission::create([
            'shift_request_id' => $shiftRequest->id,
            'user_id' => auth()->id(),
            'group_id' => $group->id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'comment' => $request->comment,
        ]);

        return redirect()->route('shift-requests.show', [$group, $shiftRequest])
            ->with('success', 'シフトに応募しました');
    }

    /**
     * シフト応募編集フォーム表示
     */
    public function edit(Group $group, ShiftRequest $shiftRequest, ShiftSubmission $shiftSubmission)
    {
        $this->checkMemberPermission($group);
        $this->checkSubmissionOwner($shiftSubmission);

        // 応募可能かチェック
        if (! $shiftRequest->canApply()) {
            return redirect()->route('shift-requests.show', [$group, $shiftRequest])
                ->with('error', '応募期限が過ぎているため、編集できません');
        }

        // 選択済みの場合は編集不可
        if ($shiftSubmission->isSelected()) {
            return redirect()->route('shift-requests.show', [$group, $shiftRequest])
                ->with('error', '選択済みのため、編集できません');
        }

        return view('shift-submissions.edit', compact('group', 'shiftRequest', 'shiftSubmission'));
    }

    /**
     * シフト応募更新処理
     */
    public function update(Request $request, Group $group, ShiftRequest $shiftRequest, ShiftSubmission $shiftSubmission)
    {
        $this->checkMemberPermission($group);
        $this->checkSubmissionOwner($shiftSubmission);

        if (! $shiftRequest->canApply() || $shiftSubmission->isSelected()) {
            return redirect()->route('shift-requests.show', [$group, $shiftRequest])
                ->with('error', '更新できません');
        }

        $request->validate([
            'start_time' => 'required|date|after_or_equal:'.$shiftRequest->start_time,
            'end_time' => 'required|date|before_or_equal:'.$shiftRequest->end_time.'|after:start_time',
            'comment' => 'nullable|string|max:1000',
        ]);

        $shiftSubmission->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'comment' => $request->comment,
        ]);

        return redirect()->route('shift-requests.show', [$group, $shiftRequest])
            ->with('success', '応募内容を更新しました');
    }

    /**
     * シフト応募削除処理
     */
    public function destroy(Group $group, ShiftRequest $shiftRequest, ShiftSubmission $shiftSubmission)
    {
        $this->checkMemberPermission($group);
        $this->checkSubmissionOwner($shiftSubmission);

        // 選択済みの場合は削除不可
        if ($shiftSubmission->isSelected()) {
            return redirect()->route('shift-requests.show', [$group, $shiftRequest])
                ->with('error', '選択済みのため、削除できません');
        }

        $shiftSubmission->delete();

        return redirect()->route('shift-requests.show', [$group, $shiftRequest])
            ->with('success', '応募を取り消しました');
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

    /**
     * 応募者本人チェック
     */
    private function checkSubmissionOwner(ShiftSubmission $shiftSubmission)
    {
        if ($shiftSubmission->user_id !== auth()->id()) {
            abort(403, '他のユーザーの応募は編集できません');
        }
    }
}
