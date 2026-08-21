<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Showtime;
use App\Models\Seat;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Support\Facades\DB;

function runTest($name, $callback, $user) {
    echo "\n--- TEST: $name ---\n";
    
    // Cleanup user's bookings before test
    $bookingIds = Booking::where('user_id', $user->id)->pluck('id');
    if ($bookingIds->isNotEmpty()) {
        \App\Models\BookedSeat::whereIn('booking_id', $bookingIds)->delete();
        \App\Models\BookingCombo::whereIn('booking_id', $bookingIds)->delete();
        Booking::whereIn('id', $bookingIds)->delete();
    }
    
    // Cleanup anti-spam records
    \Illuminate\Support\Facades\DB::table('seat_holds')->where('user_id', $user->id)->delete();
    \App\Models\BookingAbuseLog::where('user_id', $user->id)->delete();

    // Also delete any dangling booked seats for these seats
    global $seats1, $seats2;
    \App\Models\BookedSeat::whereIn('seat_id', array_merge($seats1 ?? [], $seats2 ?? []))->delete();

    try {
        $callback();
        echo "✅ PASS\n";
    } catch (\Exception $e) {
        echo "❌ FAIL: " . $e->getMessage() . "\n";
    }
}

// Setup Data
$user = User::first() ?? User::factory()->create();
$showtime1 = Showtime::where('status', 'Scheduled')->first();
$showtime2 = Showtime::where('status', 'Scheduled')->where('id', '!=', $showtime1->id)->first();
$seats1 = Seat::where('room_id', $showtime1->room_id)->take(2)->pluck('id')->toArray();
$seats2 = Seat::where('room_id', $showtime2->room_id)->take(2)->pluck('id')->toArray();

// Create ticket prices if missing
foreach ([$showtime1, $showtime2] as $st) {
    if (\App\Models\TicketPrice::where('showtime_id', $st->id)->count() === 0) {
        \App\Models\TicketPrice::create([
            'showtime_id' => $st->id,
            'seat_type' => 'Regular',
            'price' => 50000,
            'status' => 'ACTIVE'
        ]);
        \App\Models\TicketPrice::create([
            'showtime_id' => $st->id,
            'seat_type' => 'VIP',
            'price' => 70000,
            'status' => 'ACTIVE'
        ]);
        \App\Models\TicketPrice::create([
            'showtime_id' => $st->id,
            'seat_type' => 'Couple',
            'price' => 120000,
            'status' => 'ACTIVE'
        ]);
    }
}

$service = new BookingService();

// TEST 1: Khởi tạo Booking thành công
runTest('HOME-01: Create Initial Booking', function() use ($service, $user, $showtime1, $seats1) {
    $bookingId = $service->createBooking($user->id, $showtime1->id, $seats1);
    if (!$bookingId) throw new \Exception("Booking not created");
}, $user);

// TEST 2: Cùng Showtime (HOME-17)
runTest('HOME-17: Same Showtime Update', function() use ($service, $user, $showtime1, $seats1) {
    $bookingId1 = $service->createBooking($user->id, $showtime1->id, [$seats1[0]]);
    $bookingId2 = $service->createBooking($user->id, $showtime1->id, $seats1);
    
    $activeCount = Booking::where('user_id', $user->id)->where('status', 'Pending')->count();
    if ($activeCount !== 1) throw new \Exception("Expected 1 active booking, found $activeCount");
}, $user);

// TEST 3: Khác Showtime (HOME-18)
runTest('HOME-18: Different Showtime Block', function() use ($service, $user, $showtime1, $seats1, $showtime2, $seats2) {
    $service->createBooking($user->id, $showtime1->id, $seats1);
    
    try {
        $service->createBooking($user->id, $showtime2->id, $seats2);
        throw new \Exception("Should have blocked creating booking for showtime 2!");
    } catch (\Exception $e) {
        if (!str_contains($e->getMessage(), 'Bạn đang có một đơn đặt vé chưa hoàn tất')) {
            throw $e;
        }
        // Expected Exception!
    }
}, $user);

// TEST 4: Expired Booking (HOME-19)
runTest('HOME-19: Expired Booking allows new booking', function() use ($service, $user, $showtime1, $seats1, $showtime2, $seats2) {
    $bookingId = $service->createBooking($user->id, $showtime1->id, $seats1);
    
    // Force expire
    Booking::where('id', $bookingId)->update([
        'booking_time' => now()->subMinutes(15) // > 10 minutes
    ]);
    
    // Now trying to book showtime 2 should succeed because the first one is expired
    $newBookingId = $service->createBooking($user->id, $showtime2->id, $seats2);
    if (!$newBookingId) throw new \Exception("Failed to create new booking after expiration");
}, $user);

echo "\nDone!\n";
