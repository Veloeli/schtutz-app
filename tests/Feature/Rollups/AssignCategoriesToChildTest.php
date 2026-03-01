<?php

namespace Tests\Feature\Rollups;

use Tests\TestCase;
use App\Models\Rollup;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssignCategoriesToChildTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigns_categories_to_child_rollup()
    {
        $user = User::factory()->create();
        $root = Rollup::factory()->forUser($user)->create();
        $child = Rollup::factory()->create(['parent_id' => $root->id]);

        $this->actingAs($user);

        $categories = Category::factory()->count(3)->create();

        foreach ($categories as $category) {
            $response = $this->post("/rollups/{$child->id}/categories", [
                'rollup_id'   => $child->id,
                'category_id' => $category->id,
            ]);
            $response->assertRedirect();
        }

        foreach ($categories as $category) {
            $this->assertDatabaseHas('rollup_category', [
                'rollup_id' => $child->id,
                'category_id' => $category->id,
            ]);
        }
    }
}
