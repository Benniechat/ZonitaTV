<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Enforce HTTPS URL generation for all assets, routes, and Vite manifests when deployed behind reverse proxies (like Render)
        if ($this->app->environment('production') || str_starts_with(config('app.url', ''), 'https://') || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }
    }
}
