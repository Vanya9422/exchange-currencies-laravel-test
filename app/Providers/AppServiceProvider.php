<?php

namespace App\Providers;

use App\Modules\Shared\CurrencyRate\Services\CurrencyRateService;
use App\Modules\Shared\CurrencyRate\Services\CurrencyRateServiceInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CurrencyRateServiceInterface::class, CurrencyRateService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
