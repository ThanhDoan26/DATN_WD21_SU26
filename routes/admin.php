<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CinemaController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\SeatController;
use App\Http\Controllers\Admin\UserController;

/**
 * Admin Routes
 * ========================================
 * Tất cả routes admin đều bắt đầu từ /admin
 * Yêu cầu authentication và admin role
 */

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Cinemas
    Route::get('cinemas', [CinemaController::class, 'index'])->name('admin.cinemas.index');
    Route::get('cinemas/create', [CinemaController::class, 'create'])->name('admin.cinemas.create');
    Route::post('cinemas', [CinemaController::class, 'store'])->name('admin.cinemas.store');
    Route::get('cinemas/{cinema}', [CinemaController::class, 'show'])->name('admin.cinemas.show');
    Route::get('cinemas/{cinema}/edit', [CinemaController::class, 'edit'])->name('admin.cinemas.edit');
    Route::put('cinemas/{cinema}', [CinemaController::class, 'update'])->name('admin.cinemas.update');
    Route::delete('cinemas/{cinema}', [CinemaController::class, 'destroy'])->name('admin.cinemas.destroy');

    // Rooms
    Route::get('rooms', [RoomController::class, 'index'])->name('admin.rooms.index');
    Route::get('rooms/create', [RoomController::class, 'create'])->name('admin.rooms.create');
    Route::post('rooms', [RoomController::class, 'store'])->name('admin.rooms.store');
    Route::get('rooms/{room}/edit', [RoomController::class, 'edit'])->name('admin.rooms.edit');
    Route::put('rooms/{room}', [RoomController::class, 'update'])->name('admin.rooms.update');
    Route::delete('rooms/{room}', [RoomController::class, 'destroy'])->name('admin.rooms.destroy');

    // Seats
    Route::get('seats', [SeatController::class, 'index'])->name('admin.seats.index');
    Route::get('seats/by-room/{roomId}', [SeatController::class, 'getBySeatsByRoom'])->name('admin.seats.by-room');
    Route::get('seats/{seat}/edit', [SeatController::class, 'edit'])->name('admin.seats.edit');
    Route::put('seats/{seat}', [SeatController::class, 'update'])->name('admin.seats.update');

    // Showtimes
    Route::get('showtimes', [\App\Http\Controllers\Admin\ShowtimeController::class, 'index'])->name('admin.showtimes.index');
    Route::get('showtimes/create', [\App\Http\Controllers\Admin\ShowtimeController::class, 'create'])->name('admin.showtimes.create');
    Route::post('showtimes', [\App\Http\Controllers\Admin\ShowtimeController::class, 'store'])->name('admin.showtimes.store');
    Route::get('showtimes/{showtime}/edit', [\App\Http\Controllers\Admin\ShowtimeController::class, 'edit'])->name('admin.showtimes.edit');
    Route::put('showtimes/{showtime}', [\App\Http\Controllers\Admin\ShowtimeController::class, 'update'])->name('admin.showtimes.update');
    Route::delete('showtimes/{showtime}', [\App\Http\Controllers\Admin\ShowtimeController::class, 'destroy'])->name('admin.showtimes.destroy');

    // Bookings (placeholder)
    Route::get('bookings', function () {
        return view('admin.bookings.index');
    })->name('admin.bookings.index');

    // Users
    Route::get('users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

});
