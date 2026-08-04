<?php

namespace Tests\Feature\Documents;

use App\Models\Document;
use App\Models\Item;
use App\Models\User;
use App\Models\Team;
use App\Models\TeamUser;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DocumentOwnerChangeTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1, $user2;
    protected Team $team;
    protected Category $cat1, $cat2, $catt;

    protected function setUp(): void
    {
        parent::setUp();

        //users
        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();
        
        //team
        $this->team = Team::factory()->create();
        
        //memberships
        TeamUser::factory()->create(['team_id' => $this->team->id, 'user_id' => $this->user1->id, 'reveal_private' => 1]);
        TeamUser::factory()->create(['team_id' => $this->team->id, 'user_id' => $this->user2->id, 'reveal_private' => 1]);
        
        //categories
        $this->cat1  = Category::factory()->create(['user_id' => $this->user1->id,]);
        $this->cat2  = Category::factory()->create(['user_id' => $this->user2->id,]);
        $this->catt  = Category::factory()->create(['user_id' => $this->user2->id, 'team_id' => $this->team->id]);
    }

    #[test]
    public function user_can_create_own_document()
    {
        $payload = [
            'title' => 'August Posting',
            'posting_date' => now()->addDays(-3)->toDateString(),
            'user_id' => $this->user1->id,
        ];

        $response = $this->actingAs($this->user1)
            ->post('/documents', $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('documents', [
            'title' => 'August Posting',
            'posting_date' => now()->addDays(-3)->toDateString(),
            'user_id' => $this->user1->id,
        ]);
    }

    #[test]
    public function user_can_create_alien_document()
    {
        $payload = [
            'title' => 'August Posting',
            'posting_date' => now()->addDays(-3)->toDateString(),
            'user_id' => $this->user2->id,
        ];

        $response = $this->actingAs($this->user1)
            ->post('/documents', $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('documents', [
            'title' => 'August Posting',
            'posting_date' => now()->addDays(-3)->toDateString(),
            'user_id' => $this->user2->id,
        ]);
    }

    #[test]
    public function user_can_change_document_owner()
    {
        $document = Document::factory()->for($this->user1)->create([
            'title' => 'Old title',
            'posting_date' => now()->toDateString(),
            'user_id' => $this->user1->id,
            'repeat_pattern' => null,
            'repeat_constant' => false,
        ]);

        $payload = [
            'title' => 'Updated title',
            'posting_date' => now()->addDays(-5)->toDateString(),
            'user_id' => $this->user2->id,
            'repeat_pattern' => 2,
            'repeat_constant' => true,
        ];

        $response = $this->actingAs($this->user1)
            ->put("/documents/{$document->id}", $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'title' => 'Updated title',
            'posting_date' => now()->addDays(-5)->toDateString(),
            'user_id' => $this->user2->id,
            'repeat_pattern' => 2,
            'repeat_constant' => true,
        ]);
    }


    #[test]
    public function owner_change_removes_invalid_items()
    {
        // Arrange: create a document owned by user1
        $document = Document::factory()->create(['user_id' => $this->user1,]);
        $item1 = Item::factory()->create(['document_id' => $document->id, 'category_id' => $this->catt->id,]); // team category
        $item2 = Item::factory()->create(['document_id' => $document->id, 'category_id' => $this->cat1->id,]); // private category

        // Update: change document owner

        $payload = [
            'title' => 'Updated title',
            'posting_date' => now()->addDays(-5)->toDateString(),
            'user_id' => $this->user2->id,
            'repeat_pattern' => 2,
            'repeat_constant' => true,
        ];

        $response = $this->actingAs($this->user1)
            ->put("/documents/{$document->id}", $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'user_id' => $this->user2->id,
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $item1->id,
        ]);

        $this->assertDatabaseMissing('items', [
            'id' => $item2->id,
        ]);
    }
}
