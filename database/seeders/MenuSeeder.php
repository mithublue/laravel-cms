<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Setting;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Create or update a Primary menu by slug
        $menu = Menu::updateOrCreate(
            ['slug' => 'primary'],
            [
                'name' => 'Primary',
                'description' => 'Primary navigation menu',
            ]
        );

        // Ensure it has at least a Home item
        if (!MenuItem::where('menu_id', $menu->id)->exists()) {
            MenuItem::create([
                'menu_id' => $menu->id,
                'parent_id' => null,
                'title' => 'Home',
                'url' => url('/'),
                'order' => 1,
                'target' => null,
                'classes' => null,
                'rel' => null,
                'meta' => [],
            ]);
        }

        // Map 'primary' location to this menu via settings.menu_locations
        $locations = [];
        $existing = Setting::where('key', 'menu_locations')->value('value');
        if ($existing) {
            $decoded = json_decode($existing, true);
            if (is_array($decoded)) {
                $locations = $decoded;
            }
        }
        $locations['primary'] = $menu->id;
        Setting::updateOrCreate(['key' => 'menu_locations'], ['value' => json_encode($locations)]);
    }
}
