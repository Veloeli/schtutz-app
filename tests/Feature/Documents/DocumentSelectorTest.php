<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Team;
use App\Models\TeamUser;
use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DocumentSelectorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[test]
    public function test_user_sees_data_for_selected_month()
    {
        $user = User::factory()->create();

        // Arrange: create documents in different months
        Document::factory()->create([
            'posting_date' => '2024-02-10',
            'title' => 'February doc',
            'user_id' => $user->id,
        ]);

        Document::factory()->create([
            'posting_date' => '2024-03-10',
            'title' => 'March doc',
            'user_id' => $user->id,
        ]);

        // Act: user selects February
        $this->actingAs($user);
        $response = $this->get('/documents?month=2024-02');

        $response->assertOk();

        // Assert: only February data is visible
        $response->assertSee('February doc');
        $response->assertDontSee('March doc');
    }

    #[test]
    public function test_defaults_to_current_month()
    {
        Carbon::setTestNow('2024-02-15');

        $user = User::factory()->create();

        Document::factory()->create([
            'posting_date' => '2024-02-01',
            'title' => 'Current month doc',
            'user_id' => $user->id,
        ]);

        Document::factory()->create([
            'posting_date' => '2024-03-01',
            'title' => 'Next month doc',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);
        $response = $this->get('/documents');

        $response->assertOk();

        $response->assertSee('Current month doc');
        $response->assertDontSee('Next month doc');
    }

    #[test]
    public function test_filter_by_user_and_team()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $team1 = Team::factory()->create(['user_id' => $user1->id, 'has_common_financials' => 1,]);
        //TeamUser::factory()->create(['user_id' => $user1->id, 'team_id' => $team1->id, 'reveal_private' => 1, ]);
        TeamUser::factory()->create(['user_id' => $user2->id, 'team_id' => $team1->id, 'reveal_private' => 1, ]);

        // Update the auto-created pivot row
        \DB::table('team_user')
            ->where('team_id', $team1->id)
            ->where('user_id', $user1->id)
            ->update(['reveal_private' => 1]);

        Document::factory()->create(['user_id' => $user1->id, 'title' => 'User 1 doc',]);
        Document::factory()->create(['user_id' => $user2->id, 'title' => 'User 2 doc',]);

        $this->actingAs($user1);

        $response = $this->get('/documents');
        $response->assertOk();

        // options
        $response->assertSee('<optgroup label="Teams">', false);
        $response->assertSee('team-' . $team1->id, false);
        $response->assertSee('<optgroup label="Members">', false);
        $response->assertSee('member-' . $user1->id, false);
        $response->assertSee('member-' . $user2->id, false);

        // count options in filter dropdown
        $html = $response->getContent();
        $optionCount = substr_count($html, '<option value="team-');
        $this->assertSame(1, $optionCount);
        $optionCount = substr_count($html, '<option value="member-');
        $this->assertSame(2, $optionCount);
        
        // defaults to all
        $response->assertSee('User 1 doc');
        $response->assertSee('User 2 doc');

        // team filter - no team postings
        $response = $this->get('/documents?teamfilter=team-' . $team1->id);
        $response->assertOk();
        $response->assertDontSee('User 1 doc');
        $response->assertDontSee('User 2 doc');

        // member 1 filter
        $response = $this->get('/documents?teamfilter=member-' . $user1->id);
        $response->assertOk();
        $response->assertSee('User 1 doc');
        $response->assertDontSee('User 2 doc');

        // member 2 filter
        $response = $this->get('/documents?teamfilter=member-' . $user2->id);
        $response->assertOk();
        $response->assertDontSee('User 1 doc');
        $response->assertSee('User 2 doc');

    }
}
