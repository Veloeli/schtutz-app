<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;

class DocumentItemController extends Controller
{
    public function index(Document $document)
    {
        return view('documents.items.index', [
            'document' => $document,
            'items' => $document->items,
        ]);
    }

    public function create(Document $document)
    {
        return view('documents.items.create', [
            'document' => $document,
            'categories' => Category::all(),
        ]);
    }

    public function store(Request $request, Document $document)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric',
            'quantity' => 'nullable|numeric',
            'category_id' => 'required|exists:categories,id',
        ]);

        $document->items()->create($validated);

        return redirect()->route('documents.index', $document);
    }

    public function edit(Document $document, Item $item)
    {
        // Optional safety check
        if ($item->document_id !== $document->id) {
            abort(404);
        }

        return view('documents.items.edit', [
            'document' => $document,
            'item' => $item,
            'categories' => Category::all(),
        ]);
    }

    public function update(Request $request, Document $document, Item $item)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric',
            'quantity' => 'nullable|numeric',
            'category_id' => 'required|exists:categories,id',
        ]);

        $item->update($validated);

        return redirect()->route('documents.index');
    }

    public function destroy(Document $document, Item $item)
    {
        $item->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Item deleted.');
    }
}
