<?php

namespace Tests\Unit;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_has_fillable_attributes()
    {
        $fillable = ['user_id', 'chat_id', 'body'];
        $message = new Message();
        
        $this->assertEquals($fillable, $message->getFillable());
    }

    public function test_message_belongs_to_user()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'name' => 'テストチャット',
            'type' => Chat::TYPE_GROUP,
        ]);

        $message = Message::create([
            'user_id' => $user->id,
            'chat_id' => $chat->id,
            'body' => 'テストメッセージ',
        ]);

        $this->assertInstanceOf(User::class, $message->user);
        $this->assertEquals($user->id, $message->user->id);
        $this->assertEquals($user->name, $message->user->name);
    }

    public function test_message_belongs_to_chat()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'name' => 'テストチャット',
            'type' => Chat::TYPE_GROUP,
        ]);

        $message = Message::create([
            'user_id' => $user->id,
            'chat_id' => $chat->id,
            'body' => 'テストメッセージ',
        ]);

        $this->assertInstanceOf(Chat::class, $message->chat);
        $this->assertEquals($chat->id, $message->chat->id);
        $this->assertEquals($chat->name, $message->chat->name);
    }

    public function test_message_can_be_created_with_all_attributes()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'name' => 'テストチャット',
            'type' => Chat::TYPE_GROUP,
        ]);

        $message = Message::create([
            'user_id' => $user->id,
            'chat_id' => $chat->id,
            'body' => 'これはテストメッセージです。',
        ]);

        $this->assertDatabaseHas('messages', [
            'user_id' => $user->id,
            'chat_id' => $chat->id,
            'body' => 'これはテストメッセージです。',
        ]);
    }

    public function test_message_user_id_is_required()
    {
        $chat = Chat::create([
            'name' => 'テストチャット',
            'type' => Chat::TYPE_GROUP,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Message::create([
            'chat_id' => $chat->id,
            'body' => 'テストメッセージ',
        ]);
    }

    public function test_message_chat_id_is_required()
    {
        $user = User::factory()->create();

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Message::create([
            'user_id' => $user->id,
            'body' => 'テストメッセージ',
        ]);
    }

    public function test_message_body_is_required()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'name' => 'テストチャット',
            'type' => Chat::TYPE_GROUP,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Message::create([
            'user_id' => $user->id,
            'chat_id' => $chat->id,
        ]);
    }

    public function test_message_can_have_long_body()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'name' => 'テストチャット',
            'type' => Chat::TYPE_GROUP,
        ]);

        $longBody = str_repeat('これは長いメッセージです。', 100);

        $message = Message::create([
            'user_id' => $user->id,
            'chat_id' => $chat->id,
            'body' => $longBody,
        ]);

        $this->assertEquals($longBody, $message->body);
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'body' => $longBody,
        ]);
    }

    public function test_message_timestamps_are_automatically_set()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'name' => 'テストチャット',
            'type' => Chat::TYPE_GROUP,
        ]);

        $message = Message::create([
            'user_id' => $user->id,
            'chat_id' => $chat->id,
            'body' => 'タイムスタンプテスト',
        ]);

        $this->assertNotNull($message->created_at);
        $this->assertNotNull($message->updated_at);
    }

    public function test_message_foreign_key_constraints()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'name' => 'テストチャット',
            'type' => Chat::TYPE_GROUP,
        ]);

        $message = Message::create([
            'user_id' => $user->id,
            'chat_id' => $chat->id,
            'body' => '外部キーテスト',
        ]);

        // Test that message is properly linked
        $this->assertEquals($user->id, $message->user_id);
        $this->assertEquals($chat->id, $message->chat_id);
        
        // Verify relationships load correctly
        $this->assertEquals($user->name, $message->user->name);
        $this->assertEquals($chat->name, $message->chat->name);
    }
}