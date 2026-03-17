<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockSplit extends Model
{
    protected $fillable = [
        'old_id',
        'new_id',
        'split_date',
        'split_factor',
    ];

    protected $casts = [
        'split_date' => 'date',
        'split_factor' => 'decimal:6',
    ];

    // -----------------------------------------
    // Relationships
    // -----------------------------------------

    public function oldSecurity(): BelongsTo
    {
        return $this->belongsTo(Security::class, 'old_id');
    }

    public function newSecurity(): BelongsTo
    {
        return $this->belongsTo(Security::class, 'new_id');
    }

    // -----------------------------------------
    // Helpers
    // -----------------------------------------

    public function getDirectionAttribute(): string
    {
        return $this->split_factor > 1
            ? 'forward'
            : 'reverse';
    }
}
