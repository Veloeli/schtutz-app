<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'is_private', 'user_id'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subscribers()
    {
        return $this->belongsToMany(User::class);
    }

    public function isSubscribedBy(User $user): bool
    {
        return $this->subscribers()->where('user_id', $user->id)->exists();
    }
}