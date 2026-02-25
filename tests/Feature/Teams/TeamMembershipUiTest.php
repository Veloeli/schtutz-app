<?php

namespace Tests\Feature\Teams;

use App\Models\User;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TeamMembershipUiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function only_logged_in_user_sees_edit_button_on_their_membership_row()
    {
        $owner = User::factory()->create();
        $peter = User::factory()->create();
        $andrea = User::factory()->create();

        // Team owned by Andrea
        $team = Team::factory()->create([
            'owner_id' => $andrea->id,
        ]);

        // Members: Peter + Andrea
        $team->members()->attach([$peter->id, $andrea->id]);

        // Peter is logged in
        $this->actingAs($peter);

        $response = $this->get(route('teams.edit', $team));

        // Peter should see his own edit button
        $response->assertSee("edit-membership-{$peter->id}");

        // Peter should NOT see Andrea's edit button
        $response->assertDontSee("edit-membership-{$andrea->id}");
    }
}
