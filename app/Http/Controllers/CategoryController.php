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

        // Use service to fetch categories relevant to the user
        $categories = $this->categoryService->getCategoriesForUser($user);

        // Needed for the Share modal
        $users = User::all();

        return view('categories.index', compact('categories', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'is_private' => 'nullable|boolean',
        ]);

        $this->categoryService->createCategory(auth()->user(), $data);

        return redirect()->route('categories.index');
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('update', $category);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'is_private' => 'nullable|boolean',
        ]);

        $data['is_private'] = isset($data['is_private']) ? 1 : 0;

        $category->update($data);

        return back();
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        $category->delete();

        return back();
    }

    public function discover()
    {
        $user = auth()->user();

        $categories = Category::with('owner')   // eager-load owner
            ->where('is_private', 0)            // public categories
            ->where('user_id', '!=', $user->id) // not owned by user
            ->whereDoesntHave('subscribers', function ($q) use ($user) {
                $q->where('user_id', $user->id); // not subscribed/shared
            })
            ->get();

        return view('categories.discover', compact('categories'));
    }

}
