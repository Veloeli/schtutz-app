<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Services\VisibilityService;

class Document extends Model
{
    protected $fillable = [
        'title', 
        'posting_date', 
        'repeat_pattern', 
        'repeat_constant', 
        'owner_id'
    ];

    protected $casts = [
        'posting_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

}
