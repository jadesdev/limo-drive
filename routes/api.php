<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\FleetController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
});

// Protected auth routes
Route::prefix('auth')->controller(AuthController::class)->middleware('auth:sanctum')->group(function () {
    Route::post('/logout', 'logout');
    Route::get('/user', 'user');
    Route::post('/refresh-token', 'refreshToken');
});

// Admin Profile
Route::prefix('profile')->controller(ProfileController::class)->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/', 'show');
    Route::put('/', 'update');
    Route::post('/password', 'passwordUpdate');
    Route::post('/upload-image', 'uploadImage');
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

// Booking
Route::controller(BookingController::class)->prefix('bookings')->group(function () {
    Route::post('/quote', 'getQuote');
    Route::post('/', 'store');
    Route::post('/confirm-payment', 'confirmPayment');
    Route::get('/{id}', 'show');
});

// Admin Routes
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    // Services
    Route::controller(ServiceController::class)->prefix('services')->group(function () {
        Route::get('/', 'adminIndex');
        Route::get('/{service}', 'adminShow');
        Route::post('/', 'store');
        Route::post('/{service}', 'update');
        Route::delete('/{service}', 'destroy');
    });

    // Fleets
    Route::controller(FleetController::class)->prefix('fleets')->group(function () {
        Route::get('/', 'adminIndex');
        Route::get('/{fleet}', 'adminShow');
        Route::post('/', 'store');
        Route::post('/{fleet}', 'update');
        Route::delete('/{fleet}', 'destroy');
        Route::patch('/reorder', 'reorder');
        Route::patch('/{fleet}/toggle-status', 'toggleStatus');
    });

    // Faqs
    Route::controller(FaqController::class)->prefix('faqs')->group(function () {
        Route::get('/', 'adminIndex');
        Route::get('/{faq}', 'adminShow');
        Route::post('/', 'store');
        Route::put('/{faq}', 'update');
        Route::delete('/{faq}', 'destroy');
        Route::patch('/{faq}/toggle-status', 'toggleStatus');
    });

    // Contacts
    Route::controller(ContactController::class)->prefix('contacts')->group(function () {
        Route::get('/', 'index');
        Route::get('/{contact}', 'show');
        Route::post('/{contact}/reply', 'reply');
        Route::delete('/{contact}', 'destroy');
    });

    // Drivers
    Route::apiResource('drivers', DriverController::class);

    // Bookings
    Route::controller(BookingController::class)->prefix('bookings')->group(function () {
        Route::get('/', 'index');
        Route::get('/{booking}', 'adminShow');
        Route::put('/{booking}/assign-driver', 'assignDriver');
        Route::put('/{booking}', 'update');
    });
    // Payments
    Route::apiResource('payments', PaymentController::class);

    // Admin-only: Customer management
    Route::prefix('customers')->controller(CustomerController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{customer}', 'show');
        Route::put('/{customer}', 'update');
        Route::delete('/{customer}', 'destroy');
    });
});

// Stripe Webhook
Route::post('/stripe-webhook', [StripeWebhookController::class, 'handle']);
