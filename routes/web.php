<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\BookingHistoryController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MovieController::class, 'welcome'])->name('home');
Route::get('/phim-dang-chieu', [MovieController::class, 'currentMovies'])->name('movies.current');
Route::get('/phim-sap-chieu', [MovieController::class, 'upcomingMovies'])->name('movies.upcoming');
Route::get('/phim/{id}', [MovieController::class, 'show'])->name('movies.show');

Route::middleware('auth')->group(function () {
    Route::post('/movies/{movie}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('movies.reviews.store');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    // Chuyển hướng về dashboard đúng role — không cho phép staff/manager/admin ở lại trang này
    if ($user && $user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    if ($user && $user->isManager()) {
        return redirect()->route('manager.dashboard');
    }
    if ($user && $user->isStaff()) {
        return redirect()->route('staff.dashboard');
    }

    // Chỉ USER (khách hàng) mới được xem trang dashboard này
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Load admin routes
require __DIR__.'/admin.php';

// Load manager routes
require __DIR__.'/manager.php';

// Load staff routes
require __DIR__.'/staff.php';

// Booking routes
Route::controller(\App\Http\Controllers\BookingController::class)->group(function () {
    // Bước 1: Chọn cụm rạp
    Route::get('/booking/movie/{movie}/cinema', 'selectCinema')->name('booking.select-cinema');

    // Bước 2 & 3: Chọn ngày và suất chiếu
    Route::get('/booking/movie/{movie}/cinema/{cinema}/dates', 'selectDatesAndShowtimes')->name('booking.select-dates-showtimes');

    // Bước 4: Chọn ghế
    Route::get('/booking/showtime/{showtime}/seats', 'selectSeats')->middleware('auth')->name('booking.select-seats');
});

// Booking API routes
Route::prefix('api/booking')->controller(\App\Http\Controllers\BookingController::class)->group(function () {
    // Bước 2: Lấy danh sách ngày chiếu
    Route::get('/dates', 'getDates')->name('api.booking.dates');

    // Bước 3: Lấy danh sách suất chiếu
    Route::get('/showtimes', 'getShowtimes')->name('api.booking.showtimes');
});

// Frontend API/AJAX routes
Route::post('/api/apply-coupon', [\App\Http\Controllers\CheckoutController::class, 'applyCoupon'])->name('api.apply-coupon');

Route::middleware('auth')->group(function () {
    Route::post('/checkout/reserve', [\App\Http\Controllers\CheckoutController::class, 'reserve'])->name('checkout.reserve');
    Route::get('/checkout', [\App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout');
    Route::get('/checkout/success', [\App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');

    // Lịch sử đặt vé
    Route::get('/booking-history', [BookingHistoryController::class, 'index'])->name('booking.history');
    Route::get('/booking-history/{bookingCode}', [BookingHistoryController::class, 'show'])->name('booking.history.show');
    
    // Đánh giá Combo
    Route::post('/booking-history/combo-rate', [\App\Http\Controllers\ComboReviewController::class, 'store'])->name('combo-reviews.store');
});
Route::middleware('auth')->group(function () {

    Route::post('/stripe/create-session',
        [StripeController::class,'createSession'])
        ->name('stripe.session');

    Route::get('/stripe/success',
        [StripeController::class,'success'])
        ->name('stripe.success');

    Route::get('/stripe/cancel',
        [StripeController::class,'cancel'])
        ->name('stripe.cancel');

});

// Native App QR Scanner Redirection
Route::get('/tickets/{token}', function ($token) {
    // Nếu người quét là Staff hoặc Manager -> Chuyển vào trang thao tác quét chuyên dụng
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isStaff() || $user->isManager() || $user->isAdmin()) {
            return redirect()->route('staff.ticket.search', ['code' => $token, 'scan' => 1]);
        }
    }

    // Nếu người quét là Khách hàng (User gốc của vé) hoặc chưa đăng nhập
    $booking = \App\Models\Booking::where('ticket_token', $token)->first();
    if ($booking) {
        return redirect()->route('booking.history.show', ['bookingCode' => $booking->booking_code]);
    }

    return redirect()->route('home')->with('error', 'Vé không tồn tại trên hệ thống.');
})->name('tickets.scan');

// Tin tức / Blog frontend routes
Route::get('/tin-tuc', [\App\Http\Controllers\PostController::class, 'index'])->name('posts.index');
Route::get('/tin-tuc/{slug}', [\App\Http\Controllers\PostController::class, 'show'])->name('posts.show');

require __DIR__.'/auth.php';

Route::get('/quick-login-staff', function () {
    $user = \App\Models\User::whereHas('role', function($q) {
        $q->where('role_name', 'STAFF');
    })->first();
    if ($user) {
        auth()->login($user);
        return redirect()->route('staff.dashboard');
    }
    return redirect()->route('login')->with('error', 'Không tìm thấy tài khoản Staff.');

})->name('staff.quick-login');

