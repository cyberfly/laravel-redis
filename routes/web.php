<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RedisController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Redis Demo Routes
Route::prefix('redis-demo')->group(function () {
    Route::get('/products', [RedisController::class, 'getProducts']);
    Route::get('/rate-limited', [RedisController::class, 'rateLimitedEndpoint']);
    Route::get('/page-view', [RedisController::class, 'pageView']);
    Route::get('/clear-cache', [RedisController::class, 'clearCache']);
    Route::get('/cache-stats', [RedisController::class, 'getCacheStats']);
});

require __DIR__.'/auth.php';
