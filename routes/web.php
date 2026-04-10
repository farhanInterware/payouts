<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\ProfileController as UserProfileController;
use App\Http\Controllers\User\TransactionController as UserTransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes (guest only)
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
});
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Payment result routes (public, no auth required)
Route::post('/payment/result', [PaymentController::class, 'result'])->name('payment.result');
Route::get('/payment/success', function () {
    return view('payment.success');
})->name('payment.success');
Route::get('/payment/fail', function () {
    return view('payment.fail');
})->name('payment.fail');
Route::get('/payment/processing', function () {
    return view('payment.processing');
})->name('payment.processing');

// User routes (authenticated)
Route::middleware(['auth'])->group(function () {
    // User Dashboard
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
        
        // Transactions (view only — withdrawals are created by admins)
        Route::resource('transactions', UserTransactionController::class)->only(['index', 'show']);
        Route::post('transactions/{id}/check-status', [UserTransactionController::class, 'checkStatus'])->name('transactions.check-status');
        
        // Profile
        Route::get('/profile', [UserProfileController::class, 'show'])->name('profile.show');
        Route::post('/profile/password', [UserProfileController::class, 'updatePassword'])->name('profile.password');
    });
});

// Admin routes (authenticated + admin)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Users
    Route::resource('users', AdminUserController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/users/{id}/password', [AdminProfileController::class, 'changeUserPassword'])->name('users.password');
    
    // Transactions
    Route::resource('transactions', AdminTransactionController::class)->only(['index', 'create', 'store', 'show']);
    
    // Profile
    Route::get('/profile', [AdminProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password');
});

