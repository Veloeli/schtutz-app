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

    public function index()
    {
        $user = auth()->user();

        // Visibility is enforced by the Category global scope
        $categories = $this->categoryService->allVisible($user)
            ->sortBy('name');

        $users = User::all();

        return view('categories.index', compact('categories', 'users'));
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
        ]);

        $this->categoryService->create(auth()->user(), $data);

        return redirect()->route('categories.index');
    }

    public function edit(Category $category)
    {
        $this->authorize('update', $category);

        $user = auth()->user();

        $teams = $user->teams;

        return view('categories.edit', compact('category', 'teams'));
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('update', $category);

        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'team_id'       => 'nullable|exists:teams,id',
            'code'          => 'nullable|string|max:50',
            'is_selectable' => 'nullable|boolean',
        ]);

        $this->categoryService->update($category, $data);

        return redirect()->route('categories.index');
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        $category->delete();

        return redirect()->route('categories.index');
    }
}
