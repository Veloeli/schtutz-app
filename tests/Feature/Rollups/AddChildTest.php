<?php

namespace Tests\Feature\Rollups;

use Tests\TestCase;
use App\Models\User;
use App\Models\Rollup;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddChildTest extends TestCase
{
    use RefreshDatabase;

    public function test_adds_child_to_existing_rollup()
    {
        $user = User::factory()->create();
        $root = Rollup::factory()->forUser($user)->create();
        $this->actingAs($user);

        $response = $this->post("/rollups/{$root->id}/children", [
            'name' => 'New Child',
        ]);

        $response->assertRedirect(route('rollups.index'));

        $followed = $this->followRedirects($response);

        $followed->assertSessionHas('root_id', $root->id);

        $this->assertDatabaseHas('rollups', [
            'name' => 'New Child',
            'parent_id' => $root->id,
        ]);
    }
}
