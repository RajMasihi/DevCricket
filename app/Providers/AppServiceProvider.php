<?php

namespace App\Providers;

use App\Services\CricbuzzApiService;
use App\Services\MatchEventBroker;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CricbuzzApiService::class);
        $this->app->singleton(MatchEventBroker::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
