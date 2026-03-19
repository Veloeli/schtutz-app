<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RollupCategory extends Model
{
    protected $table = 'rollup_category';

    public $timestamps = false;

    protected $fillable = [
        'rollup_id',
        'category_id',
        'is_genuine',
    ];

    protected $casts = [
        'is_genuine' => 'boolean',
    ];

    public function rollup()
    {
        return $this->belongsTo(Rollup::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
