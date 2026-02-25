<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'owner_id'];

    protected static function booted()
    {
        static::created(function (Team $team) {
            // Automatically add the owner as a member
            $team->members()->attach($team->owner_id);
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }  

    public function memberships()
    {
        return $this->hasMany(TeamUser::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot('id', 'reveal_private', 'sharing_ratio', 'clearing_account', 'member_from', 'member_to')
            ->withTimestamps();
    }
}
