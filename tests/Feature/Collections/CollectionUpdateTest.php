<?php

namespace Tests\Feature\Collection;

use App\Models\Collection;
use App\Models\User;
use App\Models\Team;
use App\Models\Document;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use DateTime;

class CollectionUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Collection $collection;

    protected function setUp(): void
    {
        parent::setUp();

        $dateFrom = new DateTime("first day of last month");
        $dateTo   = new DateTime("last day of last month");

        $this->user = User::factory()->create();
        $this->collection = Collection::factory()->create([
                'user_id' => $this->user->id,
                'team_id' => null,
                'date_from' => $dateFrom->format('Y-m-d'),
                'date_to'   => $dateTo->format('Y-m-d'),
            ]);
    }
    
    #[test]
    public function user_can_change_collection_name()
    {
        $this->actingAs($this->user);

        $payload = [
            'name'       => 'New Name',
            'date_from'  => $this->collection->date_from,
            'date_to'    => $this->collection->date_to,
        ];

        $update = $this->put("/collections/{$this->collection->id}", $payload);

        // Your controller redirects back to edit?page=1
        $update->assertRedirect("/collections?collection_id={$this->collection->id}");

        $this->collection->refresh();

        $this->assertEquals('New Name', $this->collection->name);
    }

    #[test]
    public function user_can_assign_items_to_collection()
    {
        $this->actingAs($this->user);

        $doc = Document::factory()->for($this->user)->create();

        $items = Item::factory()->count(3)->create([
            'document_id' => $doc->id,
            'amount'      => 100,
        ]);

        $payload = [
            'name'       => $this->collection->name,
            'date_from'  => $this->collection->date_from,
            'date_to'    => $this->collection->date_to,

            'items'           => $items->pluck('id')->toArray(),
            'original_items'  => [],
            'change_sign'     => [],
        ];

        $response = $this->put("/collections/{$this->collection->id}", $payload);

        $response->assertRedirect("/collections?collection_id={$this->collection->id}");

        $this->collection->refresh();

        $this->collection->refresh();

        $computedBalance = $this->collection->items->sum(function ($item) {
            return $item->pivot->change_sign
                ? -$item->amount
                :  $item->amount;
        });

        $this->assertEquals(300, $computedBalance);

        foreach ($items as $item) {
            $this->assertDatabaseHas('collection_item', [
                'collection_id' => $this->collection->id,
                'item_id'       => $item->id,
            ]);
        }
    }

    #[test]
    public function assigning_items_across_pages_preserves_previous_selection()
    {
        $this->actingAs($this->user);

        // Create 6 documents so pagination has 2 pages
        $documents = Document::factory()->count(6)->create([
            'user_id' => $this->user->id,
            'posting_date' => $this->collection->date_from->copy()->addDay(),
        ]);

        $itemA = Item::factory()->create([
            'document_id' => $documents[0]->id, // page 1
        ]);

        $itemB = Item::factory()->create([
            'document_id' => $documents[5]->id, // page 2
        ]);

        // --- PAGE 1: select item A ---
        $this->put("/collections/{$this->collection->id}", [
            'name'       => $this->collection->name,
            'date_from'  => $this->collection->date_from,
            'date_to'    => $this->collection->date_to,
            'items'           => [$itemA->id],
            'original_items'  => [], // nothing preselected on page 1
            'change_sign'     => [],
            'goto_page'       => "/collections/{$this->collection->id}/edit?page=2",
        ]);

        // --- PAGE 2: select item B ---
        $this->put("/collections/{$this->collection->id}", [
            'name'       => $this->collection->name,
            'date_from'  => $this->collection->date_from,
            'date_to'    => $this->collection->date_to,
            'items'           => [$itemB->id],
            'original_items'  => [], // nothing preselected on page 2
            'change_sign'     => [],
        ]);

        // Both items must be assigned
        $this->assertDatabaseHas('collection_item', [
            'collection_id' => $this->collection->id,
            'item_id'       => $itemA->id,
        ]);

        $this->assertDatabaseHas('collection_item', [
            'collection_id' => $this->collection->id,
            'item_id'       => $itemB->id,
        ]);
    }

    #[test]
    public function assigning_items_across_pages_handles_deselection_and_sign_changes()
    {
        $this->actingAs($this->user);

        // Create 6 documents so pagination has 2 pages
        $documents = Document::factory()->count(6)->create([
            'user_id'      => $this->user->id,
            'posting_date' => $this->collection->date_from->copy()->addDay(),
        ]);

        // Page 1 items
        $itemA = Item::factory()->create([
            'document_id' => $documents[0]->id,
            'amount'      => 100,
        ]);

        $itemB = Item::factory()->create([
            'document_id' => $documents[0]->id,
            'amount'      => 200,
        ]);

        // Page 2 items
        $itemC = Item::factory()->create([
            'document_id' => $documents[5]->id,
            'amount'      => 300,
        ]);

        //
        // --- PAGE 1: select A and B ---
        //
        $this->put("/collections/{$this->collection->id}", [
            'name'       => $this->collection->name,
            'date_from'  => $this->collection->date_from,
            'date_to'    => $this->collection->date_to,
            'items'           => [$itemA->id, $itemB->id],
            'original_items'  => [],
            'change_sign'     => [],
            'goto_page'       => "/collections/{$this->collection->id}/edit?page=2",
        ]);

        //
        // --- PAGE 2: select C ---
        //
        $this->put("/collections/{$this->collection->id}", [
            'name'       => $this->collection->name,
            'date_from'  => $this->collection->date_from,
            'date_to'    => $this->collection->date_to,
            'items'           => [$itemC->id],
            'original_items'  => [],
            'change_sign'     => [],
            'goto_page'       => "/collections/{$this->collection->id}/edit?page=1",
        ]);

        //
        // --- PAGE 1 (again): deselect B and change sign A ---
        //
        $this->put("/collections/{$this->collection->id}", [
            'name'       => $this->collection->name,
            'date_from'  => $this->collection->date_from,
            'date_to'    => $this->collection->date_to,
            'items'           => [$itemA->id],
            'original_items'  => [$itemA->id, $itemB->id],

            // User checks "change sign" for item A
            'change_sign'     => [
                $itemA->id => 1,
            ],
        ]);

        //
        // --- EXPECTATIONS ---
        //

        // Item A: selected on page 1, sign changed
        $this->assertDatabaseHas('collection_item', [
            'collection_id' => $this->collection->id,
            'item_id'       => $itemA->id,
            'change_sign'   => 1,
        ]);

        // Item B: selected on page 1, but later deselected on page 1
        $this->assertDatabaseMissing('collection_item', [
            'collection_id' => $this->collection->id,
            'item_id'       => $itemB->id,
        ]);

        // Item C: selected on page 2
        $this->assertDatabaseHas('collection_item', [
            'collection_id' => $this->collection->id,
            'item_id'       => $itemC->id,
            'change_sign'   => 0,
        ]);
    }
}
