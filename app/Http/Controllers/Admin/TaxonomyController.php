<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Taxonomy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TaxonomyController extends Controller
{
    /**
     * List taxonomies, optionally filtered by scope (e.g., post, news, product).
     */
    public function index(Request $request)
    {
        $query = Taxonomy::query();

        if ($scope = $request->get('scope')) {
            $query->where('scope', $scope);
        }

        $taxonomies = $query
            ->orderBy('name')
            ->get()
            ->map(function (Taxonomy $t) {
                return [
                    'id' => $t->id,
                    'name' => $t->name,
                    'slug' => $t->slug,
                    'scope' => $t->scope,
                    'hierarchical' => (bool) $t->hierarchical,
                    'multiple' => (bool) $t->multiple,
                ];
            });

        return response()->json(['data' => $taxonomies]);
    }

    /**
     * Inertia page to manage taxonomies per scope.
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

        return Inertia::render('Admin/Taxonomies/Manage', [
            'scope' => $scope,
            'taxonomies' => $taxonomies,
        ]);
    }

    /**
     * Create a taxonomy (UI form submit).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'scope' => ['required', Rule::in(['post','news','product'])],
            'hierarchical' => ['sometimes', 'boolean'],
            'multiple' => ['sometimes', 'boolean'],
        ]);

        $slug = Str::slug($validated['slug'] ?? $validated['name']);

        // Ensure uniqueness per (slug, scope)
        $exists = Taxonomy::where('slug', $slug)->where('scope', $validated['scope'])->exists();
        if ($exists) {
            return back()->withErrors(['slug' => 'The slug has already been taken for this scope.'])->withInput();
        }

        Taxonomy::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'scope' => $validated['scope'],
            'hierarchical' => (bool)($validated['hierarchical'] ?? false),
            'multiple' => (bool)($validated['multiple'] ?? true),
        ]);

        return back()->with('success', 'Taxonomy created.');
    }
}
