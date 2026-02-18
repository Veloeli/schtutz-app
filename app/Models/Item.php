<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Services\VisibilityService;


class Item extends Model
{
    protected $fillable = [
        'name',
        'amount',
        'quantity',
        'category_id',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    protected static function booted()
    {
        static::addGlobalScope('visibleFor', function (Builder $query) {

            $user = auth()->user();
            if (!$user) {
                return;
            }

            $allowedUsers = VisibilityService::allowedUserIds($user);
            $allowedTeams = VisibilityService::allowedTeamIds($user);

            $query->where(function ($q) use ($allowedUsers, $allowedTeams) {

                // A: document owner is allowed
                $q->whereHas('document', function ($q) use ($allowedUsers) {
                    $q->whereIn('owner_id', $allowedUsers);
                });

                // B: OR category team is allowed
                $q->orWhereHas('category', function ($q) use ($allowedTeams) {
                    $q->whereIn('team_id', $allowedTeams);
                });
            });
        });
    }
}
