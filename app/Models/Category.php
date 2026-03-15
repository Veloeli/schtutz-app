<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\VisibilityService;

class Category extends Model
{
    use HasFactory;
    
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
                $q->whereIn('categories.user_id', $allowedUsers)
                  ->orWhereIn('categories.team_id', $allowedTeams);
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

    public function rollups()
    {
        return $this->belongsToMany(Rollup::class, 'rollup_category');
    }

    public function teamUsers()
    {
        return $this->hasMany(TeamUser::class, 'clearing_account');
    }

    /**
     * Return the human‑readable label for the type.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? '';
    }

    // getSourceLabelAttribute() becomes source_label automatically
    public function getSourceLabelAttribute()
    {
        if ($this->team_id) {
            return $this->team?->name;
        }

        if ($this->user_id === auth()->id()) {
            return null;
        }

        return $this->owner?->name;
    }
}
