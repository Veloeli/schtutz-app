<?php

namespace App\Http\Controllers;

use App\Models\StockSplit;
use App\Models\Security;
use Illuminate\Http\Request;

class StockSplitController extends Controller
{
    public function create(Request $request)
    {
        $oldId = $request->query('old_id');
        
        $split = new StockSplit();
        $split->old_id = $oldId;
        $split->new_id = $oldId;
        
        return view('stock-splits.create', [
            'split' => $split,
            'allSecurities' => Security::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'old_id' => 'required|exists:securities,id',
            'new_id' => 'required|exists:securities,id',
            'split_date' => 'required|date',
            'split_factor' => 'required|numeric|min:0.0000001',
        ]);

        $split = StockSplit::create($validated);

        return redirect()
            ->route('securities.edit', $validated['old_id'])
            ->with('success', 'Stock split created.');
    }

    public function edit(StockSplit $stock_split)
    {
        $allSecurities = Security::orderBy('name')->get();

        return view('stock-splits.edit', [
            'split' => $stock_split,
            'allSecurities' => $allSecurities,
        ]);
    }

    public function update(Request $request, StockSplit $stock_split)
    {
        $validated = $request->validate([
            'old_id' => 'required|exists:securities,id',
            'new_id' => 'required|exists:securities,id',
            'split_date' => 'required|date',
            'split_factor' => 'required|numeric|min:0.0000001',
        ]);

        $stock_split->update($validated);

        return redirect()
            ->route('securities.edit', $validated['old_id'])
            ->with('success', 'Stock split updated.');
    }

    public function destroy(StockSplit $stock_split)
    {
        $oldId = $stock_split->old_id;

        $stock_split->delete();

        return redirect()
            ->route('securities.edit', $oldId)
            ->with('success', 'Stock split deleted.');
    }
}
