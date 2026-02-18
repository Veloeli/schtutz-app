<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Services\VisibilityService;

class Document extends Model
{
    protected $fillable = ['title', 'posting_date', 'repeat_pattern', 'repeat_constant', 'owner_id'];

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    protected static function booted()
    {
        static::addGlobalScope('visibility', function ($query) {
            $user = auth()->user();

            if (!$user) {
                return;
            }

            $allowedUsers = \App\Services\VisibilityService::allowedUserIds($user);
            $allowedTeams = \App\Services\VisibilityService::allowedTeamIds($user);

            $query->where(function ($q) use ($allowedUsers, $allowedTeams) {

                // A: documents owned by allowed users
                $q->whereIn('owner_id', $allowedUsers);

                // B: documents that contain items in categories belonging to allowed teams
                $q->orWhereIn('id', function ($sub) use ($allowedTeams) {
                    $sub->select('items.document_id')
                        ->from('items')
                        ->join('categories', 'categories.id', '=', 'items.category_id')
                        ->whereIn('categories.team_id', $allowedTeams);
                });
            });
        });
    }
}
