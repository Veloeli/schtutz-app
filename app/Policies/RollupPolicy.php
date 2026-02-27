<?php

namespace App\Policies;

use App\Models\Rollup;
use App\Models\User;
use App\Services\VisibilityService;
use Illuminate\Auth\Access\Response;

class RollupPolicy
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
    public function view(User $user, Rollup $rollup): bool
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

    public function update(User $user, Rollup $rollup)
    {
        return ($user->id === $rollup->user_id || Rollup::rootOf($rollup)->users->contains($user)) ;
   }

    public function delete(User $user, Rollup $rollup)
    {
        return ($user->id === $rollup->user_id || Rollup::rootOf($rollup)->users->contains($user)) &&
            Rollup::rootOf($rollup)->users->count() === 1 &&
            $rollup->children->count() === 0 && 
            $rollup->categories()->withoutGlobalScopes()->count() === 0;
    }

    public function detachUser(User $actingUser, Rollup $rollup, User $targetUser)
    {
        return !($rollup->user_id === $targetUser->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Rollup $rollup): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Rollup $rollup): bool
    {
        return false;
    }

}
