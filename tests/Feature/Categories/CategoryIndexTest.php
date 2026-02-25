<?php

namespace Tests\Feature\Categories;

use Tests\TestCase;
use App\Models\User;
use App\Models\Team;
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
        $team->members()->attach([$self->id, $other->id]);

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

        // Salad → SHOULD have an edit button
        $response->assertSee("edit-category-{$salad->id}");
        $response->assertDontSee("owner-category-{$salad->id}");

        // Onion → SHOULD NOT have an edit button
        $response->assertSee("owner-category-{$onion->id}");
        $response->assertDontSee("edit-category-{$onion->id}");

        // Private category - self → SHOULD have an edit button
        $response->assertSee("edit-category-{$privateSelf->id}");
        $response->assertDontSee("owner-category-{$privateSelf->id}");
        
        // Private category - other → SHOULD NOT be present
        $response->assertDontSee("edit-category-{$privateOther->id}");
        $response->assertDontSee("owner-category-{$privateOther->id}");
    }
}
