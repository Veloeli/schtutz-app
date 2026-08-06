<?php

namespace Tests\Feature\Documents;

use Tests\TestCase;
use App\Models\User;
use App\Models\Document;
use App\Models\Security;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class DocumentCurrencyNameTest extends TestCase
{
    use RefreshDatabase;

    #[test]
    public function it_resolves_the_currency_name_from_security()
    {
        $user = User::factory()->create();

        $eur = Security::factory()->create([
            'name'        => 'EUR',
            'asset_class' => 'FX',
            'is_in_use'   => 1,
            'user_id'     => $user->id,
        ]);

        $document = Document::factory()->create([
            'user_id'     => $user->id,
            'currency_id' => $eur->id,
        ]);

        $this->assertEquals('EUR', $document->currency_name);
    }

    #[test]
    public function it_returns_null_when_document_has_no_currency()
    {
        $user = User::factory()->create();

        $document = Document::factory()->create([
            'user_id'     => $user->id,
            'currency_id' => null,
        ]);

        $this->assertNull($document->currency_name);
    }
}
