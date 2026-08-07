<?php

namespace App\View\Components;

use App\Models\User;
use Illuminate\View\Component;
use App\Services\VisibilityService;

class UserTeamSelector extends Component
{
    public function render()
    {
        $user = auth()->user();

        // Use the SAME visibility rules as everywhere else
        $allowedTeamIds = VisibilityService::allowedTeamIds($user);
        $allowedUserIds = VisibilityService::allowedUserIds($user);

        // Teams the user can see
        $teams = $user->teams()
            ->whereIn('teams.id', $allowedTeamIds)
            ->get()
            ->unique('id')
            ->values();

        // Members the user can see (including themselves)
        $members = User::whereIn('id', $allowedUserIds)->get();

        return view('components.user-team-selector', [
            'teams' => $teams,
            'members' => $members,
        ]);
    }
}
