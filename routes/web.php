<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MovieController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MovieController::class, 'welcome']);
Route::get('/phim-dang-chieu', [MovieController::class, 'currentMovies'])->name('movies.current');
Route::get('/phim-sap-chieu', [MovieController::class, 'upcomingMovies'])->name('movies.upcoming');

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

// Booking routes
Route::controller(\App\Http\Controllers\BookingController::class)->group(function () {
    // Bước 1: Chọn cụm rạp
    Route::get('/booking/movie/{movie}/cinema', 'selectCinema')->name('booking.select-cinema');

    // Bước 2 & 3: Chọn ngày và suất chiếu
    Route::get('/booking/movie/{movie}/cinema/{cinema}/dates', 'selectDatesAndShowtimes')->name('booking.select-dates-showtimes');

    // Bước 4: Chọn ghế
    Route::get('/booking/showtime/{showtime}/seats', 'selectSeats')->middleware('auth')->name('booking.select-seats');
});

// Booking API routes
Route::prefix('api/booking')->controller(\App\Http\Controllers\BookingController::class)->group(function () {
    // Bước 2: Lấy danh sách ngày chiếu
    Route::get('/dates', 'getDates')->name('api.booking.dates');

    // Bước 3: Lấy danh sách suất chiếu
    Route::get('/showtimes', 'getShowtimes')->name('api.booking.showtimes');
});

// Frontend API/AJAX routes
Route::post('/api/apply-coupon', [\App\Http\Controllers\CheckoutController::class, 'applyCoupon'])->name('api.apply-coupon');
Route::get('/checkout', [\App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout');

require __DIR__.'/auth.php';
