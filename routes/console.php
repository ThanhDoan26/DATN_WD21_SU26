<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Anti-Abuse: Dọn dẹp booking Pending quá hạn + abuse detection ──
// SUPPLEMENTAL: Booking hoạt động đúng cả khi schedule không chạy.
// Để kích hoạt: cron job chạy `php artisan schedule:run` mỗi phút.
Schedule::command('booking:cleanup-expired')->everyFiveMinutes();

// ── Tự động đồng bộ trạng thái suất chiếu theo thời gian thực (mỗi phút) ──
Schedule::command('showtimes:sync-statuses')->everyMinute();

// ── Tự động chuyển đổi trạng thái phim (PRE_ORDER / NOW_SHOWING) theo thời gian thực ──
Schedule::command('movies:sync-statuses')->everyMinute();

// ── Tự động dọn dẹp các suất chiếu đã kết thúc ──
Schedule::command('showtimes:cleanup-past')->hourly();



