<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\BookingService;

DB::table('bookings')->where('user_id', 1)->delete();
DB::table('seat_holds')->where('user_id', 1)->delete();

$bookingService = new BookingService();

try {
    $bookingId1 = $bookingService->createBooking(1, 11, [1, 2, 3, 4, 5], 'ONLINE');
    echo "Created booking 1: $bookingId1\n";
    
    $bookingId2 = $bookingService->createBooking(1, 11, [1, 2, 3, 4, 5, 6], 'ONLINE');
    echo "Created booking 2: $bookingId2\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
