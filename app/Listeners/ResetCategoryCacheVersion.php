<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Services\UserCacheVersionService;
use Illuminate\Support\Facades\Log;

class ResetCategoryCacheVersion
{
    protected $versionService;

    public function __construct(UserCacheVersionService $versionService)
    {
        $this->versionService = $versionService;
    }

    public function handle(Login $event): void
    {
Log::info('CACHE login');

        $this->versionService->increment('categories');
    }
}
