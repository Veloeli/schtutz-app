<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\VisibilityService;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'posting_date', 
        'repeat_pattern', 
        'repeat_constant', 
        'user_id'
    ];

    protected $casts = [
        'posting_date' => 'date',
    ];

    protected static function booted()
    {
        static::addGlobalScope('visibility', function ($query) {
            $user = auth()->user();

            if (!$user) {
                return;
            }

            $teams = $user->teamsWithFinancials()->pluck('teams.id');
            $allowedUsers = VisibilityService::allowedUserIds($user);

            $query->where(function ($q) use ($allowedUsers, $teams) {

                // 1) User owns the document
                $q->whereIn('documents.user_id', $allowedUsers)

                // 2) OR the document has at least one visible item
                  ->orWhereHas('items', function ($q2) use ($allowedUsers, $teams) {
                      $q2->whereHas('category', function ($q3) use ($allowedUsers, $teams) {
                          $q3->whereIn('categories.team_id', $teams)
                             ->orWhereIn('categories.user_id', $allowedUsers);
                      });
                  });
            });
        });
    }

/*            // Apply visibility through the category relationship
            $query->where(function ($q) use  ($user) {
                $q->whereIn('user_id', $user)
                  ->orWhereHas('items.category'); // category global scope applies
            });*/

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
