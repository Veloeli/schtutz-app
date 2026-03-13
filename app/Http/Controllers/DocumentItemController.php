<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Log;

class DocumentItemController extends Controller
{
    protected CategoryService $categories;

    public function __construct(CategoryService $categories)
    {
        $this->categories = $categories;
    }

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

        // Load categories visible to the user on the document date
        $categories = $this->categories->visibleForDocument($user, $document)
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

    // Load categories visible to the user on the document date
    $categories = $this->categories->visibleForDocument($user, $document)
        ->sortBy('full_path');

    // Load the item's current category so Blade can access it
    $item->load('category');

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

    public function suggestCategory(Request $request, Document $document)
    {
        $name = $request->query('name');

        if (!$name || strlen($name) < 3) {
            return response()->json(['category_id' => null]);
        }

        $categoryId = app(CategoryService::class)
            ->suggestCategory($request->user(), $document, $name);

        return response()->json(['category_id' => $categoryId]);
    }
    
}
