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
Route::prefix('redis-demo')->middleware('auth')->group(function () {
    Route::get('/products', [RedisController::class, 'getProducts']);
    Route::get('/rate-limited', [RedisController::class, 'rateLimitedEndpoint']);
    Route::get('/page-view', [RedisController::class, 'pageView']);
    Route::get('/clear-cache', [RedisController::class, 'clearCache']);
    Route::get('/cache-stats', [RedisController::class, 'getCacheStats']);
    Route::get('/transactions', function () {
        return view('redis-transaction');
    })->name('redis.transactions');
});

Route::middleware('auth')->group(function () {
    Route::post('/redis/purchase', [RedisController::class, 'processPurchase']);
    Route::post('/redis/setup-transaction-test', [RedisController::class, 'setupTransactionTest']);
});

// Leaderboard routes
Route::middleware('auth')->group(function () {
    Route::get('/leaderboard', function () {
        return view('leaderboard');
    })->name('leaderboard');
    Route::post('/leaderboard/update', [RedisController::class, 'updateLeaderboard']);
    Route::get('/leaderboard/nearby/{user_id}', [RedisController::class, 'getNearbyRankings']);
});

require __DIR__.'/auth.php';
