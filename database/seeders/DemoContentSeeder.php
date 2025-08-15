<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Choose an author (admin if exists)
        $authorId = DB::table('users')->where('email', 'admin@example.com')->value('id')
            ?? DB::table('users')->orderBy('id')->value('id');

        // Pages
        $pages = [
            [
                'template' => null,
                'status' => 'published',
                'visibility' => 'public',
                'published_at' => $now,
                'order' => 0,
                'options' => null,
                'author_id' => $authorId,
                'translations' => [
                    'locale' => 'en',
                    'title' => 'Home',
                    'slug' => 'home',
                    'excerpt' => 'Welcome to our CMS',
                    'content' => '<h1>Welcome</h1><p>This is the home page.</p>',
                ],
            ],
            [
                'template' => null,
                'status' => 'published',
                'visibility' => 'public',
                'published_at' => $now,
                'order' => 1,
                'options' => null,
                'author_id' => $authorId,
                'translations' => [
                    'locale' => 'en',
                    'title' => 'About',
                    'slug' => 'about',
                    'excerpt' => 'About us',
                    'content' => '<h1>About</h1><p>About our company.</p>',
                ],
            ],
            [
                'template' => null,
                'status' => 'published',
                'visibility' => 'public',
                'published_at' => $now,
                'order' => 2,
                'options' => null,
                'author_id' => $authorId,
                'translations' => [
                    'locale' => 'en',
                    'title' => 'Blog',
                    'slug' => 'blog',
                    'excerpt' => 'Our latest posts',
                    'content' => '<h1>Blog</h1><p>Latest updates and posts.</p>',
                ],
            ],
        ];

        foreach ($pages as $p) {
            // Find existing page by translation slug
            $existing = DB::table('page_translations')
                ->where('locale', $p['translations']['locale'])
                ->where('slug', $p['translations']['slug'])
                ->first();

            if ($existing) {
                $pageId = $existing->page_id;
                // Optionally update content/excerpt/title if you want; skipping to avoid unintended overwrites
            } else {
                $pageId = DB::table('pages')->insertGetId([
                    'parent_id' => null,
                    'author_id' => $p['author_id'],
                    'featured_image_id' => null,
                    'template' => $p['template'],
                    'status' => $p['status'],
                    'visibility' => $p['visibility'],
                    'published_at' => $p['published_at'],
                    'order' => $p['order'],
                    'options' => $p['options'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('page_translations')->insert([
                    'page_id' => $pageId,
                    'locale' => $p['translations']['locale'],
                    'title' => $p['translations']['title'],
                    'slug' => $p['translations']['slug'],
                    'excerpt' => $p['translations']['excerpt'],
                    'content' => $p['translations']['content'],
                    'seo' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Helper to create a scoped 'Categories' taxonomy with default terms
        $createScopedCategories = function (string $scope, array $terms) use ($now) {
            // Find or create taxonomy per scope
            $taxonomyId = DB::table('taxonomies')
                ->where('slug', 'categories')
                ->where('scope', $scope)
                ->value('id');

            if (!$taxonomyId) {
                $taxonomyId = DB::table('taxonomies')->insertGetId([
                    'name' => 'Categories',
                    'slug' => 'categories',
                    'scope' => $scope,
                    'hierarchical' => true,
                    'multiple' => true,
                    'options' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $termIds = [];
            foreach ($terms as $t) {
                // Try to find term by translation slug within taxonomy
                $existingTermId = DB::table('term_translations as tt')
                    ->join('terms as tm', 'tt.term_id', '=', 'tm.id')
                    ->where('tm.taxonomy_id', $taxonomyId)
                    ->where('tt.locale', 'en')
                    ->where('tt.slug', $t['slug'])
                    ->value('tm.id');

                if ($existingTermId) {
                    $termIds[$t['slug']] = $existingTermId;
                    continue;
                }

                $termId = DB::table('terms')->insertGetId([
                    'taxonomy_id' => $taxonomyId,
                    'parent_id' => null,
                    'order' => 0,
                    'options' => null,
                    'deleted_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                DB::table('term_translations')->insert([
                    'term_id' => $termId,
                    'locale' => 'en',
                    'name' => $t['name'],
                    'slug' => $t['slug'],
                    'description' => null,
                    'seo' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $termIds[$t['slug']] = $termId;
            }

            return $termIds;
        };

        // Taxonomy and terms for posts (scoped)
        $postTermIds = $createScopedCategories('post', [
            ['name' => 'General', 'slug' => 'general'],
            ['name' => 'News', 'slug' => 'news'],
        ]);

        // Posts
        $posts = [
            [
                'title' => 'Welcome to our CMS',
                'slug' => 'welcome-to-our-cms',
                'excerpt' => 'First post introducing the CMS.',
                'content' => '<p>This is your first post. Edit or delete it, then start writing!</p>',
                'category' => 'general',
            ],
            [
                'title' => 'Product Launch',
                'slug' => 'product-launch',
                'excerpt' => 'Announcing our new product.',
                'content' => '<p>We are excited to launch our new product.</p>',
                'category' => 'news',
            ],
            [
                'title' => 'Tips and Tricks',
                'slug' => 'tips-and-tricks',
                'excerpt' => 'Useful tips for using the CMS.',
                'content' => '<p>Here are some tips to get the most out of the CMS.</p>',
                'category' => 'general',
            ],
        ];

        foreach ($posts as $i => $post) {
            // Find existing post by translation slug
            $existingPostId = DB::table('post_translations')
                ->where('locale', 'en')
                ->where('slug', $post['slug'])
                ->value('post_id');

            if ($existingPostId) {
                $postId = $existingPostId;
            } else {
                $postId = DB::table('posts')->insertGetId([
                    'author_id' => $authorId,
                    'featured_image_id' => null,
                    'status' => 'published',
                    'visibility' => 'public',
                    'published_at' => $now,
                    'is_pinned' => $i === 0,
                    'allow_comments' => true,
                    'options' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('post_translations')->insert([
                    'post_id' => $postId,
                    'locale' => 'en',
                    'title' => $post['title'],
                    'slug' => $post['slug'],
                    'excerpt' => $post['excerpt'],
                    'content' => $post['content'],
                    'seo' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Attach category if not already attached
            $catTermId = $postTermIds[$post['category']] ?? null;
            if ($catTermId) {
                $exists = DB::table('termables')
                    ->where('term_id', $catTermId)
                    ->where('termable_type', 'post')
                    ->where('termable_id', $postId)
                    ->exists();
                if (!$exists) {
                    DB::table('termables')->insert([
                        'term_id' => $catTermId,
                        'termable_id' => $postId,
                        'termable_type' => 'post',
                        'order' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        // Taxonomy and terms for news (scoped)
        $createScopedCategories('news', [
            ['name' => 'General', 'slug' => 'general'],
            ['name' => 'Announcements', 'slug' => 'announcements'],
        ]);

        // Taxonomy and terms for products (scoped)
        $createScopedCategories('product', [
            ['name' => 'General', 'slug' => 'general'],
            ['name' => 'Featured', 'slug' => 'featured'],
        ]);

        // Menu
        // Menu (idempotent)
        $menuId = DB::table('menus')->where('slug', 'main-menu')->value('id');
        if (!$menuId) {
            $menuId = DB::table('menus')->insertGetId([
                'name' => 'Main Menu',
                'slug' => 'main-menu',
                'location' => 'primary',
                'description' => 'Primary navigation',
                'options' => null,
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $menuItems = [
            ['title' => 'Home', 'url' => '/', 'order' => 0],
            ['title' => 'About', 'url' => '/about', 'order' => 1],
            ['title' => 'Blog', 'url' => '/blog', 'order' => 2],
        ];

        foreach ($menuItems as $mi) {
            $exists = DB::table('menu_items')
                ->where('menu_id', $menuId)
                ->where('url', $mi['url'])
                ->exists();
            if (!$exists) {
                DB::table('menu_items')->insert([
                    'menu_id' => $menuId,
                    'parent_id' => null,
                    'title' => $mi['title'],
                    'url' => $mi['url'],
                    'route' => null,
                    'parameters' => null,
                    'linkable_id' => null,
                    'linkable_type' => null,
                    'target' => '_self',
                    'icon' => null,
                    'is_active' => true,
                    'order' => $mi['order'],
                    'options' => null,
                    'deleted_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
