<?php

namespace Tests\Feature\Collections;

use App\Models\Collection;
use App\Models\User;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use DateTime;

class CollectionCreationTest extends TestCase
{
    use RefreshDatabase;

    #[test]
    public function user_can_create_private_collection()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $dateFrom = new DateTime("first day of last month");
        $dateTo   = new DateTime("last day of last month");

        $response = $this->post('/collections', [
            'name'    => 'My private collection',
            'user_id' => $user->id,
            'team_id' => null,
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to'   => $dateTo->format('Y-m-d'),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('collections', [
            'name'    => 'My private collection',
            'team_id' => null,
        ]);
    }

    #[test]
    public function user_can_create_team_collection()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $team = Team::factory()->forUser($user)->create();
        
        $dateFrom = new DateTime("first day of last month");
        $dateTo   = new DateTime("last day of last month");

        $response = $this->post('/collections', [
            'name'    => 'My team collection',
            'user_id' => $user->id,
            'team_id' => $team->id,
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to'   => $dateTo->format('Y-m-d'),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('collections', [
            'name'    => 'My team collection',
            'team_id' => $team->id,
        ]);
    }

}
