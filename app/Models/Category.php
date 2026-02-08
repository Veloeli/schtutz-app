<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'team_id',
        'parent_id',
        'code',
        'is_selectable',
        'sort_order',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // owner of the category
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // team which uses the category
    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Hierarchy
    |--------------------------------------------------------------------------
    */

    // Parent category
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Children categories
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function isDescendantOf(Category $potentialParent): bool
    {
        // Safety: if the potential parent *is* this category, it's not a descendant
        if ($this->id === $potentialParent->id) {
            return false;
        }

        // Start walking up the tree
        $current = $this->parent;

        // Loop until we reach the root
        while ($current) {

            // Found the parent in the chain
            if ($current->id === $potentialParent->id) {
                return true;
            }

            // Move up one level
            $current = $current->parent;
        }

        // No match found → not a descendant
        return false;
    }

    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    public function allDescendantIds()
    {
        return $this->descendants()->pluck('id')->toArray();
    }

    public function getDepthAttribute()
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    // Full hierarchical path: "Food / Groceries / Vegetables"
    public function getFullPathAttribute()
    {
        $segments = [];
        $node = $this;

        while ($node) {
            $segments[] = $node->name;
            $node = $node->parent;
        }

        return implode(' / ', array_reverse($segments));
    }
}
