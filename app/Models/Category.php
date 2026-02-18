<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Services\VisibilityService;

class Category extends Model
{
    public const TYPES = [
        'EX' => 'Expenses',
        'IN' => 'Income',
        'IC' => 'Capital Income',
        'AL' => 'Asset or Liability',
        'AP' => 'Asset Portfolio',
        'CL' => 'Clearing Account',
    ];

    protected $fillable = [
        'name',
        'user_id',
        'team_id',
        'code',
        'is_selectable',
        'type',
    ];

    protected static function booted()
    {
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
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }
}
