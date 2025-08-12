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
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
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
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'] ?? null,
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
                'excerpt' => optional($page->translation)->excerpt,
                'content' => optional($page->translation)->content,
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
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
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
                'excerpt' => $data['excerpt'] ?? null,
                'content' => $data['content'] ?? null,
            ]);
        } else {
            PageTranslation::create([
                'page_id' => $page->id,
                'locale' => $locale,
                'title' => $data['title'],
                'slug' => $slug,
                'excerpt' => $data['excerpt'] ?? null,
                'content' => $data['content'] ?? null,
            ]);
        }

        return redirect()->route('admin.pages.index')->with('success', 'Page updated.');
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted.');
    }

    /**
     * List trashed pages
     */
    public function trash(Request $request): Response
    {
        $query = Page::onlyTrashed()->with(['translation']);

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
                    'deleted_at' => optional($page->deleted_at)?->toDateTimeString(),
                ];
            });

        return Inertia::render('Admin/Pages/Trash', [
            'pages' => $pages,
            'filters' => [
                'search' => $request->get('search'),
            ],
        ]);
    }

    /** Restore a trashed page */
    public function restore($id)
    {
        $page = Page::onlyTrashed()->findOrFail($id);
        $page->restore();
        return redirect()->route('admin.pages.trash')->with('success', 'Page restored.');
    }

    /** Permanently delete a page */
    public function forceDelete($id)
    {
        $page = Page::onlyTrashed()->with('translations')->findOrFail($id);
        // Ensure related translations are removed
        $page->translations()->delete();
        $page->forceDelete();
        return redirect()->route('admin.pages.trash')->with('success', 'Page permanently deleted.');
    }

    /** Bulk soft delete (move to trash) */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required','array'],
            'ids.*' => ['integer','exists:pages,id'],
        ])['ids'];

        Page::whereIn('id', $ids)->delete();
        return back()->with('success', 'Selected pages moved to trash.');
    }

    /** Bulk restore */
    public function bulkRestore(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required','array'],
            'ids.*' => ['integer'],
        ])['ids'];

        Page::onlyTrashed()->whereIn('id', $ids)->restore();
        return back()->with('success', 'Selected pages restored.');
    }

    /** Bulk permanent delete */
    public function bulkForceDelete(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required','array'],
            'ids.*' => ['integer'],
        ])['ids'];

        $pages = Page::onlyTrashed()->with('translations')->whereIn('id', $ids)->get();
        foreach ($pages as $p) {
            $p->translations()->delete();
            $p->forceDelete();
        }
        return back()->with('success', 'Selected pages permanently deleted.');
    }
}
