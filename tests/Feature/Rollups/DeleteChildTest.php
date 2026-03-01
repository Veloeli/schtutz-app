<?php

namespace Tests\Feature\Rollups;

use Tests\TestCase;
use App\Models\User;
use App\Models\Rollup;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteChildTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_child_rollup()
    {
        $user = User::factory()->create();
        $root = Rollup::factory()->forUser($user)->create();

        $this->actingAs($user);

        $child = Rollup::factory()->create([
            'parent_id' => $root->id
        ]);

        $this->assertDatabaseHas('rollups', [
            'id' => $child->id,
        ]);

        $response = $this->delete("/rollups/{$child->id}");
        $response->assertRedirect('rollups');

        $this->assertDatabaseMissing('rollups', [
            'id' => $child->id,
        ]);
    }
}
