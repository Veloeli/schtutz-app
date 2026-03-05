<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Models\TeamUser;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

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

        DB::transaction(function () use ($team, $user) {
            // Create membership if not exists
            TeamUser::firstOrCreate([
                'team_id' => $team->id,
                'user_id' => $user->id,
            ]);

            // Reset reveal_private for ALL members of this team
            TeamUser::where('team_id', $team->id)
                ->update(['reveal_private' => 0]);
        });

        return back()->with('success', 'Member added.');
    }
    
    public function edit(Team $team, TeamUser $membership)
    {
        $clearingAccounts = Category::where('type', 'CL')
            ->whereNull('team_id')
            ->orderBy('code')
            ->get();

        return view('teams.memberships.edit', [
            'membership' => $membership,
            'team' => $team,
            'clearingAccounts' => $clearingAccounts,
        ]);
    }

    public function update(Request $request, Team $team, TeamUser $membership)
    {
        $requiresFinancials = $team->has_common_financials;

        $validated = $request->validate([
            'sharing_ratio' => [
                $requiresFinancials ? 'required' : 'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'reveal_private' => [
                $requiresFinancials ? 'required' : 'nullable',
                'boolean',
            ],

            'clearing_account' => [
                $requiresFinancials ? 'required' : 'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(fn($q) =>
                    $q->where('type', 'CL')->whereNull('team_id')
                ),
            ],

            'member_from' => ['nullable', 'date'],
            'member_to'   => ['nullable', 'date', 'after_or_equal:member_from'],
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
            ->route('teams.index')
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
