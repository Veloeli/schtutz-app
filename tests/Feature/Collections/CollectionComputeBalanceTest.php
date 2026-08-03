<?php

namespace Tests\Feature\Collections;

use App\Models\Collection;
use App\Models\Document;
use App\Models\Item;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CollectionComputeBalanceTest extends TestCase
{
    use RefreshDatabase;

    #[test]
    public function test_collections_can_assign_items_and_compute_balance_correctly()
    {
        // --- USERS ---
        $user = User::factory()->create();
        $this->actingAs($user);

        // --- COLLECTION ---
        $collection = Collection::factory()->create([
            'user_id' => $user->id,
        ]);

        // --- CATEGORY ---
        $cat = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        // --- DOCUMENTS + ITEMS ---
        // Create 333 documents, each with 3 items
        $documents = Document::factory()
            ->count(33)
            ->for($user)
            ->create()
            ->each(function ($doc) use ($cat) {
                Item::factory()->count(3)->create([
                    'document_id' => $doc->id,
                    'category_id' => $cat->id,
                    'amount'      => 100,   // deterministic
                ]);
            });

        $this->assertDatabaseCount('items', 99);

        // Flatten items
        $items = $documents->flatMap->items;

        // --- PAGINATION SIMULATION ---
        // Your UI loads: /collections/{id}/edit?page=1
        $response = $this->get("/collections/{$collection->id}/edit?page=3");
        $response->assertStatus(200);

        // --- SELECT SOME ITEMS ---
        $selectedItems = $items->take(4); // choose 4 items
        $changedSignItems = $selectedItems->take(1); // invert 1 of them

        // --- FORM PAYLOAD ---
        $payload = [
            'name' => 'Test Collection',
            'date_from' => now()->toDateString(),
            'date_to' => null,
            'team_id' => null,

            'items' => $selectedItems->pluck('id')->toArray(),
            'change_sign' => $changedSignItems->mapWithKeys(fn($item) => [
                $item->id => 1
            ])->toArray(),
            'original_items' => [],
        ];

        // --- SUBMIT ---
        $update = $this->put("/collections/{$collection->id}", $payload);

        // Assert redirect to index
        $update->assertRedirect("/collections?collection_id={$collection->id}");

        // --- LOAD INDEX SCREEN ---
        $index = $this->get("/collections?collection_id={$collection->id}");
        $index->assertStatus(200);

        // --- PARSE BALANCE FROM HTML ---
        $html = $index->getContent();

        // Match the input field containing the balance
        preg_match('/<input[^>]*id="balance"[^>]*value="([^"]+)"/i', $html, $matches);

        $this->assertNotEmpty($matches, 'Balance input field not found in index HTML');

        $displayedBalance = floatval($matches[1]);

        // --- EXPECTED BALANCE ---
        $expectedBalance =
            $selectedItems->sum('amount') -
            2 * $changedSignItems->sum('amount');

        // --- ASSERT ---
        $this->assertEquals($expectedBalance, $displayedBalance);

        // Assert DB amounts
        foreach ($selectedItems as $item) {
            $this->assertDatabaseHas('items', [
                'id'     => $item->id,
                'amount' => 100,
            ]);
        }

        // Assert balance in DB
        $computedBalance = $collection->items->sum(function ($item) {
            return $item->pivot->change_sign
                ? -$item->amount
                :  $item->amount;
        });

        $this->assertEquals($expectedBalance, $computedBalance);
    }
}
