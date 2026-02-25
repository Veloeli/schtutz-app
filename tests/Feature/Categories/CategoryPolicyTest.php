<?php

namespace Tests\Feature\Categories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CategoryPolicyTest extends TestCase
{
    use RefreshDatabase;

    #[test]
    public function user_cannot_edit_someone_elses_private_category()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $category = Category::factory()->forUser($owner)->create();

        $this->actingAs($other);

        $response = $this->get("/categories/{$category->id}/edit");

        $response->assertNotFound();
    }
}
