<?php

namespace Tests\Feature\Teams;

use App\Models\User;
use App\Models\Team;
use App\Models\TeamUser;
use App\Models\Category;
use App\Models\Rollup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TeamCapabilitiesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_category_dropdown_shows_only_own_financial_teams()
    {
        // 2 users
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($user);

        // 3 teams
        $allowed = Team::factory()->forUser($user)->create(['has_common_financials' => true]);
        $blocked = Team::factory()->forUser($user)->create(['has_common_financials' => false]);
        $blocked2 = Team::factory()->forUser($other)->create(['has_common_financials' => true]);
        
        $category = Category::factory()->forUser($user)->create();
        
        $response = $this->get("/categories/{$category->id}/edit");

        $response->assertStatus(200);
        $response->assertSee($allowed->name);
        $response->assertDontSee($blocked->name);
        $response->assertDontSee($blocked2->name);
    }

    #[Test]
    public function test_rollup_dropdown_shows_only_own_reporting_teams()
    {
        // 2 users
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($user);

        // 3 teams
        $allowed = Team::factory()->forUser($user)->create(['has_common_reporting' => true]);
        $blocked = Team::factory()->forUser($user)->create(['has_common_reporting' => false]);
        $blocked2 = Team::factory()->forUser($other)->create(['has_common_reporting' => true]);
        
        $root = Rollup::factory()->forUser($user)->create();
        
        $response = $this->get("/rollups/{$root->id}/edit");

        $response->assertStatus(200);
        $response->assertSee($allowed->name);
        $response->assertDontSee($blocked->name);
        $response->assertDontSee($blocked2->name);
    }

    #[Test]
    public function test_adding_new_member_resets_every_members_reveal_private()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();


        // user 1 creates team and assigns user 2
        $this->actingAs($user1);
        $team = Team::factory()->forUser($user1)->create(['has_common_financials' => true]);
        $team->members()->attach([$user2->id]);
        $member2 = TeamUser::where('team_id', $team->id)
            ->where('user_id', $user2->id) 
            ->firstOrFail();

        // user 2 reveals private
        $this->actingAs($user2);
        $category = Category::factory()->forUser($user2)->create([
            'type' => 'CL',
            'name' => 'My beautiful clearing account',
        ]);
        $response = $this->get("/teams/{$team->id}/memberships/{$member2->id}/edit");

        $response->assertStatus(200);
        $response->assertSee("reveal_private");
        $response->assertSee("My beautiful clearing account");

        $response = $this->put("/teams/{$team->id}/memberships/{$member2->id}", [
            'reveal_private'   => true,
            'clearing_account' => $category->id,
            'sharing_ratio'    => 2
        ]);
        $response->assertRedirect(route('teams.index'));

        // user 1 assigns user 3
        $this->actingAs($user1);
        $response = $this->post("/teams/{$team->id}/memberships", [
            'email'   => $user3->email,
        ]);
        $response->assertRedirect();

        $this->assertDatabaseMissing('team_user', [
            'team_id'        => $team->id,
            'reveal_private' => 1,
        ]);
    }
    
}