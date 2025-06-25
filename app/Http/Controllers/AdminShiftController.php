<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Shift;
use App\Models\ShiftSubmission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminShiftController extends Controller
{
    /**
     * シフト作成フォーム表示
     */
    public function create(Group $group)
    {
        $this->checkAdminPermission($group);

        return view('admin.shifts.create', compact('group'));
    }

    /**
     * 利用可能なユーザー検索
     */
    public function searchAvailableUsers(Request $request, Group $group)
    {
        $this->checkAdminPermission($group);

        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        // 指定時間に利用可能なユーザーを検索
        // 完全包含: 利用可能時間が要求時間を完全に含む場合
        $availableUsers = ShiftSubmission::where('group_id', $group->id)
            ->where('date', $request->date)
            ->where('status', ShiftSubmission::STATUS_ACTIVE)
            ->where('available_start_time', '<=', $request->start_time)
            ->where('available_end_time', '>=', $request->end_time)
            ->with('user')
            ->get();

        return response()->json([
            'availableUsers' => $availableUsers->map(function ($availability) {
                return [
                    'id' => $availability->user->id,
                    'name' => $availability->user->name,
                    'available_start_time' => $availability->available_start_time,
                    'available_end_time' => $availability->available_end_time,
                    'comment' => $availability->comment,
                ];
            }),
        ]);
    }

    /**
     * シフト作成処理
     */
    public function store(Request $request, Group $group)
    {
        $this->checkAdminPermission($group);

        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'user_id' => 'required|exists:users,id',
        ]);

        // 選択されたユーザーがその時間に利用可能かチェック
        $availability = ShiftSubmission::where('group_id', $group->id)
            ->where('user_id', $request->user_id)
            ->where('date', $request->date)
            ->where('status', ShiftSubmission::STATUS_ACTIVE)
            ->where('available_start_time', '<=', $request->start_time)
            ->where('available_end_time', '>=', $request->end_time)
            ->first();

        if (! $availability) {
            return redirect()->back()
                ->with('error', '選択されたユーザーはその時間帯に利用できません');
        }

        // 時間重複チェック
        $startDateTime = Carbon::parse($request->date.' '.$request->start_time);
        $endDateTime = Carbon::parse($request->date.' '.$request->end_time);
        
        if (Shift::hasTimeConflict($request->user_id, $startDateTime, $endDateTime)) {
            return redirect()->back()
                ->with('error', '選択されたユーザーは指定時間に既に他のシフトが入っています');
        }

        // シフト作成
        $shift = Shift::create([
            'group_id' => $group->id,
            'user_id' => $request->user_id,
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
        ]);

        return redirect()->route('groups.show', $group)
            ->with('success', 'シフトを作成しました');
    }

    /**
     * シフト一覧表示（管理者用）
     */
    public function index(Group $group)
    {
        $this->checkAdminPermission($group);

        $shifts = Shift::where('group_id', $group->id)
            ->where('start_time', '>=', Carbon::today())
            ->with('user')
            ->orderBy('start_time')
            ->get();

        return view('admin.shifts.index', compact('group', 'shifts'));
    }

    /**
     * 可用性一覧表示（管理者用）
     */
    public function availabilities(Group $group)
    {
        $this->checkAdminPermission($group);

        $availabilities = ShiftSubmission::where('group_id', $group->id)
            ->where('date', '>=', Carbon::today())
            ->where('status', ShiftSubmission::STATUS_ACTIVE)
            ->with('user')
            ->orderBy('date')
            ->orderBy('available_start_time')
            ->get()
            ->groupBy(function ($item) {
                return $item->date->format('Y-m-d');
            });

        return view('admin.availabilities.index', compact('group', 'availabilities'));
    }

    /**
     * シフト削除
     */
    public function destroy(Group $group, Shift $shift)
    {
        $this->checkAdminPermission($group);

        if ($shift->group_id !== $group->id) {
            abort(404);
        }

        $shift->delete();

        return redirect()->back()
            ->with('success', 'シフトを削除しました');
    }

    /**
     * 管理者権限チェック
     */
    private function checkAdminPermission(Group $group)
    {
        // ユーザーがログインしているかチェック
        if (!auth()->check()) {
            abort(401, 'ログインが必要です');
        }

        $groupMember = GroupMember::where('group_id', $group->id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$groupMember) {
            abort(403, 'このグループのメンバーではありません');
        }

        // role_idで判定（定数ベースでより確実）
        $isAdmin = $groupMember->role_id === GroupMember::ROLE_ADMIN;

        if (!$isAdmin) {
            abort(403, '管理者権限が必要です');
        }
    }
}
