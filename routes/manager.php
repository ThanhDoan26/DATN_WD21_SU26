<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CinemaManagerDashboardController;

use App\Http\Controllers\Manager\RoomController;
use App\Http\Controllers\Manager\ShowtimeController;
use App\Http\Controllers\Manager\ComboController;
use App\Http\Controllers\Manager\CouponController;

Route::middleware(['auth', 'role:MANAGER', 'cinema.assignment'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [CinemaManagerDashboardController::class, 'index'])->name('dashboard');

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

    // Xem chi tiết Đơn hàng / Vé đặt (Manager)
    Route::get('bookings/{booking}', [\App\Http\Controllers\Admin\BookingController::class, 'show'])->name('bookings.show');

    // Quản lý phiếu giảm giá
    Route::get('/coupons/expired', [\App\Http\Controllers\Manager\CouponController::class, 'expired'])->name('coupon.expired');
    Route::resource('coupons', \App\Http\Controllers\Manager\CouponController::class)->names([
        'index'   => 'coupons.index',
        'create'  => 'coupons.create',
        'store'   => 'coupons.store',
        'edit'    => 'coupons.edit',
        'update'  => 'coupons.update',
        'destroy' => 'coupons.destroy',
    ])->except(['show']);

    // Kiểm tra phiếu giảm giá
    Route::get('/coupon-check', [\App\Http\Controllers\Manager\CouponCheckController::class, 'index'])->name('coupon.check');
    Route::post('/coupon-check', [\App\Http\Controllers\Manager\CouponCheckController::class, 'check'])->name('coupon.check.post');
});
