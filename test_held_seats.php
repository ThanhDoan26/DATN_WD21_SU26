<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$abuseService = new App\Services\SeatHoldAbuseService();
echo "Active Held Seats: " . $abuseService->countActiveHeldSeats(1) . PHP_EOL;
