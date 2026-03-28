<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Document;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ItemCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Document $document;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->document = Document::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->category = Category::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    #[test]
    public function it_creates_an_item()
    {
        $payload = [
            'name'        => 'Laptop',
            'amount'      => 1299.50,
            'quantity'    => 2,
            'category_id' => $this->category->id,
        ];

        $response = $this->post("/documents/{$this->document->id}/items", $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('items', array_merge($payload, [
            'document_id' => $this->document->id,
        ]));
    }

    #[test]
    public function it_lists_items()
    {
        // Create items for this document
        $items = Item::factory()->count(3)->create([
            'document_id' => $this->document->id,
            'category_id' => $this->category->id,
        ]);

        // Hit the documents index page
        $response = $this->get('/documents');

        $response->assertStatus(200);

        // Assert that at least one item name is visible
        $response->assertSee($items->first()->name);
    }

    #[test]
    public function it_updates_an_item()
    {
        $item = Item::factory()->create([
            'document_id' => $this->document->id,
            'category_id' => $this->category->id,
        ]);

        $payload = [
            'name'        => 'Updated Name',
            'amount'      => 999.99,
            'quantity'    => 5,
            'category_id' => $this->category->id,
        ];

        $response = $this->put("/documents/{$this->document->id}/items/{$item->id}", $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('items', array_merge($payload, [
            'id'          => $item->id,
            'document_id' => $this->document->id,
        ]));
    }

    #[test]
    public function it_deletes_an_item()
    {
        $item = Item::factory()->create([
            'document_id' => $this->document->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->delete("/documents/{$this->document->id}/items/{$item->id}");

        $response->assertStatus(302);

        $this->assertDatabaseMissing('items', [
            'id' => $item->id,
        ]);
    }

    #[test]
    public function it_validates_required_fields()
    {
        $response = $this->post("/documents/{$this->document->id}/items", []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'category_id',
        ]);
    }

    #[test]
    public function it_rejects_invalid_category_id()
    {
        $payload = [
            'name'        => 'Invalid',
            'amount'      => 10,
            'quantity'    => 1,
            'category_id' => 999999,
        ];

        $response = $this->post("/documents/{$this->document->id}/items", $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['category_id']);
    }
}
