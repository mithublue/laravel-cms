<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $menus = Menu::query()
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Menus/Index', [
            'menus' => $menus,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Menus/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $menu = Menu::create($data);

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu created.');
    }

    public function edit(Menu $menu)
    {
        $menu->load(['items' => function ($q) {
            $q->with('children');
        }, 'allItems']);

        // Build nested tree
        $tree = $menu->items->map(function ($item) {
            return $this->mapItem($item);
        })->values();

        // Sources: recent pages, posts, news, products (use safe fallbacks for columns)
        $pages = \App\Models\Page::query()->latest()->limit(20)->get()
            ->map(function ($p) {
                $title = $p->title ?? $p->name ?? ($p->slug ?? ('Page '.$p->id));
                $url = $p->slug ? '/'.$p->slug : ('/pages/'.$p->id);
                return [ 'id' => $p->id, 'title' => $title, 'url' => $url ];
            });
        $posts = \App\Models\Post::query()->latest()->limit(20)->get()
            ->map(function ($p) {
                $title = $p->title ?? ($p->slug ?? ('Post '.$p->id));
                $url = $p->slug ? '/posts/'.$p->slug : ('/posts/'.$p->id);
                return [ 'id' => $p->id, 'title' => $title, 'url' => $url ];
            });
        $news = \App\Models\News::query()->latest()->limit(20)->get()
            ->map(function ($n) {
                $title = $n->title ?? ($n->slug ?? ('News '.$n->id));
                $url = $n->slug ? '/news/'.$n->slug : ('/news/'.$n->id);
                return [ 'id' => $n->id, 'title' => $title, 'url' => $url ];
            });
        $products = \App\Models\Product::query()->latest()->limit(20)->get()
            ->map(function ($p) {
                $title = $p->name ?? ($p->slug ?? ('Product '.$p->id));
                $url = $p->slug ? '/products/'.$p->slug : ('/products/'.$p->id);
                return [ 'id' => $p->id, 'title' => $title, 'url' => $url ];
            });

        return Inertia::render('Admin/Menus/Edit', [
            'menu' => [
                'id' => $menu->id,
                'name' => $menu->name,
                'location' => $menu->location,
                'description' => $menu->description,
                'tree' => $tree,
            ],
            'sources' => [
                'pages' => $pages,
                'posts' => $posts,
                'news' => $news,
                'products' => $products,
            ],
        ]);
    }

    protected function mapItem($item)
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'url' => $item->url,
            'target' => $item->target,
            'classes' => $item->classes,
            'rel' => $item->rel,
            'linkable_type' => $item->linkable_type,
            'linkable_id' => $item->linkable_id,
            'meta' => $item->meta,
            'children' => $item->children->map(fn($c) => $this->mapItem($c))->values(),
        ];
    }

    public function update(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $menu->update($data);

        return back()->with('success', 'Menu updated.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        return redirect()->route('admin.menus.index')->with('success', 'Menu deleted.');
    }
}
