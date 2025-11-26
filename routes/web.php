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

// Authentication routes
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);

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
        
        // Transactions
        Route::resource('transactions', UserTransactionController::class);
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
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [AdminUserController::class, 'show'])->name('users.show');
    Route::post('/users/{id}/password', [AdminProfileController::class, 'changeUserPassword'])->name('users.password');
    
    // Transactions
    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{id}', [AdminTransactionController::class, 'show'])->name('transactions.show');
    
    // Profile
    Route::get('/profile', [AdminProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password');
});

