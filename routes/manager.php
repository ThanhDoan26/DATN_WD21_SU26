<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CinemaManagerDashboardController;

use App\Http\Controllers\Manager\RoomController;
use App\Http\Controllers\Manager\ShowtimeController;
use App\Http\Controllers\Manager\ComboController;
use App\Http\Controllers\Manager\CouponController;

Route::middleware(['auth', 'role:MANAGER', 'cinema.assignment'])->prefix('manager')->name('manager.')->group(function () {
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

    // Quản lý Combo bắp nước (Manager)
    Route::patch('combos/{combo}/toggle-status', [\App\Http\Controllers\Admin\ComboController::class, 'toggleStatus'])->name('combos.toggle-status');
    Route::resource('combos', ComboController::class);

    // Quản lý Bài viết / Tin tức (Manager)
    Route::patch('posts/{post}/toggle-status', [\App\Http\Controllers\Admin\PostController::class, 'toggleStatus'])->name('posts.toggle-status');
    Route::patch('posts/{post}/toggle-featured', [\App\Http\Controllers\Admin\PostController::class, 'toggleFeatured'])->name('posts.toggle-featured');
    Route::resource('posts', \App\Http\Controllers\Admin\PostController::class);

    // Xem danh sách Phim (Read-only)
    Route::resource('movies', \App\Http\Controllers\Manager\MovieController::class)->only(['index', 'show']);

    // Xem chi tiết Đơn hàng / Vé đặt (Manager)
    Route::get('bookings/{booking}', [\App\Http\Controllers\Admin\BookingController::class, 'show'])->name('bookings.show');
});
