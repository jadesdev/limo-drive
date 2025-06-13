<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\FleetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
});

// Protected auth routes
Route::prefix('auth')->controller(AuthController::class)->middleware('auth:sanctum')->group(function () {
    Route::post('/logout', 'logout');
    // Route::post('/logout-all', 'logoutAll');
    Route::get('/user', 'user');
    Route::post('/refresh-token', 'refreshToken');
});

// Admin Profile
Route::prefix('profile')->controller(ProfileController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('/', 'show');
    Route::put('/', 'update');
    Route::post('/password', 'passwordUpdate');
});

// Services
Route::controller(ServiceController::class)->prefix('services')->group(function () {
    Route::get('/', 'index');
    Route::get('/{slug}', 'show');
});

// Fleets
Route::controller(FleetController::class)->prefix('fleets')->group(function () {
    Route::get('/', 'index');
    Route::get('/{slug}', 'show');
});

// Contact message
Route::post('/contact', [ContactController::class, 'store'])->middleware(['throttle:6,1']);

// Faqs
Route::get('/faqs', [FaqController::class, 'index']);

// Admin Routes
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::controller(ServiceController::class)->prefix('services')->group(function () {
        Route::get('/', 'adminIndex');
        Route::get('/{service}', 'adminShow');
        Route::post('/', 'store');
        Route::put('/{service}', 'update');
        Route::delete('/{service}', 'destroy');
    });

    Route::controller(FleetController::class)->prefix('fleets')->group(function () {
        Route::get('/', 'adminIndex');
        Route::get('/{fleet}', 'adminShow');
        Route::post('/', 'store');
        Route::post('/{fleet}', 'update');
        Route::delete('/{fleet}', 'destroy');
        Route::patch('/reorder', 'reorder');
        Route::patch('/{fleet}/toggle-status', 'toggleStatus');
    });

    Route::controller(FaqController::class)->prefix('faqs')->group(function () {
        Route::get('/', 'adminIndex');
        Route::get('/{faq}', 'adminShow');
        Route::post('/', 'store');
        Route::put('/{faq}', 'update');
        Route::delete('/{faq}', 'destroy');
        Route::patch('/{faq}/toggle-status', 'toggleStatus');
    });

    Route::controller(ContactController::class)->prefix('contacts')->group(function () {
        Route::get('/', 'adminIndex');
        Route::get('/{contact}', 'adminShow');
        Route::put('/{contact}', 'update');
        Route::delete('/{contact}', 'destroy');
    });
});
