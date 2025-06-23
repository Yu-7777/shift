<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
// use PHPUnit\Framework\TestCase; Laravelの機能を使用する際これは使用しない
use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupMember;

class UserTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $user = User::factory()->create();
        $groups = Group::factory()->create();
        $userGroups = GroupMember::factory()->count(15)->withUser($user)->withGroup($groups)->create();

        $shiftGroups = $user->getShiftGroups();

        $this->assertCount(10, $shiftGroups);

        foreach ($shiftGroups as $group)
        {
            $this->assertNotNull($group->group_members_count);
            $this->assertEquals($group->group_members_count, 15);
        }
    }
}
