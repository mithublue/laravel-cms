<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuItemSyncController extends Controller
{
    public function store(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['nullable', 'integer', 'exists:menu_items,id'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.url' => ['nullable', 'string', 'max:2048'],
            'items.*.target' => ['nullable', 'string', 'max:20'],
            'items.*.classes' => ['nullable', 'string', 'max:255'],
            'items.*.rel' => ['nullable', 'string', 'max:255'],
            'items.*.linkable_type' => ['nullable', 'string', 'max:255'],
            'items.*.linkable_id' => ['nullable', 'integer'],
            'items.*.meta' => ['nullable', 'array'],
            'items.*.children' => ['array'],
        ]);

        DB::transaction(function () use ($menu, $data) {
            // Collect existing IDs to later delete removed ones
            $existingIds = $menu->allItems()->pluck('id')->all();
            $keptIds = [];

            $order = 0;
            foreach ($data['items'] as $rootItem) {
                $this->upsertItem($menu, $rootItem, null, $order, $keptIds);
                $order++;
            }

            // Delete items that are no longer present
            $toDelete = array_diff($existingIds, $keptIds);
            if (!empty($toDelete)) {
                MenuItem::whereIn('id', $toDelete)->delete();
            }
        });

        return back()->with('success', 'Menu items saved.');
    }

    protected function upsertItem(Menu $menu, array $payload, ?int $parentId, int $order, array & $keptIds)
    {
        $item = null;
        if (!empty($payload['id'])) {
            $item = MenuItem::where('menu_id', $menu->id)->where('id', $payload['id'])->first();
        }

        $attributes = [
            'menu_id' => $menu->id,
            'parent_id' => $parentId,
            'title' => $payload['title'] ?? 'Untitled',
            'url' => $payload['url'] ?? null,
            'target' => $payload['target'] ?? null,
            'order' => $order,
            'linkable_type' => $payload['linkable_type'] ?? null,
            'linkable_id' => $payload['linkable_id'] ?? null,
            'classes' => $payload['classes'] ?? null,
            'rel' => $payload['rel'] ?? null,
            'meta' => $payload['meta'] ?? [],
        ];

        if ($item) {
            $item->update($attributes);
        } else {
            $item = MenuItem::create($attributes);
        }

        $keptIds[] = $item->id;

        // children
        $childOrder = 0;
        foreach (($payload['children'] ?? []) as $child) {
            $this->upsertItem($menu, $child, $item->id, $childOrder, $keptIds);
            $childOrder++;
        }
    }
}
