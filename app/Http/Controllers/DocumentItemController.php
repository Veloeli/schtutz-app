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
        $user = auth()->user();

        // Load categories visible to the user
        $categories = app(\App\Services\CategoryService::class)
            ->getCategoriesForUser($user)
            ->sortBy('full_path');

        return view('documents.items.create', [
            'document' => $document,
            'categories' => $categories,
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
        $user = auth()->user();

        // Load categories visible to the user
        $categories = app(\App\Services\CategoryService::class)
            ->getCategoriesForUser($user)
            ->sortBy('full_path');

        return view('documents.items.edit', [
            'document' => $document,
            'item' => $item,
            'categories' => $categories,
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
