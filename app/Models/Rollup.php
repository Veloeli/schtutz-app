<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rollup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'code',
        'user_id',
        'team_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope('visibility', function (Builder $query) {

            $user = auth()->user();
            if (!$user) {
                return;
            }

            $teamIds = $user->teamsWithReporting()->pluck('teams.id');
           
            // 1. Find visible root rollups
            $rootIds = Rollup::withoutGlobalScopes()
                ->whereNull('parent_id')
                ->where(function ($q) use ($user, $teamIds) {
                    $q->whereIn('team_id', $teamIds)
                      ->orWhere(function ($q2) use ($user) {
                          $q2->whereNull('team_id')
                             ->where('user_id', $user->id);
                      });
                })
                ->pluck('id')
                ->toArray();

            if (empty($rootIds)) {
                $query->whereRaw('1 = 0');
                return;
            }

            // 2. Collect all descendants of visible roots
            $visibleIds = Rollup::descendantIdsOf($rootIds);

            // 3. Apply final filter
            $query->whereIn('id', $visibleIds);
        });
    }

    public static function rootOf(Rollup $node): Rollup
    {
        while ($node->parent_id !== null) {
            $node = $node->parent;
        }
        return $node;
    }

    public static function descendantIdsOf(array $roots): array
    {
        // Normalize: convert Rollup models to IDs
        $queue = [];
        foreach ($roots as $r) {
            $queue[] = $r instanceof Rollup ? $r->id : $r;
        }

        $all = $queue;

        while (!empty($queue)) {
            $children = Rollup::withoutGlobalScopes()
                ->whereIn('parent_id', $queue)
                ->pluck('id')
                ->all();

            $all = array_merge($all, $children);
            $queue = $children;
        }

        return array_values(array_unique($all));
    }

    /*
    |--------------------------------------------------------------------------
    | Hierarchy
    |--------------------------------------------------------------------------
    */

    public function parent()
    {
        return $this->belongsTo(Rollup::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Rollup::class, 'parent_id')->orderBy('code');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    /*
    |--------------------------------------------------------------------------
    | Access / Visibility
    |--------------------------------------------------------------------------
    */

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function users()
    {
        return $this->hasManyThrough(
            User::class,
            TeamUser::class,
            'team_id',   // team_user.team_id
            'id',        // users.id
            'team_id',   // rollups.team_id
            'user_id'    // team_user.user_id
        );
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeVisibleTo($query, User $user)
    {
        return $query->whereIn('team_id', $user->teams->pluck('id'));
    }

    /*
    |--------------------------------------------------------------------------
    | Category Mapping
    |--------------------------------------------------------------------------
    */

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'rollup_category');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

    public function isInternal(): bool
    {
        return !$this->isRoot() && !$this->isLeaf();
    }
}
