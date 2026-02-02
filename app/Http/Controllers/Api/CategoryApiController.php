<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use App\Services\CategorySubscriptionService;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\User;

class CategoryApiController extends Controller
{
    public function __construct(
        private CategoryService $service,
        private CategorySubscriptionService $subscriptionService
    ) {}

    public function index()
    {
        return response()->json([
            'categories' => $this->service->getCategoriesForUser(auth()->user())
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_private' => 'boolean',
        ]);

        $category = $this->service->createCategory(auth()->user(), $request->all());

        return response()->json(['success' => true, 'category' => $category]);
    }

    public function share(Category $category, Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $targetUser = User::findOrFail($request->user_id);

        // Sharing = forced subscription
        $this->subscriptionService->subscribe($targetUser, $category);

        return response()->json(['success' => true]);
    }

    public function subscribe(Category $category)
    {
        $this->subscriptionService->subscribe(auth()->user(), $category);

        return response()->json(['success' => true]);
    }
}
