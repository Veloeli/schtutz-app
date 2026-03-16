<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Security extends Model
{
    use HasFactory;

    public const ASSET_CLASSES = [
        'SH' => 'Share',
        'OP' => 'Option',
        'CM' => 'Commodity',
        'FX' => 'Currency',
        'CS' => 'Cooperative Share',
        'BD' => 'Bond',
        'IX' => 'Index',
        'RE' => 'Real Estate',
    ];

    protected $fillable = [
        'user_id',
        'team_id',
        'name',
        'isin',
        'ticker',
        'identifier',
        'company',
        'asset_class',
        'currency_id',
        'is_in_use',
        'is_tracked',
        'is_hedged',
        'region',
        'sector',
        'strategy',
        'theme',
        'option_type',
        'option_underlying_id',
        'option_strike',
        'option_multiplier',
        'option_calculate',
    ];

    protected $casts = [
        'is_in_use'         => 'boolean',
        'is_tracked'        => 'boolean',
        'is_hedged'         => 'boolean',
        'option_calculate'  => 'boolean',
        'option_strike'     => 'decimal:6',
        'option_multiplier' => 'decimal:6',
    ];

    protected static function booted()
    {
        static::addGlobalScope('visibility', function ($query) {
            $user = auth()->user();

            // No user (CLI, queue, tinker, etc.) → do nothing
            if (!$user) {
                return;
            }

            // Teams the user can see
            $teams = $user->teamsWithSecurities()->pluck('teams.id');

            // Apply visibility rules
            $query->where(function ($q) use ($user, $teams) {
                $q->whereIn('team_id', $teams)   // team-based visibility
                  ->orWhere('user_id', $user->id); // personal/private securities
            });
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * Currency relationship (self‑referencing)
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Security::class, 'currency_id');
    }

    /**
     * Option underlying (self‑referencing)
     */
    public function optionUnderlying(): BelongsTo
    {
        return $this->belongsTo(Security::class, 'option_underlying_id');
    }

    public function splitsFrom(): HasMany
    {
        return $this->hasMany(StockSplit::class, 'old_id');
    }

    public function splitsTo(): HasMany
    {
        return $this->hasMany(StockSplit::class, 'new_id');
    }

    /**
     * Return the human‑readable label for the asset class.
     */
    public function getAssetClassLabelAttribute(): string
    {
        return self::ASSET_CLASSES[$this->asset_class] ?? '';
    }

    // getSourceLabelAttribute() becomes source_label automatically
    public function getSourceLabelAttribute()
    {
        if ($this->team_id) {
            return $this->team?->name;
        }

        if ($this->user_id === auth()->id()) {
            return null;
        }

        return $this->owner?->name;
    }
}
