<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working',
    ]);
});

// cron url
Route::get('/cron', function () {
    Artisan::call('queue:work', ['--stop-when-empty' => true]);
    Artisan::call('queue:retry all');

    return response()->json(['message' => 'Queue processed successfully']);
});
