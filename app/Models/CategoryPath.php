<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryPath extends Model
{
    // This model maps to a database VIEW
    protected $table = 'category_paths_view';

    // Views do not have auto-incrementing primary keys
    public $incrementing = false;

    // Views do not have timestamps
    public $timestamps = false;

    // If your view has a natural unique key, define it here.
    // The combination (rollup_id, category_id, src) is usually unique.
    protected $primaryKey = null;

    // Allow mass assignment (view is read-only anyway)
    protected $guarded = [];

    protected static function booted()
    {
        static::addGlobalScope('visibility', function ($query) {
            $user = auth()->user();

            // No user (CLI, queue, tinker, etc.) → do nothing
            if (!$user) {
                return;
            }

            // Teams the user can see
            $teams = $user->teamsWithFinancials();
            $members = User::inRevealsTo($teams)->get();

            // Apply visibility rules
            $query->where(function ($q) use ($teams, $members) {
                $q->whereIn('team_id', $teams->pluck('teams.id'))   // team-based visibility
                  ->orWhereIn('user_id', $members->pluck('id')); // personal/private categories
            });
        });
    }

    /**
     * Make the model read-only by preventing save/update/delete.
     */
    public function save(array $options = [])
    {
        throw new \LogicException('Cannot save to a read-only database view: category_paths_view');
    }

    public function delete()
    {
        throw new \LogicException('Cannot delete from a read-only database view: category_paths_view');
    }

    public function update(array $attributes = [], array $options = [])
    {
        throw new \LogicException('Cannot update a read-only database view: category_paths_view');
    }

    /**
     * Optional: relationships back to Rollup and Category
     */
    public function rollup()
    {
        return $this->belongsTo(Rollup::class, 'rollup_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'category_id', 'category_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class)->withDefault();
    }
}
