<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CinemaController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\SeatController;

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

    // Bookings (placeholder)
    Route::get('bookings', function () {
        return view('admin.bookings.index');
    })->name('admin.bookings.index');

    // Users (placeholder)
    Route::get('users', function () {
        return view('admin.users.index');
    })->name('admin.users.index');

});
