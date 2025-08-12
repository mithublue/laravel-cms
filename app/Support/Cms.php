<?php

namespace App\Support;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Setting;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App as AppFacade;

class Cms
{
    /**
     * Get the currently authenticated user's ID or null.
     */
    public static function currentUserId(): ?int
    {
        return Auth::id();
    }

    // WP-style alias
    public static function get_current_user_id(): ?int
    {
        return self::currentUserId();
    }

    /**
     * Get the currently authenticated user or null.
     */
    public static function currentUser(): ?Authenticatable
    {
        return Auth::user();
    }

    // WP-style alias
    public static function get_current_user(): ?Authenticatable
    {
        return self::currentUser();
    }

    /**
     * Store the current content object for the request lifecycle.
     */
    public static function setCurrentObject(mixed $object): void
    {
        AppFacade::instance('cms.current_object', $object);
    }

    /**
     * Retrieve the current content object.
     */
    public static function currentObject(): mixed
    {
        return AppFacade::bound('cms.current_object') ? AppFacade::make('cms.current_object') : null;
    }

    // WP-style alias
    public static function get_current_obj(): mixed
    {
        return self::currentObject();
    }

    /**
     * Get a flat list of menu items by location string or menu id or slug/name.
     * Returns nested array tree suitable for rendering.
     */
    public static function menuTree(string|int $locationOrIdOrName): array
    {
        $menuId = self::resolveMenuId($locationOrIdOrName);
        if (!$menuId) return [];

        $items = MenuItem::where('menu_id', $menuId)
            ->orderBy('parent_id')
            ->orderBy('order')
            ->get(['id','parent_id','title','url','target','classes','rel','meta'])
            ->map(function ($i) {
                return [
                    'id' => $i->id,
                    'parent_id' => $i->parent_id,
                    'title' => $i->title,
                    'url' => $i->url,
                    'target' => $i->target,
                    'classes' => $i->classes,
                    'rel' => $i->rel,
                    'meta' => $i->meta ?? [],
                    'children' => [],
                ];
            })->all();

        $byId = [];
        foreach ($items as $item) {
            $byId[$item['id']] = $item;
        }
        $tree = [];
        foreach ($byId as $id => &$node) {
            if ($node['parent_id'] && isset($byId[$node['parent_id']])) {
                $byId[$node['parent_id']]['children'][] = &$node;
            } else {
                $tree[] = &$node;
            }
        }
        // cleanup references
        foreach ($byId as $id => $n) {}
        return $tree;
    }

    /**
     * Convenience wrapper that returns the nested menu tree.
     */
    public static function menu(string|int $locationOrIdOrName): array
    {
        return self::menuTree($locationOrIdOrName);
    }

    /**
     * Resolve a menu id from location, id, or name/slug.
     */
    public static function resolveMenuId(string|int $locationOrIdOrName): ?int
    {
        if (is_int($locationOrIdOrName)) {
            return Menu::where('id', $locationOrIdOrName)->exists() ? $locationOrIdOrName : null;
        }

        // Try location mapping in settings: key "menu_locations" is a JSON object { location: menu_id }
        $locations = self::setting('menu_locations');
        if (is_string($locations)) {
            $locations = json_decode($locations, true) ?: [];
        }
        if (is_array($locations) && isset($locations[$locationOrIdOrName])) {
            $id = (int) $locations[$locationOrIdOrName];
            if ($id && Menu::where('id', $id)->exists()) {
                return $id;
            }
        }

        // Try by name (case-insensitive) or slug
        $menu = Menu::whereRaw('LOWER(name) = ?', [strtolower($locationOrIdOrName)])
            ->orWhere('slug', $locationOrIdOrName)
            ->first(['id']);
        if ($menu) {
            return (int) $menu->id;
        }

        // Fallback: first available menu
        return optional(Menu::first(['id']))->id;
    }

    /**
     * Get a single setting value.
     */
    public static function setting(string $key, mixed $default = null): mixed
    {
        $val = Cache::rememberForever('setting_'.$key, function () use ($key) {
            return optional(Setting::where('key', $key)->first())->value;
        });
        return $val ?? $default;
    }

    /**
     * Check if a module is enabled.
     */
    public static function moduleEnabled(string $name): bool
    {
        return (bool) Cache::rememberForever('module_enabled_'.$name, function () use ($name) {
            return \App\Models\Module::where('name', $name)->value('enabled') ?? false;
        });
    }
}
