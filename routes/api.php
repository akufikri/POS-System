<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ShiftController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', \App\Http\Middleware\EnsureTenantScope::class])->group(function () {
    // Shift management
    Route::get('/shifts/current', [ShiftController::class, 'current']);
    Route::post('/shifts', [ShiftController::class, 'store']);
    Route::post('/shifts/{shift}/close', [ShiftController::class, 'close']);

    // Payment status check
    Route::get('/payments/status/{transactionId}', [PaymentController::class, 'status']);
});

// Midtrans webhook (no auth, signature verified)
Route::post('/payments/webhook', [PaymentController::class, 'webhook']);
