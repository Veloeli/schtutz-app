<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\VisibilityService;

class Category extends Model
{
    use HasFactory;
    
    public const BSTYPES = [
        'AL' => 'Asset or Liability',
        'AP' => 'Asset Portfolio',
        'CL' => 'Clearing Account',
    ];

    public const PLTYPES = [
        'EX' => 'Expenses',
        'IN' => 'Income',
        'IC' => 'Capital Income',
    ];

    public const TYPES = [...self::BSTYPES, ...self::PLTYPES];

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
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

        return $this->user?->name;
    }
}
