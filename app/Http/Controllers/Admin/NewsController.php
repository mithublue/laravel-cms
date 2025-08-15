<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsTranslation;
use App\Services\MediaService;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class NewsController extends Controller
{
    public function index(Request $request): Response
    {
        $query = News::query()->with(['translation']);

        if ($search = $request->string('search')->toString()) {
            $query->whereHas('translations', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $news = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString()
            ->through(function (News $item) {
                return [
                    'id' => $item->id,
                    'title' => optional($item->translation)->title,
                    'slug' => optional($item->translation)->slug,
                    'status' => $item->status,
                    'visibility' => $item->visibility,
                    'published_at' => optional($item->published_at)?->toDateTimeString(),
                    'is_featured' => (bool) $item->is_featured,
                ];
            });

        return Inertia::render('Admin/News/Index', [
            'news' => $news,
            'filters' => [
                'search' => $request->get('search'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/News/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['nullable','string','max:255', Rule::unique('news_translations','slug')->where('locale', app()->getLocale())],
            'status' => 'required|in:draft,scheduled,published,archived',
            'visibility' => 'required|in:public,private',
            'published_at' => 'nullable|date',
            'is_featured' => 'boolean',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'featured_image' => 'nullable|image|max:5120',
            'terms' => 'sometimes|array',
            'terms.*' => 'integer',
        ]);

        $slug = $data['slug'] ?? Str::slug($data['title']);

        $item = News::create([
            'author_id' => $request->user()->id,
            'status' => $data['status'],
            'visibility' => $data['visibility'],
            'published_at' => $data['published_at'] ?? null,
            'is_featured' => $data['is_featured'] ?? false,
        ]);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $media = MediaService::storeFromUpload($request->file('featured_image'), $request->user()->id, 'uploads/'.date('Y/m'));
            $item->update(['featured_image_id' => $media->id]);
        }

        NewsTranslation::create([
            'news_id' => $item->id,
            'locale' => app()->getLocale(),
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'] ?? null,
        ]);

        // Sync terms (scope: news)
        $termIds = collect($request->input('terms', []))->filter()->map(fn($id) => (int) $id);
        if ($termIds->isNotEmpty()) {
            $allowedIds = Term::whereIn('id', $termIds)
                ->whereHas('taxonomy', function ($q) { $q->where('scope', 'news'); })
                ->pluck('id')
                ->all();
            $item->terms()->sync($allowedIds);
        } else {
            $item->terms()->sync([]);
        }

        return redirect()->route('admin.news.edit', $item)->with('success', 'News created.');
    }

    public function edit(News $news): Response
    {
        $news->load(['translation','featuredImage']);

        return Inertia::render('Admin/News/Edit', [
            'news' => [
                'id' => $news->id,
                'title' => optional($news->translation)->title,
                'slug' => optional($news->translation)->slug,
                'excerpt' => optional($news->translation)->excerpt,
                'content' => optional($news->translation)->content,
                'status' => $news->status,
                'visibility' => $news->visibility,
                'published_at' => optional($news->published_at)?->format('Y-m-d\\TH:i'),
                'is_featured' => (bool) $news->is_featured,
                'featured_image_url' => optional($news->featuredImage)?->url(),
                'term_ids' => $news->terms()->whereHas('taxonomy', function ($q) { $q->where('scope', 'news'); })->pluck('terms.id'),
            ],
        ]);
    }

    public function update(Request $request, News $news)
    {
        $locale = app()->getLocale();
        $translation = $news->translations()->where('locale', $locale)->first();

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable','string','max:255',
                Rule::unique('news_translations','slug')
                    ->where('locale', $locale)
                    ->ignore(optional($translation)->id)
            ],
            'status' => 'required|in:draft,scheduled,published,archived',
            'visibility' => 'required|in:public,private',
            'published_at' => 'nullable|date',
            'is_featured' => 'boolean',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'featured_image' => 'nullable|image|max:5120',
            'terms' => 'sometimes|array',
            'terms.*' => 'integer',
        ]);

        $slug = $data['slug'] ?? Str::slug($data['title']);

        $news->update([
            'status' => $data['status'],
            'visibility' => $data['visibility'],
            'published_at' => $data['published_at'] ?? null,
            'is_featured' => $data['is_featured'] ?? false,
        ]);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $media = MediaService::storeFromUpload($request->file('featured_image'), $request->user()->id, 'uploads/'.date('Y/m'));
            $news->update(['featured_image_id' => $media->id]);
        }

        if ($translation) {
            $translation->update([
                'title' => $data['title'],
                'slug' => $slug,
                'excerpt' => $data['excerpt'] ?? null,
                'content' => $data['content'] ?? null,
            ]);
        } else {
            NewsTranslation::create([
                'news_id' => $news->id,
                'locale' => $locale,
                'title' => $data['title'],
                'slug' => $slug,
                'excerpt' => $data['excerpt'] ?? null,
                'content' => $data['content'] ?? null,
            ]);
        }

        // Sync terms (scope: news)
        $termIds = collect($request->input('terms', []))->filter()->map(fn($id) => (int) $id);
        if ($termIds->isNotEmpty()) {
            $allowedIds = Term::whereIn('id', $termIds)
                ->whereHas('taxonomy', function ($q) { $q->where('scope', 'news'); })
                ->pluck('id')
                ->all();
            $news->terms()->sync($allowedIds);
        } else {
            $news->terms()->sync([]);
        }

        return redirect()->route('admin.news.edit', $news)->with('success', 'News updated.');
    }

    public function destroy(News $news)
    {
        $news->delete();

        return redirect()->route('admin.news.index')->with('success', 'News deleted.');
    }

    /**
     * List trashed news
     */
    public function trash(Request $request): Response
    {
        $query = News::onlyTrashed()->with(['translation']);

        if ($search = $request->string('search')->toString()) {
            $query->whereHas('translations', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $news = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString()
            ->through(function (News $item) {
                return [
                    'id' => $item->id,
                    'title' => optional($item->translation)->title,
                    'slug' => optional($item->translation)->slug,
                    'deleted_at' => optional($item->deleted_at)?->toDateTimeString(),
                ];
            });

        return Inertia::render('Admin/News/Trash', [
            'news' => $news,
            'filters' => [
                'search' => $request->get('search'),
            ],
        ]);
    }

    /** Restore a trashed news item */
    public function restore($id)
    {
        $news = News::onlyTrashed()->findOrFail($id);
        $news->restore();
        return redirect()->route('admin.news.trash')->with('success', 'News restored.');
    }

    /** Permanently delete a news item */
    public function forceDelete($id)
    {
        $news = News::onlyTrashed()->with('translations')->findOrFail($id);
        $news->translations()->delete();
        $news->forceDelete();
        return redirect()->route('admin.news.trash')->with('success', 'News permanently deleted.');
    }

    /** Bulk soft delete (move to trash) */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required','array'],
            'ids.*' => ['integer','exists:news,id'],
        ])['ids'];

        News::whereIn('id', $ids)->delete();
        return back()->with('success', 'Selected news moved to trash.');
    }

    /** Bulk restore */
    public function bulkRestore(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required','array'],
            'ids.*' => ['integer'],
        ])['ids'];

        News::onlyTrashed()->whereIn('id', $ids)->restore();
        return back()->with('success', 'Selected news restored.');
    }

    /** Bulk permanent delete */
    public function bulkForceDelete(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required','array'],
            'ids.*' => ['integer'],
        ])['ids'];

        $items = News::onlyTrashed()->with('translations')->whereIn('id', $ids)->get();
        foreach ($items as $n) {
            $n->translations()->delete();
            $n->forceDelete();
        }
        return back()->with('success', 'Selected news permanently deleted.');
    }
}
