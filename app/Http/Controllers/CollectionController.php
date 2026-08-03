<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Document;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        // Store filter if provided
        if ($request->filled('collection_id')) {
            session(['collection_id' => $request->collection_id]);
        }

        // Retrieve stored values (fallbacks if none stored)
        $selectedCollectionId = session('collection_id', null);

        $collections = Collection::orderBy('date_from','desc')->get();

        $documents = collect();
        $collection = null;
        $balance = null;

        if ($selectedCollectionId) {

            // Load the selected collection (gives date_from, date_to)
            $collection = Collection::find($selectedCollectionId);
            // Summarized documents query
            $documents = Document::query()
                ->join('items', 'items.document_id', '=', 'documents.id')
                ->join('collection_item', 'collection_item.item_id', '=', 'items.id')
                ->join('users', 'users.id', '=', 'documents.user_id')
                ->where('collection_item.collection_id', $selectedCollectionId)
                ->select([
                    'documents.id',
                    'documents.posting_date',
                    'documents.title',
                    'documents.user_id',
                    'users.name as user_name',
                    DB::raw('SUM(CASE WHEN collection_item.change_sign THEN -items.amount ELSE items.amount END) AS total_amount'),
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

        return view('collections.index', compact(
            'collections',
            'selectedCollectionId',
            'documents',
            'collection',
            'balance'
        ));
    }

    public function create()
    {
        return view('collections.create');
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

        $collection = Collection::create($validated);

        return redirect()
            ->route('collections.index', ['collection_id' => $collection->id])
            ->with('success', 'Collection created successfully.');
    }

    public function edit(Collection $collection)
    {
        $user  = auth()->user();
        $teams = $user->teamsWithFinancials()->get();

        // Load visible items + categories + documents
        $collection->load([
            'items.category',
            'documents',
        ]);

        // Load all documents in date range
        $documents = Document::query()
            ->whereBetween('posting_date', [$collection->date_from, $collection->date_to ?? '2999-12-31'])
            ->with(['items.category'])
            ->orderBy('documents.posting_date', 'desc')
            ->orderBy('documents.title')
            ->paginate(5);
        // Sort items inside each document
        $documents->getCollection()->transform(function ($doc) {
            $doc->items = $doc->items->sortBy([
                fn ($item) => $item->category->type_label,
                fn ($item) => $item->category->name,
                fn ($item) => $item->name,
            ])->values();

            return $doc;
        });

        // Pivot items (attached to collection)
        $pivotItems = DB::table('collection_item')
            ->where('collection_id', $collection->id)
            ->pluck('item_id')
            ->toArray();
        $visibleItems = $documents
            ->pluck('items')      // collection of item collections
            ->flatten()           // flatten into one collection
            ->filter(fn ($item) => in_array($item->id, $pivotItems))
            ->pluck('id')
            ->unique()
            ->values()
            ->toArray();

        return view('collections.edit', [
            'collection'   => $collection,
            'teams'        => $teams,
            'documents'    => $documents,
            'originalItems'=> $visibleItems,
        ]);
    }

    public function update(Request $request, Collection $collection)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'date_from' => 'required|date',
            'date_to'   => 'nullable|date',
            'team_id'   => 'nullable|exists:teams,id',

            'items'     => 'array',
            'items.*'   => 'integer|exists:items,id',
        ]);

        // Update collection fields
        $collection->update($validated);

        // Round-trip data (visible items only)
        $originalItems = $request->input('original_items', []); // visible at edit time
        $selectedItems = $request->input('items', []);          // visible selected now
        $changeSign    = $request->input('change_sign', []);    // new sign values

        //
        // 1. ATTACH OR UPDATE SELECTED ITEMS (visible only)
        //
        foreach ($selectedItems as $itemId) {
            $newValue = isset($changeSign[$itemId]) ? 1 : 0;

            if (! in_array($itemId, $originalItems)) {
                // Newly attached (visible)
                $collection->items()->attach($itemId, [
                    'change_sign' => $newValue,
                ]);
            } else {
                // Previously attached (visible) → update sign
                $collection->items()->updateExistingPivot($itemId, [
                    'change_sign' => $newValue,
                ]);
            }
        }

        //
        // 2. DETACH ONLY VISIBLE ITEMS THE USER EXPLICITLY REMOVED
        //
        $itemsToDetach = array_diff($originalItems, $selectedItems);

        if (! empty($itemsToDetach)) {
            $collection->items()->detach($itemsToDetach);
        }

        if ($request->filled('goto_page')) {
            return redirect($request->goto_page);
        }
        else {
            return redirect()
                ->route('collections.index', ['collection_id' => $collection->id])
                ->with('success', 'Collection updated successfully.');
            }
    }

    public function destroy(Collection $collection)
    {
        $collection->delete();

        return redirect()->route('collections.index')
            ->with('success', 'Collection deleted.');
    }
}
