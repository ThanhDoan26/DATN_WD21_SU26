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
Route::get('/rap/{cinema}', [\App\Http\Controllers\CinemaController::class, 'show'])->name('cinemas.show');

// Posts routes
Route::get('/tin-tuc', [\App\Http\Controllers\PostController::class, 'index'])->name('posts.index');
Route::get('/tin-tuc/{slug}', [\App\Http\Controllers\PostController::class, 'show'])->name('posts.show');

// AI Chatbot Web Route
Route::post('/chat/web', [\App\Http\Controllers\ChatController::class, 'chatWeb'])->name('chat.web');

Route::middleware('auth')->group(function () {
    Route::post('/movies/{movie}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('movies.reviews.store');
    Route::post('/cinemas/{cinema}/reviews', [\App\Http\Controllers\CinemaReviewController::class, 'store'])->name('cinemas.reviews.store');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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

    // Cập nhật Real-time (Polling): Lấy danh sách ghế đã được đặt/giữ
    Route::get('/showtime/{showtime}/booked-seats', 'getBookedSeatsAPI')->name('api.booking.booked-seats');

    // Hủy chủ động (Explicit Cancel)
    Route::post('/cancel-explicit', 'cancelExplicit')->middleware('auth')->name('api.booking.cancel-explicit');
});

// Frontend API/AJAX routes
Route::post('/api/apply-coupon', [\App\Http\Controllers\CheckoutController::class, 'applyCoupon'])->name('api.apply-coupon');

Route::middleware('auth')->group(function () {
    Route::post('/checkout/init', [\App\Http\Controllers\CheckoutController::class, 'init'])->name('checkout.init');
    Route::get('/checkout', [\App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout');
    Route::get('/checkout/success', [\App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');
    Route::post('/checkout/release-lock', [\App\Http\Controllers\CheckoutController::class, 'releaseLock'])->name('checkout.release-lock');

    // Lịch sử đặt vé
    Route::get('/booking-history', [BookingHistoryController::class, 'index'])->name('booking.history');
    Route::get('/booking-history/{bookingCode}', [BookingHistoryController::class, 'show'])->name('booking.history.show');
    
    // Đánh giá Combo
    Route::post('/booking-history/combo-rate', [\App\Http\Controllers\ComboReviewController::class, 'store'])->name('combo-reviews.store');
});

// ── Anti-Abuse: Reserve endpoint với rate limiting + restriction check ──
Route::middleware(['auth', 'throttle:booking', 'check.booking.restriction'])->group(function () {
    Route::post('/checkout/reserve', [\App\Http\Controllers\CheckoutController::class, 'reserve'])->name('checkout.reserve');
});
Route::middleware('auth')->group(function () {
    Route::post('/checkout/mock-payment', [\App\Http\Controllers\CheckoutController::class, 'mockPayment'])->name('checkout.mock-payment');

    Route::post('/stripe/create-session',
        [StripeController::class,'createSession'])
        ->name('stripe.session');

    Route::get('/stripe/success',
        [StripeController::class,'success'])
        ->name('stripe.success');

    Route::get('/stripe/cancel',
        [StripeController::class,'cancel'])
        ->name('stripe.cancel');

    // VNPAY routes
    Route::post('/vnpay/payment',
        [\App\Http\Controllers\VnPayController::class, 'createPayment'])
        ->name('vnpay.payment');

    Route::get('/vnpay/return',
        [\App\Http\Controllers\VnPayController::class, 'return'])
        ->name('vnpay.return');
    
    Route::get('/vnpay/ipn',
        [\App\Http\Controllers\VnPayController::class, 'ipn'])
        ->name('vnpay.ipn');

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

require __DIR__.'/auth.php';

