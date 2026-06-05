<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CinemaController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\SeatController;
use App\Http\Controllers\Admin\BookingController;

/**
 * Admin Routes
 * ========================================
 * Tất cả routes admin đều bắt đầu từ /admin
 * Yêu cầu authentication và admin role
 */

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Cinemas - Index only
    Route::get('cinemas', [CinemaController::class, 'index'])->name('admin.cinemas.index');

    // Rooms
    Route::get('rooms', [RoomController::class, 'index'])->name('admin.rooms.index');
    Route::get('rooms/create', [RoomController::class, 'create'])->name('admin.rooms.create');
    Route::post('rooms', [RoomController::class, 'store'])->name('admin.rooms.store');
    Route::get('rooms/{room}/edit', [RoomController::class, 'edit'])->name('admin.rooms.edit');
    Route::put('rooms/{room}', [RoomController::class, 'update'])->name('admin.rooms.update');
    Route::delete('rooms/{room}', [RoomController::class, 'destroy'])->name('admin.rooms.destroy');

    // Seats - Index only
    Route::get('seats', [SeatController::class, 'index'])->name('admin.seats.index');
    Route::get('seats/by-room/{roomId}', [SeatController::class, 'getBySeatsByRoom'])->name('admin.seats.by-room');

    // Movies (placeholder)
    Route::get('movies', function () {
        return view('admin.movies.index');
    })->name('admin.movies.index');

    // Showtimes (placeholder)
    Route::get('showtimes', function () {
        return view('admin.showtimes.index');
    })->name('admin.showtimes.index');

    // Bookings
    Route::get('bookings', [BookingController::class, 'index'])->name('admin.bookings.index');
    Route::get('bookings/create', [BookingController::class, 'create'])->name('admin.bookings.create');
    Route::post('bookings', [BookingController::class, 'store'])->name('admin.bookings.store');
    Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('admin.bookings.show');
    Route::get('bookings/{booking}/edit', [BookingController::class, 'edit'])->name('admin.bookings.edit');
    Route::put('bookings/{booking}', [BookingController::class, 'update'])->name('admin.bookings.update');
    Route::delete('bookings/{booking}', [BookingController::class, 'destroy'])->name('admin.bookings.destroy');

    // Users (placeholder)
    Route::get('users', function () {
        return view('admin.users.index');
    })->name('admin.users.index');

});
