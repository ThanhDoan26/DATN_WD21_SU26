<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CinemaStaffDashboardController;

Route::middleware(['auth', 'role:STAFF'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [CinemaStaffDashboardController::class, 'index'])->name('dashboard');
    Route::get('/ticket-search', [CinemaStaffDashboardController::class, 'searchForm'])->name('ticket.search');
    Route::get('/ticket-lookup', [CinemaStaffDashboardController::class, 'lookup'])->name('ticket.lookup');
    Route::post('/ticket-checkin', [CinemaStaffDashboardController::class, 'checkIn'])->name('ticket.checkin');
});
