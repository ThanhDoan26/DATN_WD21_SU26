<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BookingService;
use App\Services\SeatHoldAbuseService;

/**
 * Artisan command dọn dẹp các hold đã hết hạn và check abuse.
 *
 * 🚨 IMPORTANT: Đây là SUPPLEMENTAL command.
 * Booking system PHẢI hoạt động chính xác ngay cả khi command này không chạy.
 * cleanupExpiredPendingBookings() vẫn được gọi trước mỗi booking request.
 *
 * Usage:
 *   php artisan booking:cleanup-expired
 *
 * Schedule (optional):
 *   $schedule->command('booking:cleanup-expired')->everyFiveMinutes();
 */
class CleanupExpiredHolds extends Command
{
    protected $signature = 'booking:cleanup-expired';

    protected $description = 'Dọn dẹp booking Pending quá hạn và xử lý abuse detection cho seat holds';

    public function handle(): int
    {
        $this->info('Starting cleanup...');

        // 1. Cleanup expired Pending bookings (existing logic)
        try {
            $bookingService = new BookingService();
            $expiredCount = $bookingService->cleanupExpiredPendingBookings();
            $this->info("Cancelled {$expiredCount} expired Pending booking(s).");
        } catch (\Exception $e) {
            $this->error('Booking cleanup failed: ' . $e->getMessage());
        }

        // 2. Process expired seat holds + abuse detection (tracking layer)
        try {
            $abuseService = new SeatHoldAbuseService();
            $processedCount = $abuseService->processExpiredHolds();
            $this->info("Processed {$processedCount} seat hold(s) (expired/completed).");
        } catch (\Exception $e) {
            $this->error('Seat hold processing failed: ' . $e->getMessage());
        }

        $this->info('Cleanup completed.');

        return self::SUCCESS;
    }
}
