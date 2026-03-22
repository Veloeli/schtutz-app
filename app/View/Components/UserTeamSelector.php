<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\User;

class UserTeamSelector extends Component
{
    public $teams;
    public $members;
    public $filter;

    public function __construct()
    {
        $user = auth()->user();

        // 1. Fetch teams
        $teams = $user->teamsWithFinancials()->get();

        // 2. Fetch all members belonging to those teams
        $members = User::whereIn('id', function ($q) use ($teams) {
            $q->select('user_id')
              ->from('team_user')
              ->whereIn('team_id', $teams->pluck('id'))
              ->distinct();
        })->get();

        // Assign to public properties so Blade can use them
        $this->teams = $teams;
        $this->members = $members;
    }

    public function render()
    {
        return view('components.user-team-selector');
    }
}
