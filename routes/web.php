<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.dashboard'));

// Cache clear endpoint (hit this after deploy)
Route::get('/admin/clear-cache', function () {
    \Artisan::call('cache:clear');
    \Artisan::call('route:clear');
    \Artisan::call('config:clear');
    \Artisan::call('view:clear');
    if (!file_exists(public_path('storage'))) {
        \Artisan::call('storage:link');
    }
    return 'All caches cleared! <a href="/admin/dashboard">Go to Admin</a>';
})->middleware(['auth', 'admin']);

// Admin auth
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login']);
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::post('posts/upload-chunk', [PostController::class, 'uploadChunk'])->name('posts.upload-chunk');
        Route::resource('posts', PostController::class)->except(['show']);
        Route::post('posts/{post}/publish', [PostController::class, 'publish'])->name('posts.publish');
        Route::post('posts/{post}/unpublish', [PostController::class, 'unpublish'])->name('posts.unpublish');
    });
});
