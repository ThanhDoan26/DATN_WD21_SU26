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

// ── Tự động xóa tạm các suất chiếu đã kết thúc ──
Schedule::command('showtimes:cleanup-past')->hourly();

