<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL; // 1. Import a fasad URL
use Illuminate\Support\ServiceProvider;

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
        // 2. Tambahkan blok if ini
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
