<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CinemaManagerDashboardController;

Route::middleware(['auth', 'role:MANAGER'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [CinemaManagerDashboardController::class, 'index'])->name('dashboard');
});
