<?php

namespace App\Providers;

use App\Hashing\LegacyBcryptHasher;
use Illuminate\Hashing\HashManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('hash', function ($app) {
            return new HashManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        \App\Models\Booking::observe(\App\Observers\BookingObserver::class);

        // Global Active Pending Booking view composer
        \Illuminate\Support\Facades\View::composer('layouts.frontend', function ($view) {
            $activeBooking = null;
            if (\Illuminate\Support\Facades\Auth::check()) {
                $bookingService = app(\App\Services\BookingService::class);
                $activeBooking = $bookingService->getActivePendingBooking(\Illuminate\Support\Facades\Auth::id());
            }
            $view->with('activePendingBooking', $activeBooking);
        });

        // ── Anti-Abuse: Rate limiter cho booking endpoints ──────────
        // Chỉ áp dụng cho POST /checkout/reserve, không rate-limit GET endpoints.
        RateLimiter::for('booking', function (Request $request) {
            return Limit::perMinute(config('booking.rate_limit.max_requests_per_minute', 10))
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bạn đang thao tác quá nhanh. Vui lòng đợi một chút.',
                    ], 429);
                });
        });
    }
}
