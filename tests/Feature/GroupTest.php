<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use App\Models\Role;
use App\Models\GroupMember;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GroupTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_lack_of_authority(): void
    {
        $users = User::factory()->count(2)->create();
        $group = Group::factory()->create();
        $role = Role::factory()->create();
        GroupMember::factory()
            ->withUser($users[0])
            ->withGroup($group)
            ->withRole($role)
            ->create();

        $this->actingAs($users[1])
            ->get('/groups/' . $group->id)
            ->assertStatus(403);
    }

    public function test_correct_display(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $role = Role::factory()->create();
        GroupMember::factory()
            ->withUser($user)
            ->withGroup($group)
            ->withRole($role)
            ->create();

        $this->actingAs($user)
            ->get('/groups/' . $group->id)
            ->assertStatus(200)
            ->assertSee($group->name)
            ->assertViewHasAll([
                'group',
                'shifts',
                'members',
            ]);
    }
}
