<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CinemaStaffDashboardController;

Route::middleware(['auth', 'role:STAFF'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [CinemaStaffDashboardController::class, 'index'])->name('dashboard');
});
