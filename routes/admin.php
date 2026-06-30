<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CinemaController;
use App\Http\Controllers\Admin\MovieController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\SeatController;
use App\Http\Controllers\Admin\ShowtimeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\ComboController;

/**
 * Admin Routes
 * ========================================
 * Tất cả routes admin đều bắt đầu từ /admin
 * Yêu cầu authentication và admin role
 */

Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    // Phân quyền cho ADMIN và MANAGER
    Route::middleware(['role:ADMIN,MANAGER'])->group(function () {

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
    Route::post('cinemas/{id}/restore', [CinemaController::class, 'restore'])->name('admin.cinemas.restore');

    // Movies
    Route::get('movies', [MovieController::class, 'index'])->name('admin.movies.index');
    Route::get('movies/create', [MovieController::class, 'create'])->name('admin.movies.create');
    Route::post('movies', [MovieController::class, 'store'])->name('admin.movies.store');
    Route::get('movies/{movie}', [MovieController::class, 'show'])->name('admin.movies.show');
    Route::get('movies/{movie}/edit', [MovieController::class, 'edit'])->name('admin.movies.edit');
    Route::put('movies/{movie}', [MovieController::class, 'update'])->name('admin.movies.update');
    Route::delete('movies/{movie}', [MovieController::class, 'destroy'])->name('admin.movies.destroy');

    // Rooms
    Route::get('rooms', [RoomController::class, 'index'])->name('admin.rooms.index');
    Route::get('rooms/create', [RoomController::class, 'create'])->name('admin.rooms.create');
    Route::post('rooms', [RoomController::class, 'store'])->name('admin.rooms.store');
    Route::get('rooms/trashed', [RoomController::class, 'trashed'])->name('admin.rooms.trashed');
    Route::get('rooms/{room}', [RoomController::class, 'show'])->name('admin.rooms.show');
    Route::get('rooms/{room}/edit', [RoomController::class, 'edit'])->name('admin.rooms.edit');
    Route::put('rooms/{room}', [RoomController::class, 'update'])->name('admin.rooms.update');
    Route::delete('rooms/{room}', [RoomController::class, 'destroy'])->name('admin.rooms.destroy');
    Route::post('rooms/{id}/restore', [RoomController::class, 'restore'])->name('admin.rooms.restore');
    Route::delete('rooms/{id}/force-delete', [RoomController::class, 'forceDelete'])->name('admin.rooms.forceDelete');

    // Seats
    Route::get('seats', [SeatController::class, 'index'])->name('admin.seats.index');
    Route::get('seats/create', [SeatController::class, 'create'])->name('admin.seats.create');
    Route::post('seats', [SeatController::class, 'store'])->name('admin.seats.store');
    Route::get('seats/by-room/{roomId}', [SeatController::class, 'getBySeatsByRoom'])->name('admin.seats.by-room');
    Route::get('seats/{seat}/edit', [SeatController::class, 'edit'])->name('admin.seats.edit');
    Route::put('seats/{seat}', [SeatController::class, 'update'])->name('admin.seats.update');
    Route::delete('seats/{seat}', [SeatController::class, 'destroy'])->name('admin.seats.destroy');

    // Categories
    Route::get('categories', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('categories/create', [\App\Http\Controllers\Admin\CategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('categories', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('categories/{category}/edit', [\App\Http\Controllers\Admin\CategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('categories/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('categories/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('admin.categories.destroy');

    // Movies
    Route::get('movies', [\App\Http\Controllers\Admin\MovieController::class, 'index'])->name('admin.movies.index');
    Route::get('movies/create', [\App\Http\Controllers\Admin\MovieController::class, 'create'])->name('admin.movies.create');
    Route::post('movies', [\App\Http\Controllers\Admin\MovieController::class, 'store'])->name('admin.movies.store');
    Route::get('movies/{movie}', [\App\Http\Controllers\Admin\MovieController::class, 'show'])->name('admin.movies.show');
    Route::get('movies/{movie}/edit', [\App\Http\Controllers\Admin\MovieController::class, 'edit'])->name('admin.movies.edit');
    Route::put('movies/{movie}', [\App\Http\Controllers\Admin\MovieController::class, 'update'])->name('admin.movies.update');
    Route::delete('movies/{movie}', [\App\Http\Controllers\Admin\MovieController::class, 'destroy'])->name('admin.movies.destroy');

    // Showtimes
    Route::get('showtimes', [ShowtimeController::class, 'index'])->name('admin.showtimes.index');
    Route::get('showtimes/create', [ShowtimeController::class, 'create'])->name('admin.showtimes.create');
    Route::post('showtimes', [ShowtimeController::class, 'store'])->name('admin.showtimes.store');
    Route::get('showtimes/{showtime}/edit', [ShowtimeController::class, 'edit'])->name('admin.showtimes.edit');
    Route::get('showtimes/trashed', [ShowtimeController::class, 'trashed'])->name('admin.showtimes.trashed');
    Route::get('showtimes/{showtime}', [ShowtimeController::class, 'show'])->name('admin.showtimes.show');
    Route::put('showtimes/{showtime}', [ShowtimeController::class, 'update'])->name('admin.showtimes.update');
    Route::delete('showtimes/{showtime}', [ShowtimeController::class, 'destroy'])->name('admin.showtimes.destroy');
    Route::post('showtimes/{showtime}/restore', [ShowtimeController::class, 'restore'])->name('admin.showtimes.restore');
    Route::delete('showtimes/{showtime}/force-delete', [ShowtimeController::class, 'forceDelete'])->name('admin.showtimes.forceDelete');

    // Bookings
    Route::get('bookings', [BookingController::class, 'index'])->name('admin.bookings.index');
    Route::get('bookings/create', [BookingController::class, 'create'])->name('admin.bookings.create');
    Route::post('bookings', [BookingController::class, 'store'])->name('admin.bookings.store');
    Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('admin.bookings.show');
    Route::get('bookings/{booking}/edit', [BookingController::class, 'edit'])->name('admin.bookings.edit');
    Route::put('bookings/{booking}', [BookingController::class, 'update'])->name('admin.bookings.update');
    Route::delete('bookings/{booking}', [BookingController::class, 'destroy'])->name('admin.bookings.destroy');

    });


    // Phân quyền chỉ cho ADMIN
    Route::middleware(['role:ADMIN'])->group(function () {
        // Users
        Route::get('users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('admin.users.create');
        Route::post('users', [UserController::class, 'store'])->name('admin.users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');

    // Coupons
    Route::resource('coupons', CouponController::class, ['as' => 'admin']);

    // Combos
    Route::resource('combos', ComboController::class, ['as' => 'admin']);

});
});

