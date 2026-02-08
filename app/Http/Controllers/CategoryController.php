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

        $categories = $this->categoryService
            ->getCategoriesForUser($user)
            ->load('team')
            ->sortBy('full_path');

        $users = User::all();

        return view('categories.index', compact('categories', 'users'));
    }

    public function create()
    {
        $user = auth()->user();

        $allCategories = $this->categoryService
            ->getCategoriesForUser($user)
            ->sortBy('full_path');

        return view('categories.create', compact('allCategories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'parent_id'     => 'nullable|exists:categories,id',
            'code'          => 'nullable|string|max:50',
            'is_selectable' => 'nullable|boolean',
            'sort_order'    => 'nullable|integer',
        ]);

        $this->categoryService->createCategory(auth()->user(), $data);

        return redirect()->route('categories.index');
    }

    public function edit(Category $category)
    {
        $this->authorize('update', $category);

        $user = auth()->user();

        // Load parent for the category being edited
        $category->load('parent');

        // Load parents for all categories to avoid N+1 and ensure tree integrity
        $allCategories = $this->categoryService
            ->getCategoriesForUser($user)
            ->load('parent')
            ->where('id', '!=', $category->id)
            ->sortBy('full_path');

        // Load teams the user belongs to
        $teams = $user->teams;

        return view('categories.edit', compact('category', 'allCategories', 'teams'));
    }


    public function update(Request $request, Category $category)
    {
        $this->authorize('update', $category);

        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'parent_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) use ($category) {
                    if ($value == $category->id) {
                        return $fail("A category cannot be its own parent.");
                    }

                    if (in_array($value, $category->allDescendantIds())) {
                        return $fail("A category cannot be assigned to one of its descendants.");
                    }
                }
            ],
            'team_id'       => 'nullable|exists:teams,id',
            'code'          => 'nullable|string|max:50',
            'is_selectable' => 'nullable|boolean',
            'sort_order'    => 'nullable|integer',
        ]);

        $this->categoryService->updateCategory($category, $data);

        return redirect()->route('categories.index');
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        $category->delete();

        return redirect()->route('categories.index');
    }

}
