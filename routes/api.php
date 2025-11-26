<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Payment callback (no auth required - called by payment provider)
Route::post('/payment/callback', [PaymentController::class, 'callback'])->name('api.payment.callback');

// Authenticated API routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/payment/payout', [PaymentController::class, 'createPayout'])->name('api.payment.payout');
    Route::post('/payment/status', [PaymentController::class, 'checkStatus'])->name('api.payment.status');
});

