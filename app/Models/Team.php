<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_id',
        'has_common_financials',
        'has_common_reporting',
        'has_common_securities',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

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
            ->withPivot('reveal_private', 'sharing_ratio', 'clearing_account', 'member_from', 'member_to')
            ->withTimestamps();
    }

    public function revealedMembers()
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot('reveal_private', 'sharing_ratio', 'clearing_account', 'member_from', 'member_to')
            ->wherePivot('reveal_private', 1);
    }

    public function rollups()
    {
        return $this->hasMany(Rollup::class);
    }

}
