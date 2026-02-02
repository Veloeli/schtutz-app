<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Category $category): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Category $category)
    {
        return $category->user_id === $user->id;
    }

    public function delete(User $user, Category $category)
    {
        return $category->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Category $category): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Category $category): bool
    {
        return false;
    }

    public function share(User $user, Category $category)
    {
        return $category->is_private && $category->user_id === $user->id;
    }

    public function subscribe(User $user, Category $category)
    {
        return !$category->is_private;
    }
    
    public function unsubscribe(User $user, Category $category)
    {
        // User can unsubscribe if they are currently subscribed
        return $category->subscribers()->where('user_id', $user->id)->exists();
    }
}
