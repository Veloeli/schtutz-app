<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{

    public function index()
    {
        $user = auth()->user();

        // Teams where the user is a member
        $memberships = $user->teamMemberships()->with('team')
            ->with([
                'team' => function ($q) {
                    $q->withSum('memberships as sharing_ratio_sum', 'sharing_ratio');
                }
            ])
            ->orderBy('member_from', 'desc')
            ->get();

        // Teams the user owns but is NOT a member of
        $ownedTeamsNotMember = $user->ownedTeams()
            ->whereNotIn('id', $memberships->pluck('team_id'))
            ->with(['members:id,name,email'])
            ->withSum('memberships as sharing_ratio_sum', 'sharing_ratio')
            ->orderBy('name')
            ->get();

        return view('teams.index', [
            'memberships' => $memberships,
            'ownedTeamsNotMember' => $ownedTeamsNotMember,
        ]);
    }

    public function create()
    {
        return view('teams.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
        ]);

        $validated['user_id'] = auth()->id();

        Team::create($validated);

        return redirect()
            ->route('teams.index')
            ->with('success', 'Team created successfully.');
    }

    public function edit(Team $team)
    {
        $team->load([
            'members',
            'memberships',
            'memberships.user',
        ])->loadSum('memberships as sharing_ratio_sum', 'sharing_ratio');

        return view('teams.edit', compact('team'));
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'has_common_financials' => 'required|boolean',
            'has_common_reporting' => 'required|boolean',
            'has_common_securities' => 'required|boolean',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
        ]);

        $team->update($validated);

        return redirect()
            ->route('teams.index')
            ->with('success', 'Team updated successfully.');
    }

    public function destroy(Team $team)
    {
        $team->delete();

        return redirect()
            ->route('teams.index')
            ->with('success', 'Team deleted successfully.');
    }
}
