<?php

namespace Tests\Feature\Documents;

use Tests\TestCase;
use App\Models\User;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class DocumentIndexTest extends TestCase
{
    use RefreshDatabase;

    #[test]
    public function it_falls_back_to_current_month_if_selected_month_no_longer_exists()
    {
        // Freeze time so "now()" is stable
        Carbon::setTestNow('2026-08-07');

        $user = User::factory()->create();
        $this->actingAs($user);

        // Create documents only for current month (Aug 2026)
        $doc = Document::factory()->create([
            'posting_date' => '2026-08-15',
            'user_id' => $user->id,
        ]);

        // Simulate user previously selecting a future month (e.g. 2027-01)
        session(['document_month' => '2027-01']);

        // Now user deleted the future document → month no longer exists
        // Controller should fallback to now()->format('Y-m') = 2026-08

        $response = $this->get('/documents');

        $response->assertStatus(200);

        // The controller should have corrected the session
        $this->assertEquals('2026-08', session('document_month'));

        // The view should receive the corrected month
        $response->assertViewHas('month', '2026-08');

        // And the visible documents should be the ones from August 2026
        $response->assertViewHas('documents', function ($docs) use ($doc) {
            return $docs->contains($doc);
        });

        // Range should reflect actual min/max posting_date
        $response->assertViewHas('minMonth', Carbon::parse('2026-08-01'));
        $response->assertViewHas('maxMonth', Carbon::parse('2026-08-01'));
    }


    #[test]
    public function it_keeps_selected_month_if_it_is_valid()
    {
        Carbon::setTestNow('2026-08-07');

        $user = User::factory()->create();
        $this->actingAs($user);

        $doc = Document::factory()->create([
            'posting_date' => '2026-06-10',
            'user_id' => $user->id,
        ]);

        session(['document_month' => '2026-06']);

        $response = $this->get('/documents');

        $response->assertViewHas('month', '2026-06');
        $response->assertViewHas('documents', function ($docs) use ($doc) {
            return $docs->contains($doc);
        });
    }
}
