<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeamUser extends Model
{
    use HasFactory;

    protected $table = 'team_user';

    protected $fillable = [
        'team_id',
        'user_id',
        'sharing_ratio',
        'member_from',
        'member_to',
        'reveal_private',
        'clearing_account',
    ];

    protected $casts = [
        'member_from' => 'date',
        'member_to' => 'date',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function clearingAccount()
    {
        return $this->belongsTo(Category::class, 'clearing_account');
    }
    
}
