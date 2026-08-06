<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Security extends Model
{
    use HasFactory;

    public const ASSET_CLASSES = [
        'EQ' => 'Equity',
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

    protected $attributes = [
        'is_tracked' => true,
        'is_in_use' => true,
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

    public function user()
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

    public function quotes()
    {
        return $this->hasMany(Quote::class);
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

        return $this->user?->name;
    }

    public function linkUnless(Security $current): string
    {
        if ($this->id === $current->id) {
            return e($this->name);
        }

        return '<a href="' . route('securities.edit', $this->id) . '" class="text-blue-600">'
            . e($this->name)
            . '</a>';
    }

    public function quoteAt(Carbon $date)
    {
        $visited = [];
        $cache = [];

        return $this->resolveQuoteAt($date, $visited, $cache);
    }

    private function resolveQuoteAt(Carbon $date, array &$visited, array &$cache)
    {
        // Prevent infinite loops
        if (in_array($this->id, $visited)) {
            throw new \Exception("Currency recursion detected for security {$this->id}");
        }

        // Cached?
        if (isset($cache[$this->id])) {
            return $cache[$this->id];
        }

        $visited[] = $this->id;

        // Latest quote <= date
        $latestQuote = $this->quotes()
            ->where('quote_date', '<=', $date)
            ->orderBy('quote_date', 'desc')
            ->value('price');

        if ($latestQuote === null) {
            throw new \Exception("No quote available for security {$this->id} on or before {$date->toDateString()}");
        }

        // No currency → done
        if ($this->currency_id === null) {
            return $cache[$this->id] = $latestQuote;
        }

        // Resolve currency recursively
        $currency = $this->currency; // relationship

        if (!$currency) {
            throw new \Exception("Currency security {$this->currency_id} not found");
        }

        $currencyQuote = $currency->resolveQuoteAt($date, $visited, $cache);

        return $cache[$this->id] = $latestQuote * $currencyQuote;
    }
    
    public function scopeAvailableCurrencies($query, User $user)
    {
        $teamIds = $user->teamsWithSecurities->pluck('id');

        return $query
            ->where('asset_class', 'FX')
            ->where('is_in_use', 1)
            ->where(function ($q) use ($user, $teamIds) {
                $q->where('user_id', $user->id)
                  ->orWhereIn('team_id', $teamIds);
            })
            ->orderBy('name');
    }
        
    public static function currenciesForDocument(Document $document, User $user)
    {
        $available = self::availableCurrencies($user)->get();

        if ($document->currency_id) {
            $current = self::find($document->currency_id);

            if ($current && !$available->contains('id', $current->id)) {
                $available->push($current);
            }
        }

        return $available;
    }
}
