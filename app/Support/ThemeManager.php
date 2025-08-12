<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ThemeManager
{
    public static function active(): string
    {
        return Cache::rememberForever('active_theme', function () {
            // During initial boot/migrations the settings table may not exist yet
            if (!Schema::hasTable('settings')) {
                return 'default';
            }
            $val = Setting::where('key', 'active_theme')->value('value');
            return $val ?: 'default';
        });
    }

    public static function setActive(string $theme): void
    {
        Setting::updateOrCreate(['key' => 'active_theme'], ['value' => $theme]);
        Cache::forget('active_theme');
    }

    public static function available(): array
    {
        $themesPath = resource_path('themes');
        if (!File::isDirectory($themesPath)) {
            return [];
        }
        return collect(File::directories($themesPath))
            ->map(fn($dir) => basename($dir))
            ->values()
            ->all();
    }

    public static function viewPaths(string $theme): array
    {
        $paths = [];
        $themeViews = resource_path('themes/' . $theme . '/views');
        if (is_dir($themeViews)) {
            $paths[] = $themeViews;
        }
        // fallback to default theme
        $defaultViews = resource_path('themes/default/views');
        if (is_dir($defaultViews)) {
            $paths[] = $defaultViews;
        }
        // finally app default views
        $paths[] = resource_path('views');
        return $paths;
    }
}
