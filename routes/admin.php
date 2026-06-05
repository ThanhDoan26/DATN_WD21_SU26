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
    Route::get('rooms/{room}', [RoomController::class, 'show'])->name('admin.rooms.show');
    Route::get('rooms/{room}/edit', [RoomController::class, 'edit'])->name('admin.rooms.edit');
    Route::put('rooms/{room}', [RoomController::class, 'update'])->name('admin.rooms.update');
    Route::delete('rooms/{room}', [RoomController::class, 'destroy'])->name('admin.rooms.destroy');

    // Seats
    Route::get('seats', [SeatController::class, 'index'])->name('admin.seats.index');
    Route::get('seats/create', [SeatController::class, 'create'])->name('admin.seats.create');
    Route::post('seats', [SeatController::class, 'store'])->name('admin.seats.store');
    Route::get('seats/by-room/{roomId}', [SeatController::class, 'getBySeatsByRoom'])->name('admin.seats.by-room');
    Route::get('seats/{seat}/edit', [SeatController::class, 'edit'])->name('admin.seats.edit');
    Route::put('seats/{seat}', [SeatController::class, 'update'])->name('admin.seats.update');
    Route::delete('seats/{seat}', [SeatController::class, 'destroy'])->name('admin.seats.destroy');

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

    // Users
    Route::get('users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

});
