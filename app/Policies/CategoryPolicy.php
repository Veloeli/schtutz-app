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
        // 1. User owns the category
        if ($category->user_id === $user->id) {
            return true;
        }

        // 2. Category has no team → no team permissions apply
        if (! $category->team) {
            return false;
        }

        // 3. User is a member of the team
        return $category->team
            ->members()
            ->where('user_id', $user->id)
            ->exists();
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

}
