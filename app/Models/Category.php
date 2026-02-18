<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Services\VisibilityService;

class Category extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'team_id',
        'code',
        'is_selectable',
    ];

    protected static function booted()
    {
logger()->info('Category.booted', []);
        static::addGlobalScope('visibility', function ($query) {
            $user = auth()->user();

            if (!$user) {
                return;
            }

            $allowedUsers = \App\Services\VisibilityService::allowedUserIds($user);
            $allowedTeams = \App\Services\VisibilityService::allowedTeamIds($user);

            $query->where(function ($q) use ($allowedUsers, $allowedTeams) {
                $q->whereIn('user_id', $allowedUsers)
                  ->orWhereIn('team_id', $allowedTeams);
            });
\Log::info('Category.scope EXECUTED');
        });
    }

    public function owner()
    {
logger()->info('Category.owner', []);
        return $this->belongsTo(User::class, 'user_id');
    }

    public function team()
    {
logger()->info('Category.team', []);
        return $this->belongsTo(Team::class, 'team_id');
    }
}
