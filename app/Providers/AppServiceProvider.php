<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Support\ThemeManager;

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
        Vite::prefetch(concurrency: 3);

        // Register theme view namespace dynamically
        $active = ThemeManager::active();
        $paths = ThemeManager::viewPaths($active);
        // Clear previous namespace to avoid stacking on reloads (only relevant in tests/cli)
        View::replaceNamespace('theme', $paths);
    }
}
