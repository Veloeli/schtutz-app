<?php

namespace Tests\Feature\Teams;

use App\Models\User;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TeamMembershipTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_create_a_team()
    {
        $owner = User::factory()->create();

        $this->actingAs($owner);

        $team = Team::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'My Test Team',
        ]);

        // Assertions
        $this->assertEquals($owner->id, $team->owner_id);
        $this->assertTrue($team->members->contains($owner));
        $this->assertCount(1, $team->members);
    }

    #[Test]
    public function user_can_add_second_team_member()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($owner);

        $team = Team::factory()->create([
            'owner_id' => $owner->id,
        ]);

        // Attach member
        $team->members()->attach($other->id);

        $team->load('members');

        $this->assertCount(2, $team->members);
        $this->assertTrue($team->members->contains($owner));
        $this->assertTrue($team->members->contains($other));
    }

    #[Test]
    public function other_user_can_change_membership_settings()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $team = Team::factory()->create([
            'owner_id' => $owner->id,
        ]);

        // Attach member
        $team->members()->attach($other->id);

        // Other updates membership settings
        $this->actingAs($other);

        $team->members()->updateExistingPivot($other->id, [
            'reveal_private' => true,
        ]);

        // Reload the relationship
        $team->load('members');

        $updated = $team->members()
            ->where('user_id', $other->id)
            ->first()
            ->pivot
            ->reveal_private;

        $this->assertEquals(true, $updated);
    }

}
