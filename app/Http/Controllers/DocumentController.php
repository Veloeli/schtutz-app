<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DocumentController extends Controller
{
    public function __construct(
        protected DocumentService $documents
    ) {}

/*    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));

        return view('documents.index', [
            'documents' => $this->documents->forMonth($month),
            'currentMonth' => $month,
            'document' => null,
        ]);
    }
*/
    public function index(Request $request)
    {
        $month = $request->query('month')
            ? Carbon::parse($request->query('month') . '-01')
            : now();

        $documents = Document::whereBetween('posting_date', [
            $month->copy()->startOfMonth(),
            $month->copy()->endOfMonth(),
        ])->get();

        return view('documents.index', [
            'documents' => $documents,
            'month' => $month->format('Y-m'),
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
            'title'         => 'required|string|max:255',
            'posting_date'  => 'required|date',
        ]);

        // Hidden defaults
        $validated['repeat_pattern']  = 0;
        $validated['repeat_constant'] = 0;

        // Owner is always the current user
        $validated['owner_id'] = $request->user()->id;

        // Create the document
        $document = $this->documents->create($validated);

        // Expand the newly created document in the UI
        session()->put("expanded_docs.{$document->id}", true);

        return redirect()
            ->route('documents.show', $document)
            ->with('success', 'Document created successfully.');
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
        // make sure we have a valid repeat_constant even if Blade delivers null
        if ($request->input('repeat_pattern') === "0") {
            $request->merge([
                'repeat_constant' => 0
            ]);
        }

        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'posting_date'     => 'required|date',
            'repeat_pattern'   => 'required|numeric|min:0|max:24',
            'repeat_constant'  => 'required|boolean',
        ]);

        if ((int) $validated['repeat_pattern'] === 0) {
            $validated['repeat_constant'] = 0;
        }

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
