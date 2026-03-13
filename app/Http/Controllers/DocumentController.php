<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentService;
use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DocumentController extends Controller
{
    public function __construct(
        protected DocumentService $documents
    ) {}

    public function index(Request $request)
    {
        // Store month if provided
        if ($request->filled('month')) {
            session(['document_month' => $request->month]);
        }

        // Store filter if provided
        if ($request->filled('filter')) {
            session(['document_filter' => $request->filter]);
        }

        // Retrieve stored values (fallbacks if none stored)
        $month = session('document_month', now()->format('Y-m'));
        $filter = session('document_filter', 'all');

        $user = auth()->user();

        // Filter documents using the service (which uses the policy)
        $documents = $this->documents->visibleFor($user, $month, $filter);

        $teams = $user->teamsWithFinancials()->get();

        $members = User::whereIn('id', function ($q) use ($teams) {
            $q->select('user_id')
              ->from('team_user')
              ->whereIn('team_id', $teams->pluck('id'))
              ->distinct();
        })->get();

        return view('documents.index', [
            'documents' => $documents,
            'month' => $month,
            'filter' => $filter,
            'teams' => $teams,
            'members' => $members,
        ]);
    }

    public function create()
    {
        // 1. If user has a stored posting_date, use it directly
        if (session()->has('document_posting_date')) {
            $defaultPostingDate = session('document_posting_date');
        } else {
            // 2. Otherwise suggest today
            $defaultPostingDate = now()->format('Y-m-d');
        }

        return view('documents.create', [
            'posting_date' => $defaultPostingDate,
        ]);
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

        // Store posting_date in session
        session(['document_posting_date' => $validated['posting_date']]);
        
        // Expand the newly created document in the UI
        session()->put("expanded_docs.{$document->id}", true);

        // If a team filter is active, reset it to "all" (else the new document would not be visible)
        $filter = session('document_filter', 'all');
        if (str_starts_with($filter, 'team-')) {
            session(['document_filter' => 'all']);
        }

        // Set the month in session based on the document date
        $month = \Carbon\Carbon::parse($document->posting_date)->format('Y-m');
        session(['document_month' => $month]);

        return redirect()
            ->route('documents.index')
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

        // Set the month in session based on the document date
        $month = \Carbon\Carbon::parse($document->posting_date)->format('Y-m');
        session(['document_month' => $month]);

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document updated.');
    }

    public function destroy(Document $document)
    {
        $month = \Carbon\Carbon::parse($document->posting_date)->format('Y-m');

        $this->documents->delete($document);

        return redirect()
            ->route('documents.index', ['month' => $month])
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
