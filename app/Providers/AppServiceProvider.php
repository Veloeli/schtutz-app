<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use App\Models\RollupCategory;
use App\Observers\RollupCategoryObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RollupCategory::observe(RollupCategoryObserver::class);
        
        Paginator::useBootstrap();
        require_once app_path('Support/helpers.php');

        DB::listen(function ($query) {
            logger()->info('SQL', [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
            ]);
        });
    }

    protected $policies = [
        \App\Models\Category::class => \App\Policies\CategoryPolicy::class,
    ];
}
