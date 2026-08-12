<?php

namespace Tests\Feature\Documents;

use Tests\TestCase;
use App\Models\User;
use App\Models\Document;
use App\Models\Security;
use App\Models\Quote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class DocumentCurrencyTest extends TestCase
{
    use RefreshDatabase;

    #[test]
    public function it_persists_currency_rates_and_handles_currency_removal()
    {
        $user = User::factory()->create();

        // FX currency A
        $currencyA = Security::factory()->create([
            'asset_class' => 'FX',
            'is_in_use'   => 1,
            'user_id'     => $user->id,
        ]);

        // FX currency B
        $currencyB = Security::factory()->create([
            'asset_class' => 'FX',
            'is_in_use'   => 1,
            'user_id'     => $user->id,
        ]);

        $postingDate = Carbon::parse('2026-08-06');

        // Insert deterministic FX quotes
        Quote::create([
            'security_id' => $currencyA->id,
            'quote_date'  => $postingDate->format('Y-m-d'),
            'price'       => 0.95,
        ]);

        Quote::create([
            'security_id' => $currencyB->id,
            'quote_date'  => $postingDate->format('Y-m-d'),
            'price'       => 1.10,
        ]);

        // --- 1. Create document with currency A -----------------------------

        $response = $this->actingAs($user)->post('/documents', [
            'title'        => 'Test Doc',
            'posting_date' => $postingDate->format('Y-m-d'),
            'user_id'      => $user->id,
            'currency_id'  => $currencyA->id,
        ]);

        $response->assertRedirect();

        $document = Document::first();

        $this->assertEquals($currencyA->id, $document->currency_id);
        $this->assertEquals(0.95, $document->currency_rate);


        // --- 2. Change currency to B ----------------------------------------

        $response = $this->actingAs($user)->put("/documents/{$document->id}", [
            'title'           => 'Test Doc',
            'posting_date'    => $postingDate->format('Y-m-d'),
            'user_id'         => $user->id,
            'repeat_pattern'  => 0,
            'repeat_constant' => 0,
            'currency_id'     => $currencyB->id,
        ]);

        $response->assertRedirect();

        $document->refresh();

        $this->assertEquals($currencyB->id, $document->currency_id);
        $this->assertEquals(1.10, $document->currency_rate);


        // --- 3. Remove currency ---------------------------------------------

        $response = $this->actingAs($user)->put("/documents/{$document->id}", [
            'title'           => 'Test Doc',
            'posting_date'    => $postingDate->format('Y-m-d'),
            'user_id'         => $user->id,
            'repeat_pattern'  => 0,
            'repeat_constant' => 0,
            'currency_id'     => null,
        ]);

        $response->assertRedirect();

        $document->refresh();

        $this->assertNull($document->currency_id);
        $this->assertEquals(1.0, $document->currency_rate);
    }
}
