<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Support\ThemeManager;
use App\Models\Post;
use App\Models\News;
use App\Models\Product;
use App\Models\User;

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

        // Register morph map aliases without enforcing them to stay compatible with
        // packages that store FQCNs (e.g., spatie/permission uses App\Models\User)
        Relation::morphMap([
            'post' => Post::class,
            'news' => News::class,
            'product' => Product::class,
            'user' => User::class,
        ]);
    }
}
