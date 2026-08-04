<?php

namespace Tests\Feature\Documents;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DocumentCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'freeze_after' => 3,
        ]);
    }

    #[test]
    public function user_can_create_document_with_minimal_fields()
    {
        $payload = [
            'title' => 'March Posting',
            'posting_date' => now()->addDays(-3)->toDateString(),
            'user_id' => $this->user->id,
        ];

        $response = $this->actingAs($this->user)
            ->post('/documents', $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('documents', [
            'title' => 'March Posting',
            'posting_date' => now()->addDays(-3)->toDateString(),
            'user_id' => $this->user->id,
        ]);
    }

    #[test]
    public function user_can_update_document_with_repeat_fields()
    {
        $document = Document::factory()->for($this->user)->create([
            'title' => 'Old title',
            'posting_date' => now()->toDateString(),
            'user_id' => $this->user->id,
            'repeat_pattern' => null,
            'repeat_constant' => false,
        ]);

        $payload = [
            'title' => 'Updated title',
            'posting_date' => now()->addDays(-5)->toDateString(),
            'user_id' => $this->user->id,
            'repeat_pattern' => 2,
            'repeat_constant' => true,
        ];

        $response = $this->actingAs($this->user)
            ->put("/documents/{$document->id}", $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'title' => 'Updated title',
            'posting_date' => now()->addDays(-5)->toDateString(),
            'repeat_pattern' => 2,
            'repeat_constant' => true,
        ]);
    }

    #[test]
    public function user_can_delete_a_document()
    {
        // Arrange: create a document owned by the user
        $document = Document::factory()
            ->for($this->user)   // uses user() relationship
            ->create([
                'title' => 'To Be Deleted',
                'posting_date' => now()->addDays(-5)->toDateString(),
            ]);

        // Act: perform the delete request
        $response = $this->actingAs($this->user)
            ->delete("/documents/{$document->id}");

        // Assert: controller redirects (typical for web CRUD)
        $response->assertRedirect();

        // Assert: document is gone (hard delete)
        $this->assertDatabaseMissing('documents', [
            'id' => $document->id,
        ]);
    }
}
