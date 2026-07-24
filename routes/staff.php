<?php

use Illuminate\Support\Facades\Route;




use App\Http\Controllers\CinemaStaffDashboardController;

Route::middleware(['auth', 'role:STAFF'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [CinemaStaffDashboardController::class, 'index'])->name('dashboard');
    Route::get('/ticket-search', [CinemaStaffDashboardController::class, 'searchForm'])->name('ticket.search');
    Route::get('/ticket-lookup', [CinemaStaffDashboardController::class, 'lookup'])->name('ticket.lookup');
    Route::post('/ticket-checkin', [CinemaStaffDashboardController::class, 'checkIn'])->name('ticket.checkin');
    Route::get('/ticket-print/{type}/{id}', [CinemaStaffDashboardController::class, 'printTicket'])->name('ticket.print');

    // Walk-in Booking
    Route::get('/walk-in/movies', [\App\Http\Controllers\Staff\WalkInBookingController::class, 'movies'])->name('walkin.movies');
    Route::get('/walk-in/movie/{movie}/dates', [\App\Http\Controllers\Staff\WalkInBookingController::class, 'selectDatesAndShowtimes'])->name('walkin.dates');
    Route::get('/walk-in/showtime/{showtime}/seats', [\App\Http\Controllers\Staff\WalkInBookingController::class, 'selectSeats'])->name('walkin.seats');
    Route::get('/walk-in/checkout', [\App\Http\Controllers\Staff\WalkInBookingController::class, 'checkout'])->name('walkin.checkout');
    Route::post('/walk-in/reserve', [\App\Http\Controllers\Staff\WalkInBookingController::class, 'reserve'])->name('walkin.reserve');
    Route::get('/walk-in/success', [\App\Http\Controllers\Staff\WalkInBookingController::class, 'success'])->name('walkin.success');
});

