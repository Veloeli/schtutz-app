<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'team_id',
        'name',
        'date_from',
        'date_to',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to'   => 'date',
    ];

    protected static function booted()
    {
        static::addGlobalScope('visibility', function ($query) {
            $user = auth()->user();

            // No user (CLI, queue, tinker, etc.) → do nothing
            if (!$user) {
                return;
            }

            // Collections the user can see
            $teams = $user->teamsWithFinancials()->pluck('teams.id');

            // Apply visibility rules
            $query->where(function ($q) use ($user, $teams) {
                $q->whereIn('collections.team_id', $teams)   // team-based visibility
                  ->orWhere('collections.user_id', $user->id); // personal/private securities
            });
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'collection_item')
            ->withPivot('change_sign')
            ->withTimestamps();
    }

    public function documents()
    {
        return $this->hasManyThrough(
            Document::class,
            Item::class,
            'id',            // Item.id
            'id',            // Document.id
            null,            // collection.id (local key)
            'document_id'    // Item.document_id (foreign key)
        )->distinct();
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors / Helpers
    |--------------------------------------------------------------------------
    */

    public function getIsTeamListAttribute()
    {
        return ! is_null($this->team_id);
    }

    public function getDurationAttribute()
    {
        return $this->date_from->toDateString()
            . ' → '
            . ($this->date_to?->toDateString() ?? 'open');
    }
}
