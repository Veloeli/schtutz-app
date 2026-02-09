<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Models\TeamUser;
use Illuminate\Http\Request;

class TeamUserController extends Controller
{
    public function index(Team $team)
    {
        $memberships = $team->memberships()->with('user')->get();

        return view('teams.memberships.index', compact('team', 'memberships'));
    }

    public function create(Team $team)
    {
        return view('teams.memberships.create', compact('team'));
    }

    public function store(Request $request, Team $team)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        // Create membership
        TeamUser::firstOrCreate([
            'team_id' => $team->id,
            'user_id' => $user->id,
        ]);

        return back()->with('success', 'Member added.');
    }

    public function edit(Team $team, TeamUser $membership)
    {
        return view('teams.memberships.edit', compact('team', 'membership'));
    }

    public function update(Request $request, Team $team, TeamUser $membership)
    {
        $validated = $request->validate([
            'sharing_ratio'     => ['nullable', 'numeric', 'min:0', 'max:100'],
            'member_from'       => ['nullable', 'date'],
            'member_to'         => ['nullable', 'date', 'after_or_equal:member_from'],
            'reveal_private'    => ['nullable', 'boolean'],
            'user_apply_date'   => ['nullable', 'date'],
            'team_accept_date'  => ['nullable', 'date'],
            'clearing_account'  => ['nullable', 'string', 'max:255'],
        ]);

        $membership->update($validated);

        return redirect()
            ->route('teams.index')
            ->with('success', 'Member updated successfully.');
    }

    public function destroy(Team $team, TeamUser $membership)
    {
        $membership->delete();

        return redirect()
            ->route('teams.index', $team)
            ->with('success', 'Member removed successfully.');
    }

    public function search(Team $team, Request $request)
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2'],
        ]);

        $query = $request->q;

        $users = User::where('email', 'like', "%{$query}%")
            ->whereNotIn('id', $team->members()->pluck('users.id'))
            ->orderBy('email')
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json($users);
    }
}
