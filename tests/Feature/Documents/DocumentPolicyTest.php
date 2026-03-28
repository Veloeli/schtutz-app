<?php

namespace Tests\Feature\Documents;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DocumentPolicyTest extends TestCase
{
    use RefreshDatabase;

    #[test]
    public function user_cannot_create_document_older_than_freeze_after_months()
    {
        $user = User::factory()->create([
            'freeze_after' => 3, // months
        ]);

        // Posting date older than 3 months → should be blocked
        $payload = [
            'title' => 'Old Posting',
            'posting_date' => now()->subMonths(6)->toDateString(),
        ];

        $response = $this->actingAs($user)
            ->post('/documents', $payload);

        // Controller returns redirect with errors
        $response->assertRedirect();
        $response->assertSessionHasErrors('posting_date');

        // Ensure nothing was created
        $this->assertDatabaseMissing('documents', [
            'title' => 'Old Posting',
        ]);
    }

    #[test]
    public function user_can_create_document_newer_than_freeze_after_months()
    {
        $user = User::factory()->create([
            'freeze_after' => 3,
        ]);

        $payload = [
            'title' => 'Recent Posting',
            'posting_date' => now()->subMonths(1)->toDateString(),
        ];

        $response = $this->actingAs($user)
            ->post('/documents', $payload);

        // Controller redirects to index or show page
        $response->assertRedirect();

        // Document is created
        $this->assertDatabaseHas('documents', [
            'title' => 'Recent Posting',
            'posting_date' => now()->subMonths(1)->toDateString(),
            'user_id' => $user->id,
        ]);
    }

    #[test]
    public function user_cannot_update_document_older_than_freeze_after_months()
    {
        $user = User::factory()->create([
            'freeze_after' => 3,
        ]);

        $document = Document::factory()
            ->for($user)
            ->create([
                'title' => 'Old Doc',
                'posting_date' => now()->subMonths(6)->toDateString(),
            ]);

        $payload = [
            'title' => 'Updated Title',
            'posting_date' => now()->subMonths(6)->toDateString(),
            'repeat_pattern' => 0,
            'repeat_constant' => false,
        ];

        $response = $this->actingAs($user)
            ->put("/documents/{$document->id}", $payload);

        $response->assertRedirect();
        $response->assertSessionHasErrors('posting_date');

        // Ensure the document was NOT updated
        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'title' => 'Old Doc', // unchanged
            'posting_date' => now()->subMonths(6)->toDateString(),
        ]);
    }

    #[test]
    public function user_can_update_document_newer_than_freeze_after_months()
    {
        $user = User::factory()->create([
            'freeze_after' => 3,
        ]);

        $document = Document::factory()
            ->for($user)
            ->create([
                'title' => 'Old Doc',
                'posting_date' => now()->subMonths(1)->toDateString(),
            ]);

        $payload = [
            'title' => 'Updated Title',
            'posting_date' => now()->subMonths(1)->toDateString(),
            'repeat_pattern' => 0,
            'repeat_constant' => false,
        ];

        $response = $this->actingAs($user)
            ->put("/documents/{$document->id}", $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'title' => 'Updated Title',
        ]);
    }
}
