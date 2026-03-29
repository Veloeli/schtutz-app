<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\User;
use App\Services\CategoryService;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        // Store filter if provided
        if ($request->filled('teamfilter')) {
            session(['category_teamfilter' => $request->teamfilter]);
        }

        // Retrieve stored values (fallbacks if none stored)
        $teamfilter = session('category_teamfilter', 'all');

        $user = auth()->user();

        // Visibility is enforced by the Category global scope
        $categories = $this->categoryService->visibleFor($user, $teamfilter)
            ->sortBy(fn($cat) => $cat->code . ' ' . $cat->name, SORT_STRING)
            ->values();

        $teams = $user->teamsWithFinancials()->get();

        $members = User::whereIn('id', function ($q) use ($teams) {
            $q->select('user_id')
              ->from('team_user')
              ->whereIn('team_id', $teams->pluck('id'))
              ->distinct();
        })->get();
        
        return view('categories.index', [
            'categories' => $categories,
            'teamfilter' => $teamfilter,
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        return view('categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'team_id'       => 'nullable|exists:teams,id',
            'code'          => 'nullable|string|max:50',
            'is_selectable' => 'nullable|boolean',
            'type'          => 'required|in:EX,IN,IC,AL,AP,CL',
        ]);

        $this->categoryService->create(auth()->user(), $data);

        return redirect()->route('categories.index');
    }

    public function edit(Category $category)
    {
        $this->authorize('update', $category);

        $user = auth()->user();

        $teams = $user->teamsWithFinancials;

        return view('categories.edit', compact('category', 'teams'));
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('update', $category);

        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'team_id'       => 'nullable|exists:teams,id',
            'code'          => 'nullable|string|max:50',
            'is_selectable' => 'required|boolean',
            'type'          => 'required|in:EX,IN,IC,AL,AP,CL',
        ]);

        $this->categoryService->update($category, $data);

        return redirect()->route('categories.index');
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        $this->categoryService->delete($category);

        return redirect()->route('categories.index');
    }
}
