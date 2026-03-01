<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\VisibilityService;

class Rollup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'code',
        'user_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope('visibility', function (Builder $query) {

            $user = auth()->user();
            if (!$user) {
                return;
            }

            $allowedUsers = VisibilityService::allowedUserIds($user);

            // 1. Find root rollups the user can access
            $rootIds = Rollup::withoutGlobalScopes()
                ->whereNull('parent_id')
                ->where(function ($q) use ($allowedUsers) {
                    $q->whereIn('user_id', $allowedUsers)
                      ->orWhereHas('users', function ($uq) use ($allowedUsers) {
                          $uq->whereIn('users.id', $allowedUsers);
                      });
                })
                ->pluck('id')
                ->toArray();

            // If user has no root access, show nothing
            if (empty($rootIds)) {
                $query->whereRaw('1 = 0');
                return;
            }

            // 2. Collect all descendants of those roots
            $visibleIds = Rollup::descendantIdsOf($rootIds);

            // 3. Apply final visibility filter
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
    | Access / Visibility (pivot)
    |--------------------------------------------------------------------------
    */

    public function users()
    {
        return $this->belongsToMany(User::class, 'rollup_user');
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
