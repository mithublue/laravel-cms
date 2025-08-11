<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Post::query()->with(['translation']);

        if ($search = $request->string('search')->toString()) {
            $query->whereHas('translations', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $posts = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString()
            ->through(function (Post $post) {
                return [
                    'id' => $post->id,
                    'title' => optional($post->translation)->title,
                    'slug' => optional($post->translation)->slug,
                    'status' => $post->status,
                    'visibility' => $post->visibility,
                    'published_at' => optional($post->published_at)?->toDateTimeString(),
                    'is_pinned' => (bool) $post->is_pinned,
                    'allow_comments' => (bool) $post->allow_comments,
                ];
            });

        return Inertia::render('Admin/Posts/Index', [
            'posts' => $posts,
            'filters' => [
                'search' => $request->get('search'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Posts/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['nullable','string','max:255', Rule::unique('post_translations','slug')->where('locale', app()->getLocale())],
            'status' => 'required|in:draft,scheduled,published,archived',
            'visibility' => 'required|in:public,private',
            'published_at' => 'nullable|date',
            'is_pinned' => 'boolean',
            'allow_comments' => 'boolean',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
        ]);

        $slug = $data['slug'] ?? Str::slug($data['title']);

        $post = Post::create([
            'author_id' => $request->user()->id,
            'status' => $data['status'],
            'visibility' => $data['visibility'],
            'published_at' => $data['published_at'] ?? null,
            'is_pinned' => $data['is_pinned'] ?? false,
            'allow_comments' => $data['allow_comments'] ?? true,
        ]);

        PostTranslation::create([
            'post_id' => $post->id,
            'locale' => app()->getLocale(),
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'] ?? null,
        ]);

        return redirect()->route('admin.posts.index')->with('success', 'Post created.');
    }

    public function edit(Post $post): Response
    {
        $post->load('translation');

        return Inertia::render('Admin/Posts/Edit', [
            'post' => [
                'id' => $post->id,
                'title' => optional($post->translation)->title,
                'slug' => optional($post->translation)->slug,
                'excerpt' => optional($post->translation)->excerpt,
                'content' => optional($post->translation)->content,
                'status' => $post->status,
                'visibility' => $post->visibility,
                'published_at' => optional($post->published_at)?->format('Y-m-d\\TH:i'),
                'is_pinned' => (bool) $post->is_pinned,
                'allow_comments' => (bool) $post->allow_comments,
            ],
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $locale = app()->getLocale();
        $translation = $post->translations()->where('locale', $locale)->first();

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable','string','max:255',
                Rule::unique('post_translations','slug')
                    ->where('locale', $locale)
                    ->ignore(optional($translation)->id)
            ],
            'status' => 'required|in:draft,scheduled,published,archived',
            'visibility' => 'required|in:public,private',
            'published_at' => 'nullable|date',
            'is_pinned' => 'boolean',
            'allow_comments' => 'boolean',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
        ]);

        $slug = $data['slug'] ?? Str::slug($data['title']);

        $post->update([
            'status' => $data['status'],
            'visibility' => $data['visibility'],
            'published_at' => $data['published_at'] ?? null,
            'is_pinned' => $data['is_pinned'] ?? false,
            'allow_comments' => $data['allow_comments'] ?? true,
        ]);

        if ($translation) {
            $translation->update([
                'title' => $data['title'],
                'slug' => $slug,
                'excerpt' => $data['excerpt'] ?? null,
                'content' => $data['content'] ?? null,
            ]);
        } else {
            PostTranslation::create([
                'post_id' => $post->id,
                'locale' => $locale,
                'title' => $data['title'],
                'slug' => $slug,
                'excerpt' => $data['excerpt'] ?? null,
                'content' => $data['content'] ?? null,
            ]);
        }

        return redirect()->route('admin.posts.index')->with('success', 'Post updated.');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Post deleted.');
    }
}
