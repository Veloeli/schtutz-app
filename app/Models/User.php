<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'preferred_root_id',
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
}
