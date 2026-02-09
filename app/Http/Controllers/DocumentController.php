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

    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));

        return view('documents.index', [
            'documents' => $this->documents->forMonth($month),
            'currentMonth' => $month,
            'document' => null,
        ]);
    }

    public function show(Document $document, Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));

        return view('documents.index', [
            'documents' => $this->documents->forMonth($month),
            'currentMonth' => $month,
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

    public function edit(Document $document, Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));

        return view('documents.edit', [
            'document' => $document,
            'currentMonth' => $month,
        ]);
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
