<?php

namespace App\Http\Controllers;

use App\Models\CategoryPath;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function index(Request $request)
    {
        // Store teamfilter if provided
        if ($request->filled('teamfilter')) {
            session(['document_teamfilter' => $request->teamfilter]);
        }

        $selectedDate = request('date', now()->toDateString());

        // Retrieve stored values (fallbacks if none stored)
        $teamfilter = session('document_teamfilter', 'all');

        $user = auth()->user();

        $selectedRoot = $user->resolveActiveRoot(request());

        // Parse filter
        [$filterType, $rawValue] = $teamfilter && str_contains($teamfilter, '-')
            ? explode('-', $teamfilter)
            : [null, null];


        $filterValue = str_contains($rawValue, ',')
            ? explode(',', $rawValue)
            : $rawValue;

        $balances = CategoryPath::from('category_paths_view')
            ->selectRaw("
                category_paths_view.name,
                category_paths_view.rollup_id,
                category_paths_view.depth,
                SUM(i.amount) AS total_amount
            ")
            ->selectRaw("CASE WHEN category_paths_view.src = 'category' THEN category_paths_view.category_id END AS category_id")
            ->selectRaw("CASE WHEN category_paths_view.src = 'category' THEN category_paths_view.team_id END AS team_id")
            ->leftJoin('teams as t', 't.id', '=', 'category_paths_view.team_id') // LEFT JOIN as requested
            ->join('items as i', 'i.category_id', '=', 'category_paths_view.category_id')
            ->join('documents as d', 'd.id', '=', 'i.document_id')
            ->whereIn('category_paths_view.type', ['AL', 'AP', 'CL'])
            ->where('category_paths_view.root_id', $selectedRoot->id)
            ->where('d.posting_date', '<=', $selectedDate)
            ->when($filterType === 'member', function ($q) use ($filterValue) {
                $q->where('d.user_id', $filterValue);
            })
            ->when($filterType === 'team', function ($q) use ($filterValue) {
                $q->where('category_paths_view.team_id', $filterValue);
            })
            ->groupBy('category_paths_view.rollup_id', 'category_id')
            ->groupByRaw("CASE WHEN category_paths_view.src = 'category' THEN category_paths_view.category_id END")
            ->havingRaw('ABS(SUM(i.amount)) > 0.01')
            ->orderBy('code')
            ->orderBy('path')
            ->with(['team','category']) 
            ->get();

        return view('balances.index', [
            'balances' => $balances,
            'teamfilter' => $teamfilter,
        ]);
    }
}
