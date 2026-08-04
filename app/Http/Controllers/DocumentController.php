<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentService;
use App\Services\CategoryService;
use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DocumentController extends Controller
{
    public function __construct(
        protected DocumentService $documents,
        protected CategoryService $categories,
    ) {}

    public function index(Request $request)
    {
        // Store month if provided
        if ($request->filled('month')) {
            session(['document_month' => $request->month]);
        }

        // Store teamfilter if provided
        if ($request->filled('teamfilter')) {
            session(['document_teamfilter' => $request->teamfilter]);
        }

        // Retrieve stored values (fallbacks if none stored)
        $month = session('document_month', now()->format('Y-m'));
        $teamfilter = session('document_teamfilter', 'all');

        $user = auth()->user();

        // teamfilter documents using the service (which uses the policy)
        $documents = $this->documents->visibleFor($user, $month, $teamfilter);

        return view('documents.index', [
            'documents' => $documents,
            'month' => $month,
            'teamfilter' => $teamfilter,
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
            'posting_date'   => $defaultPostingDate,
            'possibleOwners' => auth()->user()->possibleDocumentOwners(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'posting_date'     => 'required|date',
            'user_id'          => 'required|integer',
        ]);

        // Freeze-after check
        if ($this->isPostingDateFrozen($request->user(), $validated['posting_date'])) {
            return back()
                ->withErrors(['posting_date' => 'This posting date is too old and cannot be used.'])
                ->withInput();
        }

        // Hidden defaults
        $validated['repeat_pattern']  = 0;
        $validated['repeat_constant'] = 0;

        // Create the document
        $document = $this->documents->create($validated);

        // Store posting_date in session
        session(['document_posting_date' => $validated['posting_date']]);
        
        // Expand the newly created document in the UI
        session()->put("expanded_docs.{$document->id}", true);

        // If a team teamfilter is active, reset it to "all" (else the new document would not be visible)
        $teamfilter = session('document_teamfilter', 'all');
        if (str_starts_with($teamfilter, 'team-')) {
            session(['document_teamfilter' => 'all']);
        }

        // Set the month in session based on the document date
        $month = \Carbon\Carbon::parse($document->posting_date)->format('Y-m');
        session(['document_month' => $month]);

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document created successfully.');
    }

    public function edit(Document $document)
    {
        return view('documents.edit', [
            'document'       => $document,
            'possibleOwners' => auth()->user()->possibleDocumentOwners(),
        ]);
    }

    public function update(Request $request, Document $document)
    {
        $oldOwnerId = $document->user_id;
        $newOwnerId = $request->input('user_id');
        
        // make sure we have a valid repeat_constant even if Blade delivers null
        if ($request->input('repeat_pattern') === "0") {
            $request->merge([
                'repeat_constant' => 0
            ]);
        }

        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'posting_date'     => 'required|date',
            'user_id'          => 'required|integer',
            'repeat_pattern'   => 'required|numeric|min:0|max:24',
            'repeat_constant'  => 'required|boolean',
        ]);

        // Freeze-after check
        if ($this->isPostingDateFrozen($request->user(), $validated['posting_date'])) {
            return back()
                ->withErrors(['posting_date' => 'This posting date is too old and cannot be used.'])
                ->withInput();
        }

        if ((int) $validated['repeat_pattern'] === 0) {
            $validated['repeat_constant'] = 0;
        }

        $this->documents->update($document, $validated);

        // Set the month in session based on the document date
        $month = \Carbon\Carbon::parse($document->posting_date)->format('Y-m');
        session(['document_month' => $month]);

        // If owner changed, clean up invalid items
        if ($oldOwnerId != $newOwnerId) {

            $newOwner = User::find($newOwnerId);

            // Use the SAME visibility logic as DocumentItemController
            $validCategories = $this->categories
                ->visibleForDocument($newOwner, $document)
                ->pluck('id');

            // Delete items whose categories are not visible to the new owner
            $document->items()
                ->whereNotIn('category_id', $validCategories)
                ->delete();
        }

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

    protected function isPostingDateFrozen(User $user, string $postingDate): bool
    {
        if (!$user->freeze_after) {
            return false;
        }

        $freezeDate = now()->subMonths($user->freeze_after);

        return \Carbon\Carbon::parse($postingDate)->lt($freezeDate);
    }
}
