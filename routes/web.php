<?php

use App\Http\Controllers\ProfileController;
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

// Load admin routes
require __DIR__.'/admin.php';

// Frontend API/AJAX routes
Route::post('/api/apply-coupon', [\App\Http\Controllers\CheckoutController::class, 'applyCoupon'])->name('api.apply-coupon');
Route::get('/checkout', [\App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout');

require __DIR__.'/auth.php';
