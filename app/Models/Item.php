<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\VisibilityService;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'amount',
        'quantity',
        'category_id',
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

            // 1) document owner
            $q->whereHas('document', function ($qDoc) use ($allowedUsers) {
                $qDoc->withoutGlobalScopes()
                     ->whereIn('documents.user_id', $allowedUsers);
            })

            // 2) or team category
              ->orWhereHas('category', function ($qCat) use ($teams) {
                  $qCat->whereIn('categories.team_id', $teams);
              });
        });
    });
}

/*    protected static function booted()
    {
        static::addGlobalScope('visibility', function ($query) {
            $user = auth()->user();

            if (!$user) {
                return;
            }

            // Teams the user can see
            $teams = $user->teamsWithFinancials()->pluck('teams.id');

            $allowedUsers = VisibilityService::allowedUserIds($user);

            // Apply visibility through the category relationship
            $query->whereHas('category', function ($q) use ($allowedUsers, $teams) {
                $q->whereIn('team_id', $teams)
                  ->orWhereIn('user_id', $allowedUsers);
            });
        });
    }*/

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

}
