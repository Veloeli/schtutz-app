<?php

namespace Tests\Feature\Documents;

use Tests\TestCase;
use App\Models\User;
use App\Models\Document;
use App\Models\Security;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ItemEditCurrencyTest extends TestCase
{
    use RefreshDatabase;

    #[test]
    public function edit_view_shows_converted_amount_when_document_has_currency()
    {
        $user = User::factory()->create();

        // Create EUR currency
        $eur = Security::factory()->create([
            'name'        => 'EUR',
            'asset_class' => 'FX',
            'is_in_use'   => 1,
            'user_id'     => $user->id,
        ]);

        // Document with EUR and rate 0.95
        $document = Document::factory()->create([
            'user_id'        => $user->id,
            'currency_id'    => $eur->id,
            'currency_rate'  => 0.95,
        ]);

        // Item stored in base currency (e.g. CHF)
        // Base amount = 95 → EUR amount = 100
        $item = Item::factory()->create([
            'document_id' => $document->id,
            'amount'      => 95.00,
        ]);

        $response = $this->actingAs($user)->get(
            "/documents/{$document->id}/items/{$item->id}/edit"
        );

        $response->assertStatus(200);

        // The Blade receives $item->amount already converted
        // So the HTML must contain "100"
        $response->assertSee('value="100', false);
    }

    #[test]
    public function edit_view_shows_raw_amount_when_document_has_no_currency()
    {
        $user = User::factory()->create();

        $document = Document::factory()->create([
            'user_id'        => $user->id,
            'currency_id'    => null,
            'currency_rate'  => 1.0,
        ]);

        $item = Item::factory()->create([
            'document_id' => $document->id,
            'amount'      => 123.45,
        ]);

        $response = $this->actingAs($user)->get(
            "/documents/{$document->id}/items/{$item->id}/edit"
        );

        $response->assertStatus(200);

        $response->assertSee('value="123.45', false);
    }
}
