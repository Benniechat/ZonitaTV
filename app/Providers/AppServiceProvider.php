<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Category;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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

        // Rate Limiter Configurations
        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('reports', function (Request $request) {
            return Limit::perMinutes(10, 5)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('telemetry', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });

        View::composer('layouts.app', function ($view) {
            try {
                if (Schema::hasTable('categories')) {
                    $categories = Cache::remember('nav_categories_cache', 3600, function () {
                        return Category::where('is_nav_visible', true)
                            ->orderBy('sort_order')
                            ->get();
                    });
                    $view->with('navCategories', $categories);
                }
            } catch (\Throwable $e) {
                $view->with('navCategories', collect());
            }
        });
    }
}
