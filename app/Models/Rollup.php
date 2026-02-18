<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Services\VisibilityService;

class Rollup extends Model
{
    protected $table = 'rollup';

    protected $fillable = [
        'name',
        'parent_id',
        'code',
        'user_id',   // informational creator
    ];

    protected static function booted()
    {
        static::addGlobalScope('visibility', function ($query) {
            $user = auth()->user();

            if (!$user) {
                return; // no filtering for guests
            }

            $allowedUsers = \App\Services\VisibilityService::allowedUserIds($user);

            $query->where(function ($q) use ($allowedUsers) {

                // 1. Rollups the user created
                $q->whereIn('user_id', $allowedUsers)

                // 2. Rollups assigned via pivot
                  ->orWhereHas('users', function ($uq) use ($allowedUsers) {
                        $uq->whereIn('users.id', $allowedUsers);
                  });
            });
        });
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
    | Category Mapping (only for leaf nodes)
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
