<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentService;
use App\Services\CategoryService;
use App\Services\ReconciliationService;
use App\Models\User;
use App\Models\Team;
use App\Models\TeamUser;
use App\Models\Security;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // month dropdown ranges
        $range = Document::query()
            ->selectRaw('MIN(posting_date) as min_date, MAX(posting_date) as max_date')
            ->first();

        $min = \Carbon\Carbon::parse($range->min_date)->startOfMonth();
        $max = \Carbon\Carbon::parse($range->max_date)->startOfMonth();

        // Build valid month list
        $validMonths = [];
        for ($d = $min->copy(); $d <= $max; $d->addMonth()) {
            $validMonths[] = $d->format('Y-m');
        }

        // Fallback if selected month no longer exists
        if (!in_array($month, $validMonths)) {
            $month = now()->format('Y-m');
            session(['document_month' => $month]);   // keep UI + controller aligned
        }

        // teamfilter documents using the service (which uses the policy)
        $documents = $this->documents->visibleFor($user, $month, $teamfilter);

        return view('documents.index', [
            'documents' => $documents,
            'month' => $month,
            'teamfilter' => $teamfilter,
            'minMonth' => $min,
            'maxMonth' => $max,
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

        // 3. Available currencies for this user (active only)
        $availableCurrencies = Security::availableCurrencies(auth()->user())->get();

        return view('documents.create', [
            'posting_date'       => $defaultPostingDate,
            'possibleOwners'     => auth()->user()->possibleDocumentOwners(),
            'availableCurrencies'=> $availableCurrencies,   // NEW
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'posting_date'     => 'required|date',
            'user_id'          => 'required|integer',
            'currency_id'      => 'nullable|integer',
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

        // Create the document (currency_id included)
        $document = $this->documents->create($validated);

        // CASE 1: No currency selected → ensure rate = 1.0
        if (empty($validated['currency_id'])) {
            $document->currency_id  = null;
            $document->currency_rate = 1.0;
            $document->save();
        }

        // CASE 2: Currency selected → validate + compute FX rate
        if (!empty($validated['currency_id'])) {

            // Validate visibility: only currencies assigned to user or teams AND is_in_use=1
            $allowed = Security::availableCurrencies($request->user())->pluck('id');

            // Document may use a legacy currency → allow it
            $isLegacy = Security::where('id', $validated['currency_id'])
                                ->where('asset_class', 'FX')
                                ->exists();

            if (!$allowed->contains($validated['currency_id']) && !$isLegacy) {
                return back()
                    ->withErrors(['currency_id' => 'This currency is not available.'])
                    ->withInput();
            }

            // Compute FX rate using your recursive model method
            $currency = Security::find($validated['currency_id']);
            $rate = $currency->quoteAt(\Carbon\Carbon::parse($validated['posting_date']));

            // Persist rate
            $document->currency_id  = $currency->id;
            $document->currency_rate = $rate;
            $document->save();
        }

        // Store posting_date in session
        session(['document_posting_date' => $validated['posting_date']]);
        
        // Expand the newly created document in the UI
        session()->put("expanded_docs.{$document->id}", true);

        // If a team teamfilter is active, reset it to "all"
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
        $user = auth()->user();

        return view('documents.edit', [
            'document'           => $document,
            'possibleOwners'     => $user->possibleDocumentOwners(),
            'availableCurrencies'=> Security::currenciesForDocument($document, $user),
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
            'currency_id'      => 'nullable|integer',   // NEW
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

        /*
        |--------------------------------------------------------------------------
        | CURRENCY LOGIC
        |--------------------------------------------------------------------------
        */

        $newCurrencyId = $validated['currency_id'] ?? null;
        $oldCurrencyId = $document->currency_id;

        // CASE 1: Currency removed
        if ($newCurrencyId === null) {
            $validated['currency_id']  = null;
            $validated['currency_rate'] = 1.0;
        }

        // CASE 2: Currency changed or newly assigned
        if ($newCurrencyId !== null && $newCurrencyId != $oldCurrencyId) {

            // Validate visibility: only currencies assigned to user or teams AND is_in_use=1
            $allowed = Security::availableCurrencies($request->user())->pluck('id');

            // Document may use a legacy currency → allow it
            $isLegacy = Security::where('id', $newCurrencyId)
                                ->where('asset_class', 'FX')
                                ->exists();

            if (!$allowed->contains($newCurrencyId) && !$isLegacy) {
                return back()
                    ->withErrors(['currency_id' => 'This currency is not available.'])
                    ->withInput();
            }

            // Compute FX rate using your recursive model method
            $currency = Security::find($newCurrencyId);
            $rate = $currency->quoteAt(\Carbon\Carbon::parse($validated['posting_date']));

            $validated['currency_id']  = $newCurrencyId;
            $validated['currency_rate'] = $rate;
        }

        // CASE 3: Currency unchanged → do nothing
        // (We do NOT recompute rate — historical consistency)

        /*
        |--------------------------------------------------------------------------
        | UPDATE DOCUMENT
        |--------------------------------------------------------------------------
        */

        $this->documents->update($document, $validated);

        // Set the month in session based on the document date
        $month = \Carbon\Carbon::parse($document->posting_date)->format('Y-m');
        session(['document_month' => $month]);

        /*
        |--------------------------------------------------------------------------
        | OWNER CHANGE → CLEAN UP INVALID ITEMS
        |--------------------------------------------------------------------------
        */

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

    public function reconcile(ReconciliationService $service)
    {
        $user  = auth()->user();
        $teams = $user->teams;

        $count = 0;

        // ---------------------------------------------------------
        // 1. Query sharing_view ONCE for ALL teams of this user
        // ---------------------------------------------------------
        $allRows = DB::table('sharing_view')
            ->whereIn('team_id', $teams->pluck('id'))
            ->orderBy('team_id')
            ->orderBy('firstday')
            ->orderBy('user_id')
            ->orderBy('category_id')
            ->get();

        if ($allRows->isEmpty()) {
            return redirect()->back()->with('success', "Nothing to reconcile.");
        }

        // ---------------------------------------------------------
        // 2. Group rows by team
        // ---------------------------------------------------------
        $rowsByTeam = $allRows->groupBy('team_id');

        foreach ($teams as $team) {

            // ---------------------------------------------------------
            // 3. Check clearing accounts for all team members
            // ---------------------------------------------------------
            foreach ($team->members as $member) {

                $teamUser = TeamUser::where('team_id', $team->id)
                    ->where('user_id', $member->id)
                    ->first();

                if (!$teamUser || !$teamUser->clearing_account) {
                    return redirect()->route('documents.index')
                        ->with('error', "Team {$team->name}: User {$member->name} has no clearing account configured.");
                }
            }

            // ---------------------------------------------------------
            // 4. Get all rows for this team (already preloaded)
            // ---------------------------------------------------------
            $teamRows = $rowsByTeam->get($team->id);

            if (!$teamRows || $teamRows->isEmpty()) {
                continue; // nothing to reconcile for this team
            }

            // ---------------------------------------------------------
            // 5. Group rows by month (firstday)
            // ---------------------------------------------------------
            $months = $teamRows->groupBy('firstday');

            // ---------------------------------------------------------
            // 6. For each month: reconcile if there are gaps
            // ---------------------------------------------------------
            foreach ($months as $firstdayString => $rowsForMonth) {

                // Check if month has gaps
                $hasGaps = $rowsForMonth->contains(fn($r) => abs((float)$r->gap) >= 0.01);

                if (!$hasGaps) {
                    continue; // skip fully balanced months
                }

                // Run reconciliation (service deletes old wizard docs automatically)
                $service->run($team, $firstdayString, $rowsForMonth);
                $count++;
            }
        }

        return redirect()->back()->with('success', "$count month(s) reconciled.");
    }
}
