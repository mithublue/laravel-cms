<?php

namespace App\Support;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class TemplateResolver
{
    /**
     * Render a home/front page using WP-like hierarchy: front-page, home, index
     */
    public static function renderHome(array $data = [])
    {
        $candidates = [
            'theme::front-page',
            'theme::home',
            'theme::index',
        ];
        return self::firstExisting($candidates, $data);
    }

    /**
     * Render a page using hierarchy: page-{slug}, page-{id}, page, singular, index
     */
    public static function renderPage(object $page, array $data = [])
    {
        Cms::setCurrentObject($page);
        $slug = self::slugFrom($page, ['title', 'name', 'slug']);
        $id = $page->id ?? null;
        $candidates = [
            "theme::page-{$slug}",
            $id ? "theme::page-{$id}" : null,
            'theme::page',
            'theme::singular',
            'theme::index',
        ];
        return self::firstExisting(array_filter($candidates), array_merge($data, ['page' => $page]));
    }

    /**
     * Render a single item for a given post type (e.g., post, news, product)
     * Hierarchy tries: {type}-{slug}, {type}-{id}, {type}, single-{type}-{slug}, single-{type}-{id}, single-{type}, single, index
     */
    public static function renderSingle(string $type, object $item, array $data = [])
    {
        Cms::setCurrentObject($item);
        $slug = self::slugFrom($item, ['title', 'name', 'slug']);
        $id = $item->id ?? null;
        $type = Str::slug($type);
        $candidates = [
            "theme::{$type}-{$slug}",
            $id ? "theme::{$type}-{$id}" : null,
            "theme::{$type}",
            "theme::single-{$type}-{$slug}",
            $id ? "theme::single-{$type}-{$id}" : null,
            "theme::single-{$type}",
            'theme::single',
            'theme::index',
        ];
        return self::firstExisting(array_filter($candidates), array_merge($data, [$type => $item]));
    }

    /**
     * Render a 404 using hierarchy: 404, index
     */
    public static function render404(array $data = [])
    {
        $candidates = [
            'theme::404',
            'theme::index',
        ];
        return self::firstExisting($candidates, $data);
    }

    protected static function firstExisting(array $candidates, array $data)
    {
        foreach ($candidates as $view) {
            if (View::exists($view)) {
                return view($view, $data);
            }
        }
        // As a last resort, use a plain Laravel default
        abort(500, 'No theme templates found.');
    }

    protected static function slugFrom(object $obj, array $preferredProps): string
    {
        foreach ($preferredProps as $prop) {
            if (isset($obj->{$prop}) && is_string($obj->{$prop}) && $obj->{$prop} !== '') {
                return Str::slug($obj->{$prop});
            }
        }
        return (string) ($obj->id ?? 'item');
    }
}
