<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamUser extends Model
{
    protected $table = 'team_user';

    protected $fillable = [
        'team_id',
        'user_id',
        'sharing_ratio',
        'member_from',
        'member_to',
        'reveal_private',
        'user_apply_date',
        'team_accept_date',
        'clearing_account',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
