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
            $parent = Rollup::withoutGlobalScopes()->findOrFail($request->parent_id);
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

        $this->service->createChild($parent, $data);

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
        $rootId = session('rollups.root_id');

        $children = $rollup->children()->get();

        return view('rollups.edit', compact('rollup', 'children', 'rootId'));
    }

    public function update(Request $request, Rollup $rollup)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'code' => 'nullable|string',
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
    public function attachUser(Request $request, Rollup $rollup)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $this->service->attachUser($rollup, User::find($data['user_id']));

        return redirect()->back()->with('success', 'User added to rollup.');
    }

    /*
    |--------------------------------------------------------------------------
    | Attach category (leaf only)
    |--------------------------------------------------------------------------
    */
    public function attachCategory(Request $request, Rollup $rollup)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
        ]);

        $this->service->attachCategory($rollup, Category::find($data['category_id']));

        return redirect()->back()->with('success', 'Category added to rollup.');
    }
}
