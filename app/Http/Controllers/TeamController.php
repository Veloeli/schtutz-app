<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{

    public function index()
    {
        $user = auth()->user();
        $userId = $user->id;

        // Teams the user owns or belongs to
        $teams = Team::with(['members:id,name,email'])
            ->withCount('members')
            ->withSum('memberships as sharing_ratio_sum', 'sharing_ratio')
            ->where(function ($query) use ($userId) {
                $query->where('owner_id', $userId)
                      ->orWhereHas('members', function ($q) use ($userId) {
                          $q->where('users.id', $userId);
                      });
            })
            ->orderBy('name')
            ->get();

        // Membership rows for the logged-in user
        $memberships = $user->teamMemberships()->with('team')
            ->with([
                'team' => function ($q) {
                    $q->withSum('memberships as sharing_ratio_sum', 'sharing_ratio');
                }
            ])
            ->orderBy('member_from', 'desc')
            ->get();

        return view('teams.index', compact('teams', 'memberships'));
    }

/*    public function index()
    {
        $userId = auth()->id();

        $teams = Team::with(['members:id,name,email'])
            ->withCount('members')
            ->where(function ($query) use ($userId) {
                $query->where('owner_id', $userId)
                      ->orWhereHas('members', function ($q) use ($userId) {
                          $q->where('users.id', $userId);
                      });
            })
            ->orderBy('name')
            ->get();

        return view('teams.index', compact('teams'));
    }
*/
    public function create()
    {
        return view('teams.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
        ]);

        $validated['owner_id'] = auth()->id();

        Team::create($validated);

        return redirect()
            ->route('teams.index')
            ->with('success', 'Team created successfully.');
    }

    public function edit(Team $team)
    {
        return view('teams.edit', compact('team'));
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
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
