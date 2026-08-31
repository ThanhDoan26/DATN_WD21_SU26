<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingHoldController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API v1 Bookings
Route::prefix('v1')->group(function () {
    Route::post('/bookings/release-hold-seats', [BookingHoldController::class, 'releaseHoldSeats'])->name('api.v1.bookings.release-hold-seats');
});

// Alias endpoint without version prefix
Route::post('/bookings/release-hold-seats', [BookingHoldController::class, 'releaseHoldSeats'])->name('api.bookings.release-hold-seats');
