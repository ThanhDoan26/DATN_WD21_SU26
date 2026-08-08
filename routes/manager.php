<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CinemaManagerDashboardController;

use App\Http\Controllers\Manager\RoomController;
use App\Http\Controllers\Manager\ShowtimeController;
use App\Http\Controllers\Manager\ComboController;

Route::middleware(['auth', 'role:MANAGER'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [CinemaManagerDashboardController::class, 'index'])->name('dashboard');

    // Quản lý Phòng chiếu (Manager)
    Route::get('rooms/trashed', [RoomController::class, 'trashed'])->name('rooms.trashed');
    Route::post('rooms/{id}/restore', [RoomController::class, 'restore'])->name('rooms.restore');
    Route::delete('rooms/{id}/force-delete', [RoomController::class, 'forceDelete'])->name('rooms.forceDelete');
    Route::post('rooms/{room}/seats/{seat}/toggle-status', [RoomController::class, 'toggleSeatStatus'])->name('rooms.seats.toggleStatus');
    Route::resource('rooms', RoomController::class);

    // Quản lý Suất chiếu (Manager)
    Route::get('showtimes/trashed', [ShowtimeController::class, 'trashed'])->name('showtimes.trashed');
    Route::post('showtimes/{id}/restore', [ShowtimeController::class, 'restore'])->name('showtimes.restore');
    Route::delete('showtimes/{id}/force-delete', [ShowtimeController::class, 'forceDelete'])->name('showtimes.forceDelete');
    Route::resource('showtimes', ShowtimeController::class);

    // Xem danh sách Combo (Read-only)
    Route::resource('combos', ComboController::class)->only(['index', 'show']);
});
