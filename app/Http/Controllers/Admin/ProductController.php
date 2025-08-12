<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Product::query()->with(['translation']);

        if ($search = $request->string('search')->toString()) {
            $query->whereHas('translations', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            })->orWhere('sku', 'like', "%{$search}%");
        }

        $products = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString()
            ->through(function (Product $product) {
                return [
                    'id' => $product->id,
                    'name' => optional($product->translation)->name,
                    'slug' => optional($product->translation)->slug,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'status' => $product->status,
                    'visibility' => $product->visibility,
                    'stock_qty' => $product->stock_qty,
                ];
            });

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'filters' => [
                'search' => $request->get('search'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Products/Create');
    }

    public function store(Request $request)
    {
        $locale = app()->getLocale();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable','string','max:255', Rule::unique('product_translations','slug')->where('locale', $locale)],
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'sku' => 'required|string|max:100|unique:products,sku',
            'type' => 'required|string|in:simple',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'stock_qty' => 'required|integer|min:0',
            'manage_stock' => 'boolean',
            'stock_status' => 'required|in:in_stock,out_of_stock,backorder',
            'backorder' => 'boolean',
            'status' => 'required|in:draft,scheduled,published,archived',
            'visibility' => 'required|in:public,private',
            'published_at' => 'nullable|date',
            'featured_image' => 'nullable|image|max:5120',
        ]);

        $slug = $data['slug'] ?? Str::slug($data['name']);

        $product = Product::create([
            'author_id' => $request->user()->id,
            'type' => $data['type'],
            'sku' => $data['sku'],
            'price' => $data['price'],
            'sale_price' => $data['sale_price'] ?? null,
            'currency' => strtoupper($data['currency']),
            'stock_qty' => $data['stock_qty'],
            'manage_stock' => $data['manage_stock'] ?? true,
            'stock_status' => $data['stock_status'],
            'backorder' => $data['backorder'] ?? false,
            'status' => $data['status'],
            'visibility' => $data['visibility'],
            'published_at' => $data['published_at'] ?? null,
        ]);

        if ($request->hasFile('featured_image')) {
            $media = MediaService::storeFromUpload($request->file('featured_image'), $request->user()->id, 'uploads/'.date('Y/m'));
            $product->update(['featured_image_id' => $media->id]);
        }

        ProductTranslation::create([
            'product_id' => $product->id,
            'locale' => $locale,
            'name' => $data['name'],
            'slug' => $slug,
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product): Response
    {
        $product->load(['translation', 'featuredImage']);

        return Inertia::render('Admin/Products/Edit', [
            'product' => [
                'id' => $product->id,
                'name' => optional($product->translation)->name,
                'slug' => optional($product->translation)->slug,
                'short_description' => optional($product->translation)->short_description,
                'description' => optional($product->translation)->description,
                'sku' => $product->sku,
                'type' => $product->type,
                'price' => (string) $product->price,
                'sale_price' => $product->sale_price !== null ? (string) $product->sale_price : null,
                'currency' => $product->currency,
                'stock_qty' => $product->stock_qty,
                'manage_stock' => (bool) $product->manage_stock,
                'stock_status' => $product->stock_status,
                'backorder' => (bool) $product->backorder,
                'status' => $product->status,
                'visibility' => $product->visibility,
                'published_at' => optional($product->published_at)?->format('Y-m-d\\TH:i'),
                'featured_image_url' => optional($product->featuredImage)?->url(),
            ],
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $locale = app()->getLocale();
        $translation = $product->translations()->where('locale', $locale)->first();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable','string','max:255',
                Rule::unique('product_translations','slug')
                    ->where('locale', $locale)
                    ->ignore(optional($translation)->id)
            ],
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'sku' => ['required','string','max:100', Rule::unique('products','sku')->ignore($product->id)],
            'type' => 'required|string|in:simple',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'stock_qty' => 'required|integer|min:0',
            'manage_stock' => 'boolean',
            'stock_status' => 'required|in:in_stock,out_of_stock,backorder',
            'backorder' => 'boolean',
            'status' => 'required|in:draft,scheduled,published,archived',
            'visibility' => 'required|in:public,private',
            'published_at' => 'nullable|date',
            'featured_image' => 'nullable|image|max:5120',
        ]);

        $slug = $data['slug'] ?? Str::slug($data['name']);

        $product->update([
            'sku' => $data['sku'],
            'type' => $data['type'],
            'price' => $data['price'],
            'sale_price' => $data['sale_price'] ?? null,
            'currency' => strtoupper($data['currency']),
            'stock_qty' => $data['stock_qty'],
            'manage_stock' => $data['manage_stock'] ?? true,
            'stock_status' => $data['stock_status'],
            'backorder' => $data['backorder'] ?? false,
            'status' => $data['status'],
            'visibility' => $data['visibility'],
            'published_at' => $data['published_at'] ?? null,
        ]);

        if ($request->hasFile('featured_image')) {
            $media = MediaService::storeFromUpload($request->file('featured_image'), $request->user()->id, 'uploads/'.date('Y/m'));
            $product->update(['featured_image_id' => $media->id]);
        }

        if ($translation) {
            $translation->update([
                'name' => $data['name'],
                'slug' => $slug,
                'short_description' => $data['short_description'] ?? null,
                'description' => $data['description'] ?? null,
            ]);
        } else {
            ProductTranslation::create([
                'product_id' => $product->id,
                'locale' => $locale,
                'name' => $data['name'],
                'slug' => $slug,
                'short_description' => $data['short_description'] ?? null,
                'description' => $data['description'] ?? null,
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    /**
     * List trashed products
     */
    public function trash(Request $request): Response
    {
        $query = Product::onlyTrashed()->with(['translation']);

        if ($search = $request->string('search')->toString()) {
            $query->whereHas('translations', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            })->orWhere('sku', 'like', "%{$search}%");
        }

        $products = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString()
            ->through(function (Product $product) {
                return [
                    'id' => $product->id,
                    'name' => optional($product->translation)->name,
                    'slug' => optional($product->translation)->slug,
                    'sku' => $product->sku,
                    'deleted_at' => optional($product->deleted_at)?->toDateTimeString(),
                ];
            });

        return Inertia::render('Admin/Products/Trash', [
            'products' => $products,
            'filters' => [
                'search' => $request->get('search'),
            ],
        ]);
    }

    /** Restore a trashed product */
    public function restore($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();
        return redirect()->route('admin.products.trash')->with('success', 'Product restored.');
    }

    /** Permanently delete a product */
    public function forceDelete($id)
    {
        $product = Product::onlyTrashed()->with('translations')->findOrFail($id);
        $product->translations()->delete();
        $product->forceDelete();
        return redirect()->route('admin.products.trash')->with('success', 'Product permanently deleted.');
    }

    /** Bulk soft delete (move to trash) */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required','array'],
            'ids.*' => ['integer','exists:products,id'],
        ])['ids'];

        Product::whereIn('id', $ids)->delete();
        return back()->with('success', 'Selected products moved to trash.');
    }

    /** Bulk restore */
    public function bulkRestore(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required','array'],
            'ids.*' => ['integer'],
        ])['ids'];

        Product::onlyTrashed()->whereIn('id', $ids)->restore();
        return back()->with('success', 'Selected products restored.');
    }

    /** Bulk permanent delete */
    public function bulkForceDelete(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required','array'],
            'ids.*' => ['integer'],
        ])['ids'];

        $products = Product::onlyTrashed()->with('translations')->whereIn('id', $ids)->get();
        foreach ($products as $p) {
            $p->translations()->delete();
            $p->forceDelete();
        }
        return back()->with('success', 'Selected products permanently deleted.');
    }
}
