<?php

namespace Tests\Unit;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_has_fillable_attributes()
    {
        $fillable = ['name', 'type'];
        $chat = new Chat();
        
        $this->assertEquals($fillable, $chat->getFillable());
    }

    public function test_chat_has_type_constants()
    {
        $this->assertEquals('dm', Chat::TYPE_DM);
        $this->assertEquals('group', Chat::TYPE_GROUP);
    }

    public function test_chat_has_many_messages()
    {
        $chat = Chat::create([
            'name' => 'テストチャット',
            'type' => Chat::TYPE_GROUP,
        ]);

        $user = User::factory()->create();
        Message::create([
            'user_id' => $user->id,
            'chat_id' => $chat->id,
            'body' => 'テストメッセージ',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $chat->messages);
        $this->assertCount(1, $chat->messages);
        $this->assertEquals('テストメッセージ', $chat->messages->first()->body);
    }

    public function test_chat_belongs_to_many_users()
    {
        $chat = Chat::create([
            'name' => 'テストチャット',
            'type' => Chat::TYPE_GROUP,
        ]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $chat->users()->attach([$user1->id, $user2->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $chat->users);
        $this->assertCount(2, $chat->users);
        $this->assertTrue($chat->users->contains($user1));
        $this->assertTrue($chat->users->contains($user2));
    }

    public function test_chat_can_be_created_with_dm_type()
    {
        $chat = Chat::create([
            'name' => 'DM',
            'type' => Chat::TYPE_DM,
        ]);

        $this->assertDatabaseHas('chats', [
            'name' => 'DM',
            'type' => 'dm',
        ]);
    }

    public function test_chat_can_be_created_with_group_type()
    {
        $chat = Chat::create([
            'name' => 'グループチャット',
            'type' => Chat::TYPE_GROUP,
        ]);

        $this->assertDatabaseHas('chats', [
            'name' => 'グループチャット',
            'type' => 'group',
        ]);
    }

    public function test_chat_name_is_required()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Chat::create([
            'type' => Chat::TYPE_GROUP,
        ]);
    }

    public function test_chat_type_validation()
    {
        // Test that both valid enum values work
        $dmChat = Chat::create([
            'name' => 'DMチャット',
            'type' => Chat::TYPE_DM,
        ]);
        $this->assertEquals(Chat::TYPE_DM, $dmChat->type);

        $groupChat = Chat::create([
            'name' => 'グループチャット', 
            'type' => Chat::TYPE_GROUP,
        ]);
        $this->assertEquals(Chat::TYPE_GROUP, $groupChat->type);
    }

    public function test_type_constants_are_valid_strings()
    {
        $this->assertIsString(Chat::TYPE_DM);
        $this->assertIsString(Chat::TYPE_GROUP);
        $this->assertNotEquals(Chat::TYPE_DM, Chat::TYPE_GROUP);
    }

    public function test_chat_relationships_work_correctly()
    {
        $chat = Chat::create([
            'name' => 'テストチャット',
            'type' => Chat::TYPE_GROUP,
        ]);

        $user = User::factory()->create();
        $chat->users()->attach($user->id);

        $message = Message::create([
            'user_id' => $user->id,
            'chat_id' => $chat->id,
            'body' => 'テストメッセージ',
        ]);

        // Verify relationships
        $this->assertEquals($chat->id, $message->chat->id);
        $this->assertEquals($user->id, $message->user->id);
        $this->assertTrue($chat->users->contains($user));
        $this->assertTrue($chat->messages->contains($message));
    }
}