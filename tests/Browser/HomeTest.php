<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Role;

class HomeTest extends DuskTestCase
{
    use DatabaseMigrations;
    /**
     * A Dusk test example.
     */
    public function testExample(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();
            $group = Group::factory()->create();
            $role = Role::factory()->create();
            GroupMember::factory()
                ->withUser($user)
                ->withGroup($group)
                ->withRole($role)
                ->create();

            $browser->loginAs($user)
                    ->visit('/home')
                    ->assertSee('Home')
                    ->assertSee($group->name)
                    ->clickLink($group->name)
                    ->assertRouteIs('groups.show', ['group' => $group->id]);
        });
    }
}
