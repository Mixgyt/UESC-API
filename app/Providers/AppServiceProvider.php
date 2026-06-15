<?php

namespace App\Providers;

use App\Services\BitcoinRpcService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BitcoinRpcService::class, fn (): BitcoinRpcService => new BitcoinRpcService());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
