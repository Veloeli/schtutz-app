<?php

namespace App\Http\Controllers;

use App\Models\Rollup;
use App\Models\Category;
use App\Models\User;
use App\Services\RollupService;
use Illuminate\Http\Request;

class RollupController extends Controller
{
    public function __construct(
        protected RollupService $service
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $rootRollups = Rollup::whereNull('parent_id')->get();
        $selectedRoot = null;

        // 1. URL root_id hat höchste Priorität
        if ($request->filled('root_id')) {
            $selectedRoot = $rootRollups->firstWhere('id', $request->root_id);

            if ($selectedRoot) {
                // Session aktualisieren
                $request->session()->put('root_id', $selectedRoot->id);
            }
        }

        // 2. Falls keine URL, aber Session vorhanden
        if (!isset($selectedRoot) && $request->session()->has('root_id')) {
            $selectedRoot = $rootRollups->firstWhere('id', $request->session()->get('root_id'));
        }

        // 3. Falls keine Session, aber User‑Preference
        if (!isset($selectedRoot) && $user->preferred_root_id) {
            $selectedRoot = $rootRollups->firstWhere('id', $user->preferred_root_id);
            if ($selectedRoot) {
                $request->session()->put('root_id', $selectedRoot->id);
            }
        }

        // 4. Fallback: erster Root
        if (!isset($selectedRoot) && $rootRollups->isNotEmpty()) {
            $selectedRoot = $rootRollups->first();
            $request->session()->put('root_id', $selectedRoot->id);
        }

        return view('rollups.index', compact('rootRollups', 'selectedRoot'));
    }

    /*
    |--------------------------------------------------------------------------
    | Root creation
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $parent = null;

        if ($request->filled('parent_id')) {
            $parent = Rollup::findOrFail($request->parent_id);
        }

        return view('rollups.create', compact('parent'));
    }

    public function storeRoot(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'code' => 'nullable|string',
        ]);

        $rollup = $this->service->createRoot($data, $request->user());

        return redirect()
            ->route('rollups.index', ['root_id' => $rollup->id])
            ->with('success', 'Rollup created.');
    }

    /*
    |--------------------------------------------------------------------------
    | Child creation
    |--------------------------------------------------------------------------
    */
    public function storeChild(Request $request, Rollup $parent)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'code' => 'nullable|string',
        ]);

        $this->service->createChild($parent, $data, $request->user());

        return redirect()
            ->route('rollups.index')
            ->with('success', 'Child rollup created.');
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */
    public function edit(Rollup $rollup)
    {
        $assignedCategories = $rollup->categories()->get();
        
        // 1. Find the root
        $root = Rollup::rootOf($rollup);

        // Assigned users (from pivot rollup_user) 
        $assignedUsers = $root->users()->get();

        // 2. Get all descendant IDs including root
        $allRollupIds = Rollup::descendantIdsOf([$root]);

        // 3. Categories already assigned anywhere in this tree
        $assignedInTree = Category::whereHas('rollups', function ($q) use ($allRollupIds) {
            $q->whereIn('rollups.id', $allRollupIds);
        })->pluck('id');

        // 4. Categories not yet assigned
        $availableCategories = Category::whereNotIn('id', $assignedInTree)
            ->orderBy('code')
            ->get();

        return view('rollups.edit', compact('rollup', 'assignedCategories', 'availableCategories', 'assignedUsers'));
    }

    public function update(Request $request, Rollup $rollup)
    {
        $data = $request->validate([
            'name'    => 'required|string',
            'code'    => 'nullable|string',
            'user_id' => ['required', 'exists:users,id'], 
        ]);

        $this->service->update($rollup, $data);

        $rootId = session('rollups.root_id');

        return redirect()
            ->route('rollups.index', ['root_id' => $rootId])
            ->with('success', 'Rollup updated.');
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */
    public function destroy(Rollup $rollup)
    {
        $rootId = session('rollups.root_id');

        if ($rootId == $rollup->id) {
            $rootId = $rollup->parent_id;
        }

        $this->service->delete($rollup);

        return redirect()
            ->route('rollups.index', ['root_id' => $rootId])
            ->with('success', 'Rollup deleted.');
    }

    /*
    |--------------------------------------------------------------------------
    | Attach user (root only)
    |--------------------------------------------------------------------------
    */
    public function attachUser(Request $request)
    {
        $request->validate([
            'rollup_id' => 'required|exists:rollups,id',
            'email' => 'required|email'
        ]);

        $rollup = Rollup::findOrFail($request->rollup_id);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'User not found.');
        }

        // Avoid duplicates
        if ($rollup->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'User already assigned.');
        }

        $rollup->users()->attach($user->id);

        return back()->with('success', 'User added.');
    }

    public function detachUser(Rollup $rollup, User $user)
    {
        $this->authorize('detachUser', [$rollup, $user]);

        $rollup->users()->detach($user->id);

        return back();
    }

    /*
    |--------------------------------------------------------------------------
    | Attach category (leaf only)
    |--------------------------------------------------------------------------
    */
    public function attachCategory(Rollup $rollup, Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
        ]);

        $rollup->categories()->attach($request->category_id);

        return back();
    }

    public function detachCategory(Rollup $rollup, Category $category)
    {
        $rollup->categories()->detach($category->id);

        return back();
    }
}
