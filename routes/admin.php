<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CinemaController;
use App\Http\Controllers\Admin\MovieController;
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

    // Cinemas - Full CRUD
    Route::resource('cinemas', CinemaController::class, [
        'names' => [
            'index' => 'admin.cinemas.index',
            'create' => 'admin.cinemas.create',
            'store' => 'admin.cinemas.store',
            'show' => 'admin.cinemas.show',
            'edit' => 'admin.cinemas.edit',
            'update' => 'admin.cinemas.update',
            'destroy' => 'admin.cinemas.destroy',
        ]
    ]);

    // Movies - Full CRUD
    Route::resource('movies', MovieController::class, [
        'names' => [
            'index' => 'admin.movies.index',
            'create' => 'admin.movies.create',
            'store' => 'admin.movies.store',
            'show' => 'admin.movies.show',
            'edit' => 'admin.movies.edit',
            'update' => 'admin.movies.update',
            'destroy' => 'admin.movies.destroy',
        ]
    ]);

    // Rooms - Index only
    Route::get('rooms', [RoomController::class, 'index'])->name('admin.rooms.index');

    // Seats - Index only
    Route::get('seats', [SeatController::class, 'index'])->name('admin.seats.index');
    Route::get('seats/by-room/{roomId}', [SeatController::class, 'getBySeatsByRoom'])->name('admin.seats.by-room');

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
