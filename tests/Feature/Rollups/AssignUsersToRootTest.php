<?php

namespace Tests\Feature\Rollups;

use Tests\TestCase;
use App\Models\User;
use App\Models\Rollup;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssignUsersToRootTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigns_users_to_root_rollup()
    {
        $user = User::factory()->create();
        $root = Rollup::factory()->forUser($user)->create();

        $this->actingAs($user);

        // owner should be created in pivot table
        $this->assertDatabaseHas('rollup_user', [
            'rollup_id' => $root->id,
            'user_id' => $user->id,
        ]);

        // share the rollup with 2 more users
        $users = User::factory()->count(2)->create();

        foreach ($users as $user) {
            $response = $this->post("/rollups/{$root->id}/users", [
                'rollup_id' => $root->id,
                'email'     => $user->email,
            ]);

            $response->assertRedirect();
        }

        foreach ($users as $user) {
            $this->assertDatabaseHas('rollup_user', [
                'rollup_id' => $root->id,
                'user_id' => $user->id,
            ]);
        }

    }
}
