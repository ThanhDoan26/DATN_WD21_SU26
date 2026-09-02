<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Dọn dẹp booking Pending quá hạn (10 phút) + giải phóng ghế + abuse detection ──
Schedule::command('booking:cleanup-expired')->everyThirtySeconds();

// ── Tự động đồng bộ trạng thái suất chiếu theo thời gian thực (mỗi phút) ──
Schedule::command('showtimes:sync-statuses')->everyMinute();

// ── Tự động chuyển đổi trạng thái phim (PRE_ORDER / NOW_SHOWING) theo thời gian thực ──
Schedule::command('movies:sync-statuses')->everyMinute();

// ── Tự động dọn dẹp các suất chiếu đã kết thúc ──
Schedule::command('showtimes:cleanup-past')->hourly();



