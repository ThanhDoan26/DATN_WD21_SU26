<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingHoldController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API v1 Bookings
Route::prefix('v1')->group(function () {
    Route::get('/bookings/{id}', [BookingHoldController::class, 'getBookingDetails'])->name('api.v1.bookings.show');
    Route::post('/bookings/release-hold-seats', [BookingHoldController::class, 'releaseHoldSeats'])->name('api.v1.bookings.release-hold-seats');
});

// Alias endpoints without version prefix
Route::get('/bookings/{id}', [BookingHoldController::class, 'getBookingDetails'])->name('api.bookings.show');
Route::post('/bookings/release-hold-seats', [BookingHoldController::class, 'releaseHoldSeats'])->name('api.bookings.release-hold-seats');
