<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use App\Services\VisibilityService;
use Illuminate\Auth\Access\Response;

class TeamPolicy
{
    public function update(User $user, Team $team)
    {
        return $user->id === $team->userid 
            || $user->teams->contains($team);
    }

    public function delete(User $user, Team $team)
    {
        return $team->members->count() === 0 && (
            $user->id === $team->user_id 
            || $user->teams->contains($team));
    }
}
