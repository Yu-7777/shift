<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;
    private Group $group;
    private Chat $dmChat;
    private Chat $groupChat;

    protected function setUp(): void
    {
        parent::setUp();
        
        // シーダーを実行してRoleを作成
        $this->seed(\Database\Seeders\RoleSeeder::class);
        
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->group = Group::factory()->create();

        // ユーザーをグループに追加
        GroupMember::create([
            'user_id' => $this->user->id,
            'group_id' => $this->group->id,
            'role_id' => GroupMember::ROLE_MEMBER
        ]);

        GroupMember::create([
            'user_id' => $this->otherUser->id,
            'group_id' => $this->group->id,
            'role_id' => GroupMember::ROLE_MEMBER
        ]);

        // DMチャットを作成
        $this->dmChat = Chat::create([
            'type' => Chat::TYPE_DM
        ]);
        $this->dmChat->users()->attach([$this->user->id, $this->otherUser->id]);

        // グループチャットを作成
        $this->groupChat = Chat::create([
            'name' => 'テストグループチャット',
            'type' => Chat::TYPE_GROUP
        ]);
        $this->groupChat->users()->attach([$this->user->id, $this->otherUser->id]);
    }

    public function test_index_displays_user_chats()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('chats.index'));

        $response->assertStatus(200);
        $response->assertViewIs('chats.index');
        $response->assertViewHas('chats');
        $response->assertSee('チャット一覧');
    }

    public function test_show_displays_chat_with_messages()
    {
        // メッセージを作成
        Message::create([
            'user_id' => $this->user->id,
            'chat_id' => $this->dmChat->id,
            'body' => 'テストメッセージ'
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('chats.show', $this->dmChat));

        $response->assertStatus(200);
        $response->assertViewIs('chats.show');
        $response->assertViewHas(['chat', 'messages', 'chatMembers']);
        $response->assertSee('テストメッセージ');
    }

    public function test_show_prevents_unauthorized_access()
    {
        $unauthorizedUser = User::factory()->create();
        
        $this->actingAs($unauthorizedUser);

        $response = $this->get(route('chats.show', $this->dmChat));

        $response->assertStatus(403);
    }

    public function test_create_group_chat_successfully()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('chats.create-group', $this->group));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'グループチャットを作成しました');
        
        $this->assertDatabaseHas('chats', [
            'name' => $this->group->name . 'チャット',
            'type' => Chat::TYPE_GROUP
        ]);
    }

    public function test_create_group_chat_requires_group_membership()
    {
        $nonMember = User::factory()->create();
        
        $this->actingAs($nonMember);

        $response = $this->post(route('chats.create-group', $this->group));

        $response->assertStatus(403);
    }

    public function test_create_dm_successfully()
    {
        // 既存のDMチャットを削除
        $this->dmChat->users()->detach();
        $this->dmChat->delete();
        
        $this->actingAs($this->user);

        $response = $this->post(route('chats.create-dm', $this->otherUser));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('chats', [
            'type' => Chat::TYPE_DM
        ]);
    }

    public function test_create_dm_redirects_to_existing_dm()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('chats.create-dm', $this->otherUser));

        $response->assertRedirect(route('chats.show', $this->dmChat));
        $response->assertSessionHas('info', '既存のDMに移動しました');
    }

    public function test_create_dm_prevents_self_dm()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('chats.create-dm', $this->user));

        $response->assertRedirect();
        $response->assertSessionHas('error', '自分自身とのDMは作成できません');
    }

    public function test_send_message_successfully()
    {
        $this->actingAs($this->user);

        $messageData = ['body' => 'テスト送信メッセージ'];

        $response = $this->post(route('chats.send-message', $this->dmChat), $messageData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'メッセージを送信しました');
        
        $this->assertDatabaseHas('messages', [
            'user_id' => $this->user->id,
            'chat_id' => $this->dmChat->id,
            'body' => 'テスト送信メッセージ'
        ]);
    }

    public function test_send_message_validates_input()
    {
        $this->actingAs($this->user);

        // 空のメッセージ
        $response = $this->post(route('chats.send-message', $this->dmChat), ['body' => '']);
        $response->assertSessionHasErrors('body');

        // 長すぎるメッセージ
        $longMessage = str_repeat('a', 1001);
        $response = $this->post(route('chats.send-message', $this->dmChat), ['body' => $longMessage]);
        $response->assertSessionHasErrors('body');
    }

    public function test_send_message_requires_chat_membership()
    {
        $unauthorizedUser = User::factory()->create();
        
        $this->actingAs($unauthorizedUser);

        $response = $this->post(route('chats.send-message', $this->dmChat), ['body' => 'テスト']);

        $response->assertStatus(403);
    }

    public function test_destroy_chat_successfully()
    {
        $this->actingAs($this->user);

        $response = $this->delete(route('chats.destroy', $this->dmChat));

        $response->assertRedirect(route('chats.index'));
        $response->assertSessionHas('success', 'チャットを削除しました');
        
        $this->assertDatabaseMissing('chats', ['id' => $this->dmChat->id]);
    }

    public function test_destroy_chat_requires_membership()
    {
        $unauthorizedUser = User::factory()->create();
        
        $this->actingAs($unauthorizedUser);

        $response = $this->delete(route('chats.destroy', $this->dmChat));

        $response->assertStatus(403);
    }

    public function test_search_users_returns_json()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('chats.search-users', ['q' => $this->otherUser->name]));

        $response->assertStatus(200);
        $response->assertJson([
            [
                'id' => $this->otherUser->id,
                'name' => $this->otherUser->name
            ]
        ]);
    }

    public function test_search_users_requires_minimum_query_length()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('chats.search-users', ['q' => 'a']));

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    public function test_search_users_excludes_current_user()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('chats.search-users', ['q' => $this->user->name]));

        $response->assertStatus(200);
        $searchResults = $response->json();
        
        $userIds = array_column($searchResults, 'id');
        $this->assertNotContains($this->user->id, $userIds);
    }

    public function test_chat_updates_timestamp_when_message_sent()
    {
        $originalTimestamp = $this->dmChat->updated_at;
        
        // 1秒待機して確実にタイムスタンプが変わるようにする
        sleep(1);
        
        $this->actingAs($this->user);

        $this->post(route('chats.send-message', $this->dmChat), ['body' => 'テスト']);

        $this->dmChat->refresh();
        $this->assertTrue($this->dmChat->updated_at->gt($originalTimestamp));
    }

    public function test_messages_are_paginated()
    {
        // 60個のメッセージを作成（ページサイズが50の場合）
        for ($i = 1; $i <= 60; $i++) {
            Message::create([
                'user_id' => $this->user->id,
                'chat_id' => $this->dmChat->id,
                'body' => "メッセージ {$i}"
            ]);
        }

        $this->actingAs($this->user);

        $response = $this->get(route('chats.show', $this->dmChat));

        $response->assertStatus(200);
        $messages = $response->viewData('messages');
        $this->assertLessThanOrEqual(50, $messages->count());
        $this->assertTrue($messages->hasPages());
    }

    public function test_guest_cannot_access_chats()
    {
        $response = $this->get(route('chats.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('chats.show', $this->dmChat));
        $response->assertRedirect(route('login'));

        $response = $this->post(route('chats.send-message', $this->dmChat), ['body' => 'テスト']);
        $response->assertRedirect(route('login'));
    }
}