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
        Route::resource('posts', \App\Http\Controllers\Admin\PostController::class)->except(['show']);
        Route::resource('news', \App\Http\Controllers\Admin\NewsController::class)->except(['show']);
        Route::resource('products', \App\Http\Controllers\Admin\ProductController::class)->except(['show']);
        Route::resource('menus', \App\Http\Controllers\Admin\MenuController::class)->except(['show']);
        Route::post('menus/{menu}/sync-items', [\App\Http\Controllers\Admin\MenuItemSyncController::class, 'store'])->name('menus.sync-items');
        Route::post('media/upload', [\App\Http\Controllers\Admin\MediaUploadController::class, 'store'])->name('media.upload');

        // Themes management
        Route::get('themes', [\App\Http\Controllers\Admin\ThemeController::class, 'index'])->name('themes.index');
        Route::post('themes/activate', [\App\Http\Controllers\Admin\ThemeController::class, 'activate'])->name('themes.activate');
    });

require __DIR__.'/auth.php';
