<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;
use App\Models\News;
use App\Models\Product;
use Illuminate\Support\Str;

class PublicController extends Controller
{
    public function home()
    {
        return \App\Support\TemplateResolver::renderHome();
    }

    public function page(string $slug)
    {
        $page = Page::with(['translation', 'translations'])->get()->first(function ($p) use ($slug) {
            $t = $p->translation ?? $p->translations->first();
            return $t && Str::slug($t->title) === $slug;
        });
        abort_unless($page, 404);
        return \App\Support\TemplateResolver::renderPage($page, compact('page'));
    }

    public function post(string $slug)
    {
        $post = Post::with(['translation', 'translations'])->get()->first(function ($p) use ($slug) {
            $t = $p->translation ?? $p->translations->first();
            return $t && Str::slug($t->title) === $slug;
        });
        abort_unless($post, 404);
        return \App\Support\TemplateResolver::renderSingle('post', $post, compact('post'));
    }

    public function news(string $slug)
    {
        $news = News::with(['translation', 'translations'])->get()->first(function ($n) use ($slug) {
            $t = $n->translation ?? $n->translations->first();
            return $t && Str::slug($t->title) === $slug;
        });
        abort_unless($news, 404);
        return \App\Support\TemplateResolver::renderSingle('news', $news, compact('news'));
    }

    public function product(string $slug)
    {
        $product = Product::with(['translation', 'translations'])->get()->first(function ($p) use ($slug) {
            $t = $p->translation ?? $p->translations->first();
            return $t && Str::slug($t->name) === $slug;
        });
        abort_unless($product, 404);
        return \App\Support\TemplateResolver::renderSingle('product', $product, compact('product'));
    }
}
