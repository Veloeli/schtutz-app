<?php

namespace Tests\Feature\Categories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CategoryUpdateTest extends TestCase
{
    use RefreshDatabase;

    #[test]
    public function user_can_update_own_category()
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();

        $this->actingAs($user);

        $response = $this->put("/categories/{$category->id}", [
            'name' => 'Updated Name',
            'code' => 'UPD',
            'type' => 'IN',
            'is_selectable' => false,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
            'code' => 'UPD',
            'type' => 'IN',
            'is_selectable' => false,
        ]);
    }
}
