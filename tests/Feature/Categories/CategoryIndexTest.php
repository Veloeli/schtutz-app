<?php

namespace Tests\Feature\Categories;

use Tests\TestCase;
use App\Models\User;
use App\Models\Team;
use App\Models\TeamUser;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class CategoryIndexTest extends TestCase
{
    use RefreshDatabase;

    #[test]
    public function it_shows_correct_edit_buttons_based_on_ownership_and_team_membership()
    {
        // Create team and users
        $team = Team::factory()->create();

        $self  = User::factory()->create();   // owner of Salad + private
        $other = User::factory()->create();   // owner of Onion + private

        // Add both users to the same team
        TeamUser::factory()->create(['team_id' => $team->id, 'user_id' => $self->id,]);
        TeamUser::factory()->create(['team_id' => $team->id, 'user_id' => $other->id, 'reveal_private' => 1,]);

        // Team categories
        $salad = Category::factory()
            ->forUser($self)
            ->forTeam($team)
            ->create(['name' => 'Salad']);

        $onion = Category::factory()
            ->forUser($other)
            ->forTeam($team)
            ->create(['name' => 'Onion']);

        // Private categories
        $privateSelf = Category::factory()
            ->forUser($self)
            ->create(['name' => 'Wallet Self']);

        $privateOther = Category::factory()
            ->forUser($other)
            ->create(['name' => 'Wallet Other']);

        // Act as self
        $response = $this->actingAs($self)->get('/categories');

        // own private and team - should have edit button
        $response->assertSee("edit-category-{$salad->id}");
        $response->assertSee("edit-category-{$onion->id}");
        $response->assertSee("edit-category-{$privateSelf->id}");

        // Private category - other and revealed - SHOULD NOT have edit button
        $response->assertDontSee("edit-category-{$privateOther->id}");
        $response->assertSee("user-category-{$privateOther->id}");

        // other private (revealed) and team - should have badge
        $response->assertSee("badge-{$salad->id}");  // team badge
        $response->assertSee("badge-{$onion->id}");  // team badge
        $response->assertSee("badge-{$privateOther->id}"); // user badge

        // own private should not have a badge
        $response->assertDontSee("badge-{$privateSelf->id}");

        // filter team
        $response = $this->get('/categories?teamfilter=team-' . $team->id);
        $response->assertSee("Salad");
        $response->assertSee("Onion");
        $response->assertDontSee("Wallet Self");
        $response->assertDontSee("Wallet Other");

        // filter member self
        $response = $this->get('/categories?teamfilter=member-' . $self->id);
        $response->assertSee("Salad");
        $response->assertDontSee("Onion");
        $response->assertSee("Wallet Self");
        $response->assertDontSee("Wallet Other");

        // filter member other (revealed)
        $response = $this->get('/categories?teamfilter=member-' . $other->id);
        $response->assertDontSee("Salad");
        $response->assertSee("Onion");
        $response->assertDontSee("Wallet Self");
        $response->assertSee("Wallet Other");
    }
}
