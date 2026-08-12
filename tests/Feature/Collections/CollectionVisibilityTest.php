<?php

namespace Tests\Feature\Collections;

use App\Models\Collection;
use App\Models\User;
use App\Models\Team;
use App\Models\TeamUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use DateTime;

class CollectionVisibilityTest extends TestCase
{
    use RefreshDatabase;

    #[test]
    public function private_collection_is_hidden_from_others()
    {
        $me    = User::factory()->create();
        $other = User::factory()->create();

        $own = Collection::factory()->forUser($me)->create();

        // visible for me
        $this->actingAs($me);

        $visible = Collection::get();

        $this->assertTrue($visible->contains($own));
        $this->assertCount(1, $visible);

        // hidden for others
        $this->actingAs($other);

        $visible = Collection::get();

        $this->assertFalse($visible->contains($own));
        $this->assertCount(0, $visible);
    }

    #[test]
    public function team_collection_is_visible_to_other_members()
    {
        $me    = User::factory()->create();
        $other = User::factory()->create();
        
        $team  = Team::factory()->forUser($me)->create(['has_common_financials' => 1,]);
        TeamUser::factory()->create(['team_id' => $team->id, 'user_id' => $other->id,]);

        $own = Collection::factory()->forUser($me)->forTeam($team)->create();

        // visible for me
        $this->actingAs($me);

        $visible = Collection::get();

        $this->assertTrue($visible->contains($own));
        $this->assertCount(1, $visible);

        // hidden for others
        $this->actingAs($other);

        $visible = Collection::get();

        $this->assertTrue($visible->contains($own));
        $this->assertCount(1, $visible);
    }

}
