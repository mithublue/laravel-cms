<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsTranslation;
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
        ]);

        $slug = $data['slug'] ?? Str::slug($data['title']);

        $item = News::create([
            'author_id' => $request->user()->id,
            'status' => $data['status'],
            'visibility' => $data['visibility'],
            'published_at' => $data['published_at'] ?? null,
            'is_featured' => $data['is_featured'] ?? false,
        ]);

        NewsTranslation::create([
            'news_id' => $item->id,
            'locale' => app()->getLocale(),
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'] ?? null,
        ]);

        return redirect()->route('admin.news.index')->with('success', 'News created.');
    }

    public function edit(News $news): Response
    {
        $news->load('translation');

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
        ]);

        $slug = $data['slug'] ?? Str::slug($data['title']);

        $news->update([
            'status' => $data['status'],
            'visibility' => $data['visibility'],
            'published_at' => $data['published_at'] ?? null,
            'is_featured' => $data['is_featured'] ?? false,
        ]);

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

        return redirect()->route('admin.news.index')->with('success', 'News updated.');
    }

    public function destroy(News $news)
    {
        $news->delete();

        return redirect()->route('admin.news.index')->with('success', 'News deleted.');
    }
}
