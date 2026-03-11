<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UserCacheVersionService
{
    protected function key(string $domain): ?string
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        return "{$domain}_version_user_{$user->id}";
    }

    public function get(string $domain): int
    {
        $cacheKey = $this->key($domain);

        if (! $cacheKey) {
            return -1;
        }
$int = Cache::rememberForever(
    $cacheKey,
    fn () => 1
);
Log::info('CACHE get ' . $int);

        return Cache::rememberForever(
            $cacheKey,
            fn () => 1
        );
    }

    public function increment(string $domain): void
    {
Log::info('CACHE increment');
        $cacheKey = $this->key($domain);

        if (! $cacheKey) {
            return;
        }

        // add() sets the value ONLY if the key does not exist
        if (! Cache::add($cacheKey, 1)) {
            Cache::increment($cacheKey);
        }
    }
}
