<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Page::query()->with(['translation']);

        if ($search = $request->string('search')->toString()) {
            $query->whereHas('translations', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $pages = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString()
            ->through(function (Page $page) {
                return [
                    'id' => $page->id,
                    'title' => optional($page->translation)->title,
                    'slug' => optional($page->translation)->slug,
                    'status' => $page->status,
                    'visibility' => $page->visibility,
                    'published_at' => optional($page->published_at)?->toDateTimeString(),
                ];
            });

        return Inertia::render('Admin/Pages/Index', [
            'pages' => $pages,
            'filters' => [
                'search' => $request->get('search'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Pages/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['nullable','string','max:255', Rule::unique('page_translations','slug')->where('locale', app()->getLocale())],
            'status' => 'required|in:draft,scheduled,published,archived',
            'visibility' => 'required|in:public,private',
            'published_at' => 'nullable|date',
        ]);

        $slug = $data['slug'] ?? Str::slug($data['title']);

        $page = Page::create([
            'author_id' => $request->user()->id,
            'status' => $data['status'],
            'visibility' => $data['visibility'],
            'published_at' => $data['published_at'] ?? null,
        ]);

        PageTranslation::create([
            'page_id' => $page->id,
            'locale' => app()->getLocale(),
            'title' => $data['title'],
            'slug' => $slug,
        ]);

        return redirect()->route('admin.pages.index')->with('success', 'Page created.');
    }

    public function edit(Page $page): Response
    {
        $page->load('translation');

        return Inertia::render('Admin/Pages/Edit', [
            'page' => [
                'id' => $page->id,
                'title' => optional($page->translation)->title,
                'slug' => optional($page->translation)->slug,
                'status' => $page->status,
                'visibility' => $page->visibility,
                'published_at' => optional($page->published_at)?->format('Y-m-d\\TH:i'),
            ],
        ]);
    }

    public function update(Request $request, Page $page)
    {
        $locale = app()->getLocale();
        $translation = $page->translations()->where('locale', $locale)->first();

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:page_translations,slug,' . optional($translation)->id . ',id,locale,' . $locale,
            'status' => 'required|in:draft,scheduled,published,archived',
            'visibility' => 'required|in:public,private',
            'published_at' => 'nullable|date',
        ]);

        $slug = $data['slug'] ?? Str::slug($data['title']);

        $page->update([
            'status' => $data['status'],
            'visibility' => $data['visibility'],
            'published_at' => $data['published_at'] ?? null,
        ]);

        if ($translation) {
            $translation->update([
                'title' => $data['title'],
                'slug' => $slug,
            ]);
        } else {
            PageTranslation::create([
                'page_id' => $page->id,
                'locale' => $locale,
                'title' => $data['title'],
                'slug' => $slug,
            ]);
        }

        return redirect()->route('admin.pages.index')->with('success', 'Page updated.');
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted.');
    }
}
