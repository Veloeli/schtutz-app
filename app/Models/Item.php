<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
