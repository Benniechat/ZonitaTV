<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Category;
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

        View::composer('layouts.app', function ($view) {
            try {
                if (Schema::hasTable('categories')) {
                    $categories = Category::where('is_nav_visible', true)
                        ->orderBy('sort_order')
                        ->get();
                    $view->with('navCategories', $categories);
                }
            } catch (\Throwable $e) {
                $view->with('navCategories', collect());
            }
        });
    }
}
