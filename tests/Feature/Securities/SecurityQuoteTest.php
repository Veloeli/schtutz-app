<?php

namespace Tests\Feature\Securities;

use App\Models\Security;
use App\Models\Quote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SecurityQuoteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_latest_quote_before_or_on_date()
    {
        $security = Security::factory()->create();

        Quote::factory()->create([
            'security_id' => $security->id,
            'quote_date' => '2024-01-01',
            'price' => 100,
        ]);

        Quote::factory()->create([
            'security_id' => $security->id,
            'quote_date' => '2024-01-10',
            'price' => 120,
        ]);

        Quote::factory()->create([
            'security_id' => $security->id,
            'quote_date' => '2024-01-20',
            'price' => 130,
        ]);

        $result = $security->quoteAt(Carbon::parse('2024-01-15'));

        $this->assertEquals(120, $result);
    }

    #[Test]
    public function it_throws_if_no_quote_available()
    {
        $security = Security::factory()->create();

        $this->expectExceptionMessage('No quote available');

        $security->quoteAt(Carbon::parse('2024-01-01'));
    }

    #[Test]
    public function it_multiplies_with_currency_quote()
    {
        $fund = Security::factory()->create();
        $eur  = Security::factory()->create();

        $fund->update(['currency_id' => $eur->id]);

        Quote::factory()->create([
            'security_id' => $fund->id,
            'quote_date' => '2024-01-01',
            'price' => 100,
        ]);

        Quote::factory()->create([
            'security_id' => $eur->id,
            'quote_date' => '2024-01-01',
            'price' => 1.10,
        ]);

        $result = $fund->quoteAt(Carbon::parse('2024-01-02'));

        $this->assertEqualsWithDelta(110.0, $result, 0.000001);

    }

    #[Test]
    public function it_resolves_multi_level_currency_recursion()
    {
        $fund = Security::factory()->create();
        $eur  = Security::factory()->create();
        $usd  = Security::factory()->create();

        $fund->update(['currency_id' => $eur->id]);
        $eur->update(['currency_id' => $usd->id]);

        Quote::factory()->create([
            'security_id' => $fund->id,
            'quote_date' => '2024-01-01',
            'price' => 100,
        ]);

        Quote::factory()->create([
            'security_id' => $eur->id,
            'quote_date' => '2024-01-01',
            'price' => 1.10,
        ]);

        Quote::factory()->create([
            'security_id' => $usd->id,
            'quote_date' => '2024-01-01',
            'price' => 1.00,
        ]);

        $result = $fund->quoteAt(Carbon::parse('2024-01-02'));

        $this->assertEqualsWithDelta(110.0, $result, 0.000001);

    }

    #[Test]
    public function it_detects_cycles_and_throws()
    {
        $a = Security::factory()->create();
        $b = Security::factory()->create();

        $a->update(['currency_id' => $b->id]);
        $b->update(['currency_id' => $a->id]);

        Quote::factory()->create([
            'security_id' => $a->id,
            'quote_date' => '2024-01-01',
            'price' => 100,
        ]);

        Quote::factory()->create([
            'security_id' => $b->id,
            'quote_date' => '2024-01-01',
            'price' => 1.10,
        ]);

        $this->expectExceptionMessage('Currency recursion detected');

        $a->quoteAt(Carbon::parse('2024-01-02'));
    }

    #[Test]
    public function it_uses_cache_to_avoid_recomputing_currency_chain()
    {
        $fund = Security::factory()->create();
        $eur  = Security::factory()->create();

        $fund->update(['currency_id' => $eur->id]);

        Quote::factory()->create([
            'security_id' => $fund->id,
            'quote_date' => '2024-01-01',
            'price' => 100,
        ]);

        Quote::factory()->create([
            'security_id' => $eur->id,
            'quote_date' => '2024-01-01',
            'price' => 1.10,
        ]);

        // First call warms cache
        $first = $fund->quoteAt(Carbon::parse('2024-01-02'));

        // Second call should use cached EUR quote
        $second = $fund->quoteAt(Carbon::parse('2024-01-02'));

        $this->assertEquals($first, $second);
    }
}
