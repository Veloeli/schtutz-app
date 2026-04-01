<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Document;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        // Store filter if provided
        if ($request->filled('listing_id')) {
            session(['listing_id' => $request->listing_id]);
        }

        // Retrieve stored values (fallbacks if none stored)
        $selectedListingId = session('listing_id', null);

        $listings = Listing::orderBy('date_from','desc')->get();

        $documents = collect();
        $listing = null;
        $balance = null;

        if ($selectedListingId) {

            // Load the selected listing (gives you date_from, date_to)
            $listing = Listing::find($selectedListingId);
            // Summarized documents query
            $documents = Document::query()
                ->join('items', 'items.document_id', '=', 'documents.id')
                ->join('listing_item', 'listing_item.item_id', '=', 'items.id')
                ->join('users', 'users.id', '=', 'documents.user_id') // ← required
                ->where('listing_item.listing_id', $selectedListingId)
                ->select([
                    'documents.id',
                    'documents.posting_date',
                    'documents.title',
                    'documents.user_id',
                    'users.name as user_name',
                    DB::raw('SUM(CASE WHEN listing_item.change_sign THEN -items.amount ELSE items.amount END) AS total_amount'),
                ])
                ->groupBy(
                    'documents.id',
                    'documents.posting_date',
                    'documents.title',
                    'documents.user_id',
                    'users.name'
                )
                ->orderBy('documents.posting_date', 'desc')
                ->orderBy('documents.title')
                ->get();

            // Compute balance from summarized documents
            $balance = $documents->sum('total_amount');
        }

        return view('listings.index', compact(
            'listings',
            'selectedListingId',
            'documents',
            'listing',
            'balance'
        ));
    }

    public function create()
    {
        return view('listings.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date_from' => 'required|date',
            'date_to' => 'nullable|date',
            'team_id' => 'nullable|exists:teams,id',
        ]);

        // Owner is always the current user
        $validated['user_id'] = $request->user()->id;

        $listing = Listing::create($validated);

        return redirect()
            ->route('listings.index', ['listing_id' => $listing->id])
            ->with('success', 'Listing created successfully.');
    }

    public function edit(Listing $listing)
    {
        $user = auth()->user();
        $teams = $user->teamsWithFinancials()->get();

        // Load listing's attached items + their categories + their documents
        $listing->load([
            'items.category',
            'documents',
        ]);

        // Load all documents in date range
        $documents = Document::query()
            ->whereBetween('posting_date', [$listing->date_from, $listing->date_to ?? '2999-12-31'])
            ->with(['items.category'])
            ->orderBy('posting_date')
            ->get();

        // Sort items inside each document
        $documents->each(function ($doc) {
            $doc->items = $doc->items->sortBy([
                fn ($item) => $item->category->type_label,
                fn ($item) => $item->category->name,
                fn ($item) => $item->name,
            ])->values();
        });

        return view('listings.edit', [
            'listing'   => $listing,
            'teams'     => $teams,
            'documents' => $documents,
        ]);
    }

    public function update(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date_from' => 'required|date',
            'date_to' => 'nullable|date',
            'team_id' => 'nullable|exists:teams,id',

            'items' => 'array',
            'items.*' => 'integer|exists:items,id',
        ]);

        // Update listing fields
        $listing->update($validated);

        // Selected items (or empty array)
        $selectedItems = $request->input('items', []);

        // Change-sign array: [item_id => 1]
        $changeSign = $request->input('change_sign', []);

        // Build sync array with pivot data
        $syncData = collect($selectedItems)->mapWithKeys(function ($itemId) use ($changeSign) {
            return [
                $itemId => [
                    'change_sign' => isset($changeSign[$itemId]) ? 1 : 0,
                ]
            ];
        })->toArray();

        // Sync items + pivot fields
        $listing->items()->sync($syncData);

        return redirect()
            ->route('listings.index', ['listing_id' => $listing->id])
            ->with('success', 'Listing updated successfully.');
    }

    public function destroy(Listing $listing)
    {
        $listing->delete();

        return redirect()->route('listings.index')
            ->with('success', 'Listing deleted.');
    }
}
