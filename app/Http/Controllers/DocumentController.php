<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(
        protected DocumentService $documents
    ) {}

    public function index()
    {
        return view('documents.master', [
            'documents' => $this->documents->all(),
            'document' => null,
        ]);
    }

    public function show(Document $document)
    {
        return view('documents.master', [
            'documents' => $this->documents->all(),
            'document' => $document,
        ]);
    }

    public function create()
    {
        return view('documents.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        // Create the document and capture the model
        $document = $this->documents->create($validated);

        // Mark this document as expanded
        session()->put("expanded_docs.{$document->id}", true);

        return redirect()->route('documents.index');
    }

    public function edit(Document $document)
    {
        return view('documents.edit', compact('document'));
    }

    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $this->documents->update($document, $validated);

        return redirect()->route('documents.show', $document);
    }

    public function destroy(Document $document)
    {
        $this->documents->delete($document);

        return redirect()->route('documents.index')
            ->with('success', 'Document deleted.');
    }

    public function toggle(Request $request)
    {
        $id = $request->id;
        $expanded = $request->expanded;

        $state = session('expanded_docs', []);
        $state[$id] = $expanded;

        session(['expanded_docs' => $state]);

        return response()->json(['ok' => true]);
    }
}
