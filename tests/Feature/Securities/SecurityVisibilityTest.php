<?php

namespace Tests\Feature\Securities;

use App\Models\User;
use App\Models\Team;
use App\Models\Security;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SecurityVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected $alice;
    protected $bob;
    protected $charlie;

    protected $teamA;
    protected $teamB;

    protected $secA1;
    protected $secB1;

    protected $privateAlice;
    protected $privateBob;

    protected function setUp(): void
    {
        parent::setUp();

        // Users
        $this->alice = User::factory()->create();
        $this->bob   = User::factory()->create();
        $this->charlie = User::factory()->create(); // no teams
        $this->dan   = User::factory()->create(); // both teams

        // Teams
        $this->teamA = Team::factory()->create(['has_common_securities' => 1]);
        $this->teamB = Team::factory()->create(['has_common_securities' => 1]);

        // Attach users to teams
        $this->alice->teams()->attach($this->teamA->id);
        $this->bob->teams()->attach($this->teamB->id);
        $this->dan->teams()->attach($this->teamA->id);
        $this->dan->teams()->attach($this->teamB->id);

        // Securities for team A
        $this->secA1 = Security::factory()->create([
            'team_id' => $this->teamA->id,
            'user_id' => $this->alice->id,
        ]);

        // Securities for team B
        $this->secB1 = Security::factory()->create([
            'team_id' => $this->teamB->id,
            'user_id' => $this->bob->id,
        ]);

        // Private securities
        $this->privateAlice = Security::factory()->create([
            'team_id' => null,
            'user_id' => $this->alice->id,
        ]);

        $this->privateBob = Security::factory()->create([
            'team_id' => null,
            'user_id' => $this->bob->id,
        ]);
    }

    #[Test]
    public function alice_sees_securities_of_her_own_team()
    {
        $this->actingAs($this->alice);

        $visible = Security::all();

        $this->assertTrue($visible->contains($this->secA1));
        $this->assertFalse($visible->contains($this->secB1));
    }

    #[Test]
    public function alice_sees_her_private_securities()
    {
        $this->actingAs($this->alice);

        $visible = Security::all();

        $this->assertTrue($visible->contains($this->privateAlice));
    }

    #[Test]
    public function alice_does_not_see_bobs_private_securities()
    {
        $this->actingAs($this->alice);

        $visible = Security::all();

        $this->assertFalse($visible->contains($this->privateBob));
    }

    #[Test]
    public function bob_sees_only_his_team_securities()
    {
        $this->actingAs($this->bob);

        $visible = Security::all();

        $this->assertTrue($visible->contains($this->secB1));
        $this->assertFalse($visible->contains($this->secA1));
    }

    #[Test]
    public function user_with_no_teams_sees_only_their_private_securities()
    {
        $this->actingAs($this->charlie);

        $visible = Security::all();

        // Charlie has no securities
        $this->assertCount(0, $visible);
    }

    #[Test]
    public function dan_sees_both_team_securities()
    {
        $this->actingAs($this->dan);

        $visible = Security::all();
        $this->assertTrue($visible->contains($this->secA1));
        $this->assertTrue($visible->contains($this->secB1));
        $this->assertFalse($visible->contains($this->privateAlice));
        $this->assertFalse($visible->contains($this->privateBob));
        $this->assertCount(2, $visible);
    }

    #[Test]
    public function global_scope_can_be_bypassed()
    {
        $this->actingAs($this->alice);

        $all = Security::withoutGlobalScopes()->get();

        $this->assertCount(4, $all);
    }

    #[Test]
    public function visibility_scope_uses_correct_or_logic()
    {
        $this->actingAs($this->alice);

        $visible = Security::all();

        $this->assertTrue($visible->contains($this->secA1));        // team match
        $this->assertTrue($visible->contains($this->privateAlice)); // user match
    }
}
