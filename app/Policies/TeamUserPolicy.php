<?php

namespace App\Policies;

use App\Models\TeamUser;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TeamUserPolicy
{
    public function update(User $user, TeamUser $teamUser): bool
    {
        return $teamUser->user_id === $user->id;
    }
}
