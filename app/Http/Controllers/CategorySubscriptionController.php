<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use App\Services\CategorySubscriptionService;

class CategorySubscriptionController extends Controller
{
    protected CategorySubscriptionService $service;

    public function __construct(CategorySubscriptionService $service)
    {
        $this->service = $service;
    }

    public function subscribe(Category $category)
    {
        $this->authorize('subscribe', $category);

        $user = auth()->user();

        $subscribed = $this->service->subscribe($user, $category);

        return redirect()
            ->back()
            ->with('success', $subscribed
                ? 'User subscribed to category.'
                : 'User was already subscribed.');
    }

    public function forceSubscribe(Category $category)
    {
        $user = User::findOrFail(request('user_id'));

        $subscribed = $this->service->subscribe($user, $category);

        return redirect()
            ->back()
            ->with('success', $subscribed
                ? 'User subscribed to category.'
                : 'User was already subscribed.');
    }

    public function unsubscribe(Category $category)
    {
        $user = auth()->user();

        $unsubscribed = $this->service->unsubscribe($user, $category);

        return redirect()
            ->back()
            ->with('success', $unsubscribed
                ? 'User unsubscribed from category.'
                : 'User was not subscribed.');
    }

}
