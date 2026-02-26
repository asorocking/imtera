<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\YandexReviewsController;
use App\Http\Controllers\YandexSettingsController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');
    Route::get('/reviews', [YandexReviewsController::class, 'index'])->name('reviews.index');
//    Route::post('/reviews/refresh', [YandexReviewsController::class, 'refresh'])->name('reviews.refresh');
    Route::get('/settings/yandex', [YandexSettingsController::class, 'index'])->name('settings.yandex');
    Route::post('/settings/yandex', [YandexSettingsController::class, 'store'])->name('settings.yandex.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
