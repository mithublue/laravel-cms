<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Taxonomy;
use App\Models\Term;
use App\Models\TermTranslation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TermController extends Controller
{
    /**
     * Inertia page to manage terms per scope (choose taxonomy, list terms, delete, etc.).
     */
    public function manage(Request $request)
    {
        $scope = $request->query('scope');
        $validScopes = ['post', 'news', 'product'];
        if ($scope && !in_array($scope, $validScopes, true)) {
            $scope = null; // ignore invalid
        }

        $taxonomies = Taxonomy::when($scope, fn($q) => $q->where('scope', $scope))
            ->orderBy('name')
            ->get(['id','name','slug','scope','hierarchical','multiple']);

        return Inertia::render('Admin/Terms/Manage', [
            'scope' => $scope,
            'taxonomies' => $taxonomies,
        ]);
    }

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

    /**
     * Delete a term and unsync it from any related items. Soft delete to preserve history.
     */
    public function destroy(Request $request, Term $term)
    {
        $request->validate([
            'scope' => ['required','in:post,news,product'],
        ]);

        // Ensure the term belongs to the requested scope
        $term->load('taxonomy');
        if (!$term->taxonomy || $term->taxonomy->scope !== $request->string('scope')->toString()) {
            abort(404);
        }

        // Detach relations (unsync) then soft delete
        $term->posts()->detach();
        $term->news()->detach();
        $term->products()->detach();

        $term->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Term deleted.']);
        }

        return back()->with('success', 'Term deleted.');
    }
}
