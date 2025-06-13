<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\FleetController;
use App\Http\Controllers\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::controller(ServiceController::class)->prefix('services')->group(function () {
    Route::get('/', 'index');
    Route::get('/{slug}', 'show');
});

Route::controller(FleetController::class)->prefix('fleets')->group(function () {
    Route::get('/', 'index');
    Route::get('/{slug}', 'show');
});

// Contact message
Route::post('/contact', [ContactController::class, 'store'])->middleware(['throttle:6,1']);

// Faqs
Route::get('/faqs', [FaqController::class, 'index']);
