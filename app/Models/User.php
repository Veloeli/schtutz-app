<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Http\Request;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $attributes = [
        'freeze_after' => 3,
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'preferred_root_id',
        'freeze_after',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function ownedCategories()
    {
        return $this->hasMany(Category::class, 'user_id');
    }

    public function teamCategories()
    {
        return Category::whereIn('team_id', $this->teams->pluck('id'));
    }

    /**
     * Full membership records (TeamUser model).
     */
    public function teamMemberships()
    {
        return $this->hasMany(TeamUser::class);
    }

    public function ownedTeams()
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    /**
     * Convenience: list of teams the user belongs to.
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot(['reveal_private','sharing_ratio'])
            ->withTimestamps();
    }

    public function teamsWithFinancials()
    {
        return $this->teams()->where('teams.has_common_financials', true);
    }

    public function teamsWithReporting()
    {
        return $this->teams()->where('teams.has_common_reporting', true);
    }

    public function teamsWithSecurities()
    {
        return $this->teams()->where('teams.has_common_securities', true);
    }

    public function activeTeams()
    {
        $today = now();

        return $this->belongsToMany(Team::class, 'team_user')
            ->where(function ($q) use ($today) {
                $q->whereNull('member_from')
                  ->orWhere('member_from', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('member_to')
                  ->orWhere('member_to', '>=', $today);
            });
    }

    public function preferredRoot()
    {
        return $this->belongsTo(Rollup::class, 'preferred_root_id');
    }

    public function resolveActiveRoot(Request $request)
    {
        $rootRollups = Rollup::whereNull('parent_id')->get();
        $selectedRoot = null;

        // 1. URL parameter
        if ($request->filled('root_id')) {
            $selectedRoot = $rootRollups->firstWhere('id', $request->root_id);
            if ($selectedRoot) {
                $request->session()->put('root_id', $selectedRoot->id);
            }
        }

        // 2. Session
        if (!$selectedRoot && $request->session()->has('root_id')) {
            $selectedRoot = $rootRollups->firstWhere('id', $request->session()->get('root_id'));
        }

        // 3. User preference
        if (!$selectedRoot && $this->preferred_root_id) {
            $selectedRoot = $rootRollups->firstWhere('id', $this->preferred_root_id);
            if ($selectedRoot) {
                $request->session()->put('root_id', $selectedRoot->id);
            }
        }

        // 4. Fallback
        if (!$selectedRoot && $rootRollups->isNotEmpty()) {
            $selectedRoot = $rootRollups->first();
            $request->session()->put('root_id', $selectedRoot->id);
        }

        return $selectedRoot;
    }

    /**
     * Usage:
     * $teams = $user->teamsWithSecurities()->get();
     * $members = User::inTeams($teams)->get();
     * $members = User::inRevealsTo($teams)->get();
     */
    public function scopeInTeams($query, $teams)
    {
        return $query->whereIn('id', function ($q) use ($teams) {
            $q->select('user_id')
              ->from('team_user')
              ->whereIn('team_id', $teams->pluck('team_user.id'))
              ->distinct();
        });
    }

    public function scopeInRevealsTo($query, $teams)
    {
        return $query->whereIn('id', function ($q) use ($teams) {
            $q->select('user_id')
              ->from('team_user')
              ->whereIn('team_id', $teams->pluck('team_user.id'))
              ->where('reveal_private', 1)
              ->distinct();
        });
    }
}
