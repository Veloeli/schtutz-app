<?php

namespace Tests\Feature\Categories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CategoryCreationTest extends TestCase
{
    use RefreshDatabase;

    #[test]
    public function user_can_create_private_category()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/categories', [
            'name' => 'Groceries',
            'code' => 'GR',
            'type' => 'EX',
            'is_selectable' => true,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'name' => 'Groceries',
            'code' => 'GR',
            'type' => 'EX',
            'user_id' => $user->id,
            'team_id' => null,
            'is_selectable' => true,
        ]);
    }
}
