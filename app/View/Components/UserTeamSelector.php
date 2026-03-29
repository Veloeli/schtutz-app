<?php

namespace App\View\Components;

use App\Models\User;
use Illuminate\View\Component;
use Illuminate\Support\Facades\Log;

class UserTeamSelector extends Component
{
    public function render()
    {
        $user = auth()->user();

        $teams = $user->teamsWithFinancials()->get()->unique('id')->values(); // same user might have several memberships in the same team

        $members = User::whereIn('id', function ($q) use ($teams) {
            $q->select('user_id')
              ->from('team_user')
              ->whereIn('team_id', $teams->pluck('id'))
              ->distinct();
        })->get();

        return view('components.user-team-selector', [
            'teams' => $teams,
            'members' => $members,
        ]);
    }
}
