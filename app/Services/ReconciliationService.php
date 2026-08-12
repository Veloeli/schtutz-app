<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Item;
use App\Models\Team;
use App\Models\TeamUser;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReconciliationService
{
    public function run($team, string $month, $rows): array
    {
        $firstday = Carbon::parse($month)->startOfMonth();
        $lastday  = $firstday->copy()->endOfMonth();

        $teamId = $team->id; 

        return DB::transaction(function () use ($team, $teamId, $firstday, $lastday, $rows) {

            // Delete existing wizard documents of the team
            Document::whereBetween('posting_date', [$firstday, $lastday])
                ->whereIn(DB::raw("COALESCE(wizard, '')"), ['T', 't'])
                ->whereExists(function ($q) use ($team) {
                    $q->from('items')
                      ->join('categories', 'items.category_id', '=', 'categories.id')
                      ->whereColumn('items.document_id', 'documents.id')
                      ->where('categories.team_id', $team->id);
                })
                ->delete();

            // Skip month if no gaps
            $hasGaps = $rows->contains(fn($r) => (float)$r->gap !== 0.0);
            if (!$hasGaps) {
                return [];
            }

            // Group rows by user
            $grouped = $rows->groupBy('user_id');

            $createdDocuments = [];

            foreach ($grouped as $userId => $userRows) {

                // Skip user if no gaps
                $userHasGaps = $userRows->contains(fn($r) => (float)$r->gap !== 0.0);
                if (!$userHasGaps) {
                    continue;
                }

                $teamUser = TeamUser::where('team_id', $teamId)
                    ->where('user_id', $userId)
                    ->firstOrFail();

                $clearingCategoryId = $teamUser->clearing_account;

                // Create document
                $doc = Document::create([
                    'user_id'       => $userId,
                    'posting_date'  => $firstday,
                    'wizard'        => 'T',
                    'title'         => sprintf(
                        'Reconciliation %s (%s)',
                        $firstday->format('Y-m'),
                        $team->name
                    ),
                    'repeate_pattern' => '0',
                    'currency_rate'   => '1.0',
                ]);

                $total = 0;

                foreach ($userRows as $row) {
                    if ((float)$row->target_sharing !== 0.0) {
                        Item::create([
                            'document_id' => $doc->id,
                            'category_id' => $row->category_id,
                            'amount'      => $row->target_sharing,
                        ]);

                        $total += $row->target_sharing;
                    }
                }

                if ($total != 0) {
                    Item::create([
                        'document_id' => $doc->id,
                        'category_id' => $clearingCategoryId,
                        'amount'      => -$total,
                    ]);
                }

                $createdDocuments[] = $doc;
            }

            return $createdDocuments;
        });
    }
}
