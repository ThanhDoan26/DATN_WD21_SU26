<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Services\BookingService;

try {
    $bookingService = new BookingService();
    $id = $bookingService->createBooking(1, 11, [1, 2, 3], 'ONLINE');
    echo 'OK: ' . $id . PHP_EOL;
} catch (\Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}
