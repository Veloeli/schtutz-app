<?php

namespace Tests\Feature\Documents;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Team;
use App\Models\TeamUser;
use App\Models\Category;
use App\Models\Document;
use App\Models\Item;
use App\Services\ReconciliationService;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class DocumentReconciliationTest extends TestCase
{
    use RefreshDatabase;

    #[test]
    public function it_runs_full_reconciliation_for_three_users()
    {
        $month = '2026-07-01';
        $firstday = Carbon::parse($month)->startOfMonth();

        // ---------------------------------------------------------
        // 1. Create users
        // ---------------------------------------------------------
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $u3 = User::factory()->create();

        // ---------------------------------------------------------
        // 2. Create team with 3 members
        // ---------------------------------------------------------
        $team = Team::factory()->create([
            'user_id' => $u1->id,
        ]);

        TeamUser::create([
            'team_id' => $team->id,
            'user_id' => $u2->id,
            'clearing_account' => null,
        ]);

        TeamUser::create([
            'team_id' => $team->id,
            'user_id' => $u3->id,
            'clearing_account' => null,
        ]);

        // ---------------------------------------------------------
        // 3. Create categories
        // ---------------------------------------------------------

        // Each user gets:
        // - 1 clearing category
        // - 1 asset category
        // - 1 expense category (2 team-assigned, 1 not)

        $u1_clear = Category::factory()->create(['user_id' => $u1->id, 'team_id' => null, ]);
        $u1_asset = Category::factory()->create(['user_id' => $u1->id, 'team_id' => null, ]);
        $u1_exp   = Category::factory()->create(['user_id' => $u1->id, 'team_id' => $team->id,]);

        $u2_clear = Category::factory()->create(['user_id' => $u2->id, 'team_id' => null, ]);
        $u2_asset = Category::factory()->create(['user_id' => $u2->id, 'team_id' => null, ]);
        $u2_exp   = Category::factory()->create(['user_id' => $u2->id, 'team_id' => $team->id,]); // not used

        $u3_clear = Category::factory()->create(['user_id' => $u3->id, 'team_id' => null, ]);
        $u3_asset = Category::factory()->create(['user_id' => $u3->id, 'team_id' => null, ]);
        $u3_exp   = Category::factory()->create(['user_id' => $u3->id, 'team_id' => null,]);

        // Assign clearing accounts
        TeamUser::where('user_id', $u1->id)->update(['clearing_account' => $u1_clear->id]);
        TeamUser::where('user_id', $u2->id)->update(['clearing_account' => $u2_clear->id]);
        TeamUser::where('user_id', $u3->id)->update(['clearing_account' => $u3_clear->id]);

        // ---------------------------------------------------------
        // 4. Create documents + items
        // ---------------------------------------------------------

        // User 1: +15 expense on team account
        $doc1 = Document::factory()->create([
            'user_id' => $u1->id,
            'posting_date' => $firstday,
        ]);

        Item::factory()->create(['document_id' => $doc1->id, 'category_id' => $u1_exp->id,   'amount' => 15]);
        Item::factory()->create(['document_id' => $doc1->id, 'category_id' => $u1_asset->id, 'amount' => -15]);

        // User 2: +30 expense on SAME team account
        $doc2 = Document::factory()->create([
            'user_id' => $u2->id,
            'posting_date' => $firstday,
        ]);

        Item::factory()->create(['document_id' => $doc2->id, 'category_id' => $u1_exp->id,   'amount' => 30]);
        Item::factory()->create(['document_id' => $doc2->id, 'category_id' => $u2_asset->id, 'amount' => -30]);

        // User 3: +77 expense on private account
        $doc3 = Document::factory()->create([
            'user_id' => $u3->id,
            'posting_date' => $firstday,
        ]);

        Item::factory()->create(['document_id' => $doc3->id, 'category_id' => $u3_exp->id,   'amount' => 77]);
        Item::factory()->create(['document_id' => $doc3->id, 'category_id' => $u3_asset->id, 'amount' => -77]);

        // ---------------------------------------------------------
        // 5. Read rows from the DB view
        // ---------------------------------------------------------

        $rows = DB::table('sharing_view')
            ->where('team_id', $team->id)
            ->where('firstday', $firstday->toDateString())
            ->orderBy('user_id')
            ->orderBy('category_id')
            ->get();

        $this->assertNotEmpty($rows, 'sharing_view returned no rows — test setup failed');

        // ---------------------------------------------------------
        // 6. Run reconciliation
        // ---------------------------------------------------------
        $service = app()->make(ReconciliationService::class);
        $created = $service->run($team, $month, $rows);

        // ---------------------------------------------------------
        // 7. Assertions
        // ---------------------------------------------------------

        // Should create 2 reconciliation documents
        $this->assertCount(2, $created);

        // User 1 no reconciliation item
        $this->assertDatabaseMissing('items', [
            'category_id' => $u1_clear->id,
        ]);

        // User 2 reconciliation item: -15 (receive 15)
        $this->assertDatabaseHas('items', [
            'category_id' => $u2_clear->id,
            'amount' => 15,
        ]);

        // User 3 reconciliation item: +15 (pay 15)
        $this->assertDatabaseHas('items', [
            'category_id' => $u3_clear->id,
            'amount' => -15,
        ]);
    }
}
