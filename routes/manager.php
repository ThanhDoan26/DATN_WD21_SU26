<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CinemaManagerDashboardController;

use App\Http\Controllers\Manager\RoomController;
use App\Http\Controllers\Manager\ShowtimeController;
use App\Http\Controllers\Manager\ComboController;
use App\Http\Controllers\Manager\CouponController;

Route::middleware(['auth', 'role:MANAGER'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [CinemaManagerDashboardController::class, 'index'])->name('dashboard');

    // Quản lý Mã giảm giá (Manager)
    Route::get('coupons/trashed', [CouponController::class, 'trashed'])->name('coupons.trashed');
    Route::post('coupons/{id}/restore', [CouponController::class, 'restore'])->name('coupons.restore');
    Route::delete('coupons/{id}/force-delete', [CouponController::class, 'forceDelete'])->name('coupons.forceDelete');
    Route::resource('coupons', CouponController::class);

    // Quản lý Phòng chiếu (Manager)
    Route::get('rooms/trashed', [RoomController::class, 'trashed'])->name('rooms.trashed');
    Route::post('rooms/{id}/restore', [RoomController::class, 'restore'])->name('rooms.restore');
    Route::delete('rooms/{id}/force-delete', [RoomController::class, 'forceDelete'])->name('rooms.forceDelete');
    Route::post('rooms/{room}/seats/{seat}/toggle-status', [RoomController::class, 'toggleSeatStatus'])->name('rooms.seats.toggleStatus');
    Route::get('seats/by-room/{roomId}', [RoomController::class, 'getBySeatsByRoom'])->name('seats.by-room');
    Route::resource('rooms', RoomController::class);

    // Quản lý Suất chiếu (Manager)
    Route::get('showtimes/trashed', [ShowtimeController::class, 'trashed'])->name('showtimes.trashed');
    Route::post('showtimes/{id}/restore', [ShowtimeController::class, 'restore'])->name('showtimes.restore');
    Route::delete('showtimes/{id}/force-delete', [ShowtimeController::class, 'forceDelete'])->name('showtimes.forceDelete');
    Route::resource('showtimes', ShowtimeController::class);

    // Xem danh sách Combo (Read-only)
    Route::resource('combos', ComboController::class)->only(['index', 'show']);

    // Xem danh sách Phim (Read-only)
    Route::resource('movies', \App\Http\Controllers\Manager\MovieController::class)->only(['index', 'show']);
});
