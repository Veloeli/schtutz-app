<?php

namespace Tests\Feature\Rollups;

use Tests\TestCase;
use App\Models\User;
use App\Models\Rollup;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_root_and_child()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create root
        $response = $this->post('/rollups', [
            'name' => 'Root A',
        ]);

        $root = Rollup::first();
        $rootId = $root->id;

        // Create child
        $childResponse = $this->post("/rollups/{$rootId}/children", [
            'name' => 'Child A1',
        ]);

        $response->assertRedirect(route('rollups.index'));
        $childResponse->assertRedirect(route('rollups.index'));

        $followed = $this->followRedirects($childResponse);

        $followed->assertSessionHas('root_id', $rootId);

        $this->assertDatabaseHas('rollups', [
            'name' => 'Child A1',
            'parent_id' => $rootId,
        ]);
    }

}
