<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable = [
        'security_id',
        'quote_date',
        'price',
    ];

    protected $casts = [
        'quote_date' => 'date',
        'price' => 'decimal:6',
    ];

    public function security()
    {
        return $this->belongsTo(Security::class);
    }
}
