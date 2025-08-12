<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public themed routes
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/pages/{slug}', [PublicController::class, 'page'])->middleware('module:pages')->name('pages.show');
Route::get('/posts/{slug}', [PublicController::class, 'post'])->middleware('module:posts')->name('posts.show');
Route::get('/news/{slug}', [PublicController::class, 'news'])->middleware('module:news')->name('news.show');
Route::get('/products/{slug}', [PublicController::class, 'product'])->middleware('module:products')->name('products.show');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin routes
Route::middleware(['auth', 'verified', 'role:Admin|Editor'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        // Register bulk DELETE first to avoid conflict with resource DELETE {id}
        Route::delete('pages/bulk-force-delete', [\App\Http\Controllers\Admin\PageController::class, 'bulkForceDelete'])->middleware('module:pages')->name('pages.bulk-force-delete');
        Route::resource('pages', \App\Http\Controllers\Admin\PageController::class)->except(['show'])->middleware('module:pages');
        Route::get('pages/trash', [\App\Http\Controllers\Admin\PageController::class, 'trash'])->middleware('module:pages')->name('pages.trash');
        Route::post('pages/{id}/restore', [\App\Http\Controllers\Admin\PageController::class, 'restore'])->middleware('module:pages')->name('pages.restore');
        Route::delete('pages/{id}/force-delete', [\App\Http\Controllers\Admin\PageController::class, 'forceDelete'])->middleware('module:pages')->name('pages.force-delete');
        // Pages bulk actions
        Route::post('pages/bulk-destroy', [\App\Http\Controllers\Admin\PageController::class, 'bulkDestroy'])->middleware('module:pages')->name('pages.bulk-destroy');
        Route::post('pages/bulk-restore', [\App\Http\Controllers\Admin\PageController::class, 'bulkRestore'])->middleware('module:pages')->name('pages.bulk-restore');
        // Register bulk DELETE first to avoid conflict with resource DELETE {id}
        Route::delete('posts/bulk-force-delete', [\App\Http\Controllers\Admin\PostController::class, 'bulkForceDelete'])->middleware('module:posts')->name('posts.bulk-force-delete');
        Route::resource('posts', \App\Http\Controllers\Admin\PostController::class)->except(['show'])->middleware('module:posts');
        Route::get('posts/trash', [\App\Http\Controllers\Admin\PostController::class, 'trash'])->middleware('module:posts')->name('posts.trash');
        Route::post('posts/{id}/restore', [\App\Http\Controllers\Admin\PostController::class, 'restore'])->middleware('module:posts')->name('posts.restore');
        Route::delete('posts/{id}/force-delete', [\App\Http\Controllers\Admin\PostController::class, 'forceDelete'])->middleware('module:posts')->name('posts.force-delete');
        // Posts bulk actions
        Route::post('posts/bulk-destroy', [\App\Http\Controllers\Admin\PostController::class, 'bulkDestroy'])->middleware('module:posts')->name('posts.bulk-destroy');
        Route::post('posts/bulk-restore', [\App\Http\Controllers\Admin\PostController::class, 'bulkRestore'])->middleware('module:posts')->name('posts.bulk-restore');
        // Register bulk DELETE first to avoid conflict with resource DELETE {id}
        Route::delete('news/bulk-force-delete', [\App\Http\Controllers\Admin\NewsController::class, 'bulkForceDelete'])->middleware('module:news')->name('news.bulk-force-delete');
        Route::resource('news', \App\Http\Controllers\Admin\NewsController::class)->except(['show'])->middleware('module:news');
        Route::get('news/trash', [\App\Http\Controllers\Admin\NewsController::class, 'trash'])->middleware('module:news')->name('news.trash');
        Route::post('news/{id}/restore', [\App\Http\Controllers\Admin\NewsController::class, 'restore'])->middleware('module:news')->name('news.restore');
        Route::delete('news/{id}/force-delete', [\App\Http\Controllers\Admin\NewsController::class, 'forceDelete'])->middleware('module:news')->name('news.force-delete');
        // News bulk actions
        Route::post('news/bulk-destroy', [\App\Http\Controllers\Admin\NewsController::class, 'bulkDestroy'])->middleware('module:news')->name('news.bulk-destroy');
        Route::post('news/bulk-restore', [\App\Http\Controllers\Admin\NewsController::class, 'bulkRestore'])->middleware('module:news')->name('news.bulk-restore');
        // Register bulk DELETE first to avoid conflict with resource DELETE {id}
        Route::delete('products/bulk-force-delete', [\App\Http\Controllers\Admin\ProductController::class, 'bulkForceDelete'])->middleware('module:products')->name('products.bulk-force-delete');
        Route::resource('products', \App\Http\Controllers\Admin\ProductController::class)->except(['show'])->middleware('module:products');
        Route::get('products/trash', [\App\Http\Controllers\Admin\ProductController::class, 'trash'])->middleware('module:products')->name('products.trash');
        Route::post('products/{id}/restore', [\App\Http\Controllers\Admin\ProductController::class, 'restore'])->middleware('module:products')->name('products.restore');
        Route::delete('products/{id}/force-delete', [\App\Http\Controllers\Admin\ProductController::class, 'forceDelete'])->middleware('module:products')->name('products.force-delete');
        // Products bulk actions
        Route::post('products/bulk-destroy', [\App\Http\Controllers\Admin\ProductController::class, 'bulkDestroy'])->middleware('module:products')->name('products.bulk-destroy');
        Route::post('products/bulk-restore', [\App\Http\Controllers\Admin\ProductController::class, 'bulkRestore'])->middleware('module:products')->name('products.bulk-restore');
        Route::resource('menus', \App\Http\Controllers\Admin\MenuController::class)->except(['show']);
        Route::post('menus/{menu}/sync-items', [\App\Http\Controllers\Admin\MenuItemSyncController::class, 'store'])->name('menus.sync-items');
        Route::post('media/upload', [\App\Http\Controllers\Admin\MediaUploadController::class, 'store'])->name('media.upload');

        // Users
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['show']);

        // Roles (Admin only)
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class)
            ->except(['show'])
            ->middleware('role:Admin');

        // Themes management
        Route::get('themes', [\App\Http\Controllers\Admin\ThemeController::class, 'index'])->name('themes.index');
        Route::post('themes/activate', [\App\Http\Controllers\Admin\ThemeController::class, 'activate'])->name('themes.activate');

        // Modules management (Admin only)
        Route::resource('modules', \App\Http\Controllers\Admin\ModuleController::class)
            ->only(['index','update'])
            ->middleware('role:Admin');
    });

require __DIR__.'/auth.php';
