<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public themed routes
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/pages/{slug}', [PublicController::class, 'page'])->name('pages.show');
Route::get('/posts/{slug}', [PublicController::class, 'post'])->name('posts.show');
Route::get('/news/{slug}', [PublicController::class, 'news'])->name('news.show');
Route::get('/products/{slug}', [PublicController::class, 'product'])->name('products.show');

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
        Route::resource('pages', \App\Http\Controllers\Admin\PageController::class)->except(['show']);
        Route::get('pages/trash', [\App\Http\Controllers\Admin\PageController::class, 'trash'])->name('pages.trash');
        Route::post('pages/{id}/restore', [\App\Http\Controllers\Admin\PageController::class, 'restore'])->name('pages.restore');
        Route::delete('pages/{id}/force-delete', [\App\Http\Controllers\Admin\PageController::class, 'forceDelete'])->name('pages.force-delete');
        // Pages bulk actions
        Route::post('pages/bulk-destroy', [\App\Http\Controllers\Admin\PageController::class, 'bulkDestroy'])->name('pages.bulk-destroy');
        Route::post('pages/bulk-restore', [\App\Http\Controllers\Admin\PageController::class, 'bulkRestore'])->name('pages.bulk-restore');
        Route::delete('pages/bulk-force-delete', [\App\Http\Controllers\Admin\PageController::class, 'bulkForceDelete'])->name('pages.bulk-force-delete');
        Route::resource('posts', \App\Http\Controllers\Admin\PostController::class)->except(['show']);
        Route::get('posts/trash', [\App\Http\Controllers\Admin\PostController::class, 'trash'])->name('posts.trash');
        Route::post('posts/{id}/restore', [\App\Http\Controllers\Admin\PostController::class, 'restore'])->name('posts.restore');
        Route::delete('posts/{id}/force-delete', [\App\Http\Controllers\Admin\PostController::class, 'forceDelete'])->name('posts.force-delete');
        // Posts bulk actions
        Route::post('posts/bulk-destroy', [\App\Http\Controllers\Admin\PostController::class, 'bulkDestroy'])->name('posts.bulk-destroy');
        Route::post('posts/bulk-restore', [\App\Http\Controllers\Admin\PostController::class, 'bulkRestore'])->name('posts.bulk-restore');
        Route::delete('posts/bulk-force-delete', [\App\Http\Controllers\Admin\PostController::class, 'bulkForceDelete'])->name('posts.bulk-force-delete');
        Route::resource('news', \App\Http\Controllers\Admin\NewsController::class)->except(['show']);
        Route::get('news/trash', [\App\Http\Controllers\Admin\NewsController::class, 'trash'])->name('news.trash');
        Route::post('news/{id}/restore', [\App\Http\Controllers\Admin\NewsController::class, 'restore'])->name('news.restore');
        Route::delete('news/{id}/force-delete', [\App\Http\Controllers\Admin\NewsController::class, 'forceDelete'])->name('news.force-delete');
        // News bulk actions
        Route::post('news/bulk-destroy', [\App\Http\Controllers\Admin\NewsController::class, 'bulkDestroy'])->name('news.bulk-destroy');
        Route::post('news/bulk-restore', [\App\Http\Controllers\Admin\NewsController::class, 'bulkRestore'])->name('news.bulk-restore');
        Route::delete('news/bulk-force-delete', [\App\Http\Controllers\Admin\NewsController::class, 'bulkForceDelete'])->name('news.bulk-force-delete');
        Route::resource('products', \App\Http\Controllers\Admin\ProductController::class)->except(['show']);
        Route::get('products/trash', [\App\Http\Controllers\Admin\ProductController::class, 'trash'])->name('products.trash');
        Route::post('products/{id}/restore', [\App\Http\Controllers\Admin\ProductController::class, 'restore'])->name('products.restore');
        Route::delete('products/{id}/force-delete', [\App\Http\Controllers\Admin\ProductController::class, 'forceDelete'])->name('products.force-delete');
        // Products bulk actions
        Route::post('products/bulk-destroy', [\App\Http\Controllers\Admin\ProductController::class, 'bulkDestroy'])->name('products.bulk-destroy');
        Route::post('products/bulk-restore', [\App\Http\Controllers\Admin\ProductController::class, 'bulkRestore'])->name('products.bulk-restore');
        Route::delete('products/bulk-force-delete', [\App\Http\Controllers\Admin\ProductController::class, 'bulkForceDelete'])->name('products.bulk-force-delete');
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
    });

require __DIR__.'/auth.php';
