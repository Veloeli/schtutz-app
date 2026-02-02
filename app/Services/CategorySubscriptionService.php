<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;

class CategorySubscriptionService
{
    public function subscribe(User $user, Category $category): bool
    {
        if ($user->categories()->where('category_id', $category->id)->exists()) {
            return false;
        }

        $user->categories()->attach($category->id);
        return true;
    }

    public function unsubscribe(User $user, Category $category): bool
    {
        return $user->categories()->detach($category->id) > 0;
    }

}
