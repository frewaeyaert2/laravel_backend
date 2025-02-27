<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\Azure\AzureBlobHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AzureBlobHelper::class, function ($app) {
            return new AzureBlobHelper();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
