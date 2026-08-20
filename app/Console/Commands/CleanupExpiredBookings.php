<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('booking:cleanup')]
#[Description('Clean up expired pending bookings in the database')]
class CleanupExpiredBookings extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of expired bookings...');
        $bookingService = new \App\Services\BookingService();
        $count = $bookingService->cleanupExpiredPendingBookings();
        $this->info("Successfully cleaned up {$count} expired bookings.");
    }
}
