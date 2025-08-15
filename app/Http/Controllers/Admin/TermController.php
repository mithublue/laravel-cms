<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Taxonomy;
use App\Models\Term;
use App\Models\TermTranslation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class TermController extends Controller
{
    /**
     * List terms for a given taxonomy and scope. Supports optional search.
     */
    public function index(Request $request)
    {
        $request->validate([
            'scope' => ['required','in:post,news,product'],
            'taxonomy' => ['required','string'],
            'search' => ['nullable','string'],
        ]);

        $taxonomy = Taxonomy::where('scope', $request->get('scope'))
            ->where('slug', $request->get('taxonomy'))
            ->firstOrFail();

        $locale = app()->getLocale();
        $search = $request->string('search')->toString();

        $query = Term::query()
            ->with(['translation' => function ($q) use ($locale) { $q->where('locale', $locale); }])
            ->where('taxonomy_id', $taxonomy->id)
            ->orderBy('order')
            ->orderBy('id');

        if ($search) {
            $query->whereHas('translations', function ($q) use ($search, $locale) {
                $q->where('locale', $locale)
                  ->where(function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('slug', 'like', "%{$search}%");
                  });
            });
        }

        $terms = $query->get()->map(function (Term $t) {
            return [
                'id' => $t->id,
                'parent_id' => $t->parent_id,
                'name' => optional($t->translation)->name,
                'slug' => optional($t->translation)->slug,
            ];
        });

        return response()->json(['data' => $terms]);
    }

    /**
     * Create a new term within a taxonomy (by slug and scope) with translation for current locale.
     */
    public function store(Request $request)
    {
        $locale = app()->getLocale();

        $data = $request->validate([
            'scope' => ['required','in:post,news,product'],
            'taxonomy' => ['required','string'],
            'name' => ['required','string','max:255'],
            'slug' => ['nullable','string','max:255'],
            'parent_id' => ['nullable','integer','exists:terms,id'],
        ]);

        $taxonomy = Taxonomy::where('scope', $data['scope'])
            ->where('slug', $data['taxonomy'])
            ->firstOrFail();

        // Ensure parent belongs to same taxonomy if provided
        if (!empty($data['parent_id'])) {
            $parent = Term::where('id', $data['parent_id'])->where('taxonomy_id', $taxonomy->id)->first();
            if (!$parent) {
                return response()->json(['message' => 'Parent term must belong to the same taxonomy.'], 422);
            }
        }

        // Handle optional slug safely when not provided in validated data
        $slugInput = $data['slug'] ?? null;
        $slug = $slugInput ? Str::slug($slugInput) : Str::slug($data['name']);

        // Ensure slug uniqueness per taxonomy + locale by auto-incrementing suffix if needed
        $baseSlug = $slug;
        $suffix = 2;
        while (
            TermTranslation::where('slug', $slug)
                ->where('locale', $locale)
                ->whereHas('term', function ($q) use ($taxonomy) { $q->where('taxonomy_id', $taxonomy->id); })
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        $term = Term::create([
            'taxonomy_id' => $taxonomy->id,
            'parent_id' => $data['parent_id'] ?? null,
            'order' => 0,
        ]);

        TermTranslation::create([
            'term_id' => $term->id,
            'locale' => $locale,
            'name' => $data['name'],
            'slug' => $slug,
            'description' => null,
            'seo' => null,
        ]);

        return response()->json([
            'data' => [
                'id' => $term->id,
                'parent_id' => $term->parent_id,
                'name' => $data['name'],
                'slug' => $slug,
            ]
        ], 201);
    }
}
