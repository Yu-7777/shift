<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Group;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * チャット一覧表示
     */
    public function index()
    {
        $user = Auth::user();
        
        $chats = $user->chats()
            ->with(['messages' => function($query) {
                $query->latest()->limit(1);
            }, 'users'])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('chats.index', compact('chats'));
    }

    /**
     * 特定のチャット表示
     */
    public function show(Chat $chat)
    {
        // ユーザーがこのチャットのメンバーかチェック
        $this->checkChatMembership($chat);

        $messages = $chat->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        $chatMembers = $chat->users;

        return view('chats.show', compact('chat', 'messages', 'chatMembers'));
    }

    /**
     * グループチャット作成（グループ用）
     */
    public function createGroupChat(Group $group)
    {
        // ユーザーがグループのメンバーかチェック
        $this->checkGroupMembership($group);

        DB::beginTransaction();
        try {
            // グループ用チャットを作成
            $chat = Chat::create([
                'name' => $group->name . 'チャット',
                'type' => Chat::TYPE_GROUP
            ]);

            // グループの全メンバーをチャットに追加
            $groupMembers = $group->users()->pluck('id');
            $chat->users()->attach($groupMembers);

            DB::commit();

            return redirect()->route('chats.show', $chat)
                ->with('success', 'グループチャットを作成しました');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'チャットの作成に失敗しました');
        }
    }

    /**
     * DM（ダイレクトメッセージ）作成
     */
    public function createDM(User $user)
    {
        $currentUser = Auth::user();

        // 自分自身とはDMできない
        if ($currentUser->id === $user->id) {
            return back()->with('error', '自分自身とのDMは作成できません');
        }

        // 既存のDMがあるかチェック
        $existingDM = $this->findExistingDM($currentUser->id, $user->id);
        
        if ($existingDM) {
            return redirect()->route('chats.show', $existingDM)
                ->with('info', '既存のDMに移動しました');
        }

        DB::beginTransaction();
        try {
            // DM作成
            $chat = Chat::create([
                'name' => $currentUser->name . '-' . $user->name . 'DM',
                'type' => Chat::TYPE_DM
            ]);

            // 両ユーザーをチャットに追加
            $chat->users()->attach([$currentUser->id, $user->id]);

            DB::commit();

            return redirect()->route('chats.show', $chat)
                ->with('success', 'DMを作成しました');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'DMの作成に失敗しました');
        }
    }

    /**
     * チャット削除（管理者のみ）
     */
    public function destroy(Chat $chat)
    {
        // チャットのメンバーかチェック
        $this->checkChatMembership($chat);

        // グループチャットの場合は管理者のみ削除可能
        if ($chat->type === Chat::TYPE_GROUP) {
            // TODO: グループ管理者権限のチェックを実装
        }

        DB::beginTransaction();
        try {
            // 関連するメッセージとメンバーシップを削除
            $chat->messages()->delete();
            $chat->users()->detach();
            $chat->delete();

            DB::commit();

            return redirect()->route('chats.index')
                ->with('success', 'チャットを削除しました');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'チャットの削除に失敗しました');
        }
    }

    /**
     * メッセージ送信
     */
    public function sendMessage(Request $request, Chat $chat)
    {
        // チャットのメンバーかチェック
        $this->checkChatMembership($chat);

        $request->validate([
            'body' => 'required|string|max:1000'
        ]);

        try {
            Message::create([
                'user_id' => Auth::id(),
                'chat_id' => $chat->id,
                'body' => $request->body
            ]);

            // チャットの最終更新時刻を更新
            $chat->touch();

            return back()->with('success', 'メッセージを送信しました');

        } catch (\Exception $e) {
            return back()->with('error', 'メッセージの送信に失敗しました');
        }
    }

    /**
     * ユーザー検索（DM作成用）
     */
    public function searchUsers(Request $request)
    {
        $query = $request->get('q');
        
        $users = User::where('name', 'like', "%{$query}%")
            ->where('id', '!=', Auth::id())
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json($users);
    }

    /**
     * チャットメンバーシップの確認
     */
    private function checkChatMembership(Chat $chat)
    {
        $isMember = $chat->users()
            ->where('users.id', Auth::id())
            ->exists();

        if (!$isMember) {
            abort(403, 'このチャットにアクセスする権限がありません');
        }
    }

    /**
     * グループメンバーシップの確認
     */
    private function checkGroupMembership(Group $group)
    {
        $isMember = $group->users()
            ->where('users.id', Auth::id())
            ->exists();

        if (!$isMember) {
            abort(403, 'このグループにアクセスする権限がありません');
        }
    }

    /**
     * 既存のDMを検索
     */
    private function findExistingDM(int $userId1, int $userId2)
    {
        return Chat::where('type', Chat::TYPE_DM)
            ->whereHas('users', function($query) use ($userId1) {
                $query->where('users.id', $userId1);
            })
            ->whereHas('users', function($query) use ($userId2) {
                $query->where('users.id', $userId2);
            })
            ->first();
    }
}
