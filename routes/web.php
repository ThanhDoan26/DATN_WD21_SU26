<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MovieController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MovieController::class, 'welcome']);
Route::get('/phim-dang-chieu', [MovieController::class, 'currentMovies'])->name('movies.current');
Route::get('/phim-sap-chieu', [MovieController::class, 'upcomingMovies'])->name('movies.upcoming');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Load admin routes
require __DIR__.'/admin.php';

require __DIR__.'/auth.php';
