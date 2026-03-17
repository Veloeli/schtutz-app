<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable = [
        'security_id',
        'quote_date',
        'price',
    ];

    protected $casts = [
        'quote_date' => 'date',
        'price' => 'decimal:6',
    ];

    protected static function booted()
    {
        static::addGlobalScope('visibility', function ($query) {
            $user = auth()->user();

            if (!$user) {
                return;
            }

            // Teams the user can see
            $teams = $user->teamsWithSecurities()->pluck('teams.id');

            // Apply visibility through the security relationship
            $query->whereHas('security', function ($q) use ($user, $teams) {
                $q->whereIn('team_id', $teams)
                  ->orWhere('user_id', $user->id);
            });
        });
    }

    public function security()
    {
        return $this->belongsTo(Security::class);
    }
}
