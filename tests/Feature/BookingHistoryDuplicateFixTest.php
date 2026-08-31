<?php

use App\Models\User;
use App\Models\Role;
use App\Models\Cinema;
use App\Models\Room;
use App\Models\Movie;
use App\Models\Showtime;
use App\Models\Seat;
use App\Models\Booking;
use App\Services\BookingHistoryService;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('booking history excludes auto-cancelled replaced draft bookings', function () {
    $role = Role::firstOrCreate([
        'role_name' => 'USER'
    ], [
        'description' => 'User role'
    ]);

    $user = User::create([
        'name' => 'Test User',
        'email' => 'user@test.com',
        'password' => bcrypt('password'),
        'role_id' => $role->id,
        'status' => 'ACTIVE',
    ]);

    $cinema = Cinema::create([
        'name' => 'Cinema 1',
        'address' => '123 Test St',
        'city' => 'Hanoi',
    ]);

    $room = Room::create([
        'cinema_id' => $cinema->id,
        'name' => 'Room 1',
        'capacity' => 50,
        'format' => '2D',
    ]);

    $movie = Movie::create([
        'title' => 'Test Movie',
        'duration' => 120,
        'status' => 'NOW_SHOWING',
        'release_date' => now()->subDays(5),
    ]);

    $showtime = Showtime::create([
        'movie_id' => $movie->id,
        'room_id' => $room->id,
        'start_time' => now()->addHours(3),
        'end_time' => now()->addHours(5),
        'status' => Showtime::STATUS_SCHEDULED,
    ]);

    // 1. Paid booking
    $paidBooking = Booking::create([
        'user_id' => $user->id,
        'showtime_id' => $showtime->id,
        'booking_code' => 'BK_PAID_123',
        'total_price' => 200000,
        'status' => 'Paid',
        'booking_time' => now(),
    ]);

    // 2. Draft booking replaced by new booking request
    $replacedDraft = Booking::create([
        'user_id' => $user->id,
        'showtime_id' => $showtime->id,
        'booking_code' => 'BK_REPLACED_123',
        'total_price' => 150000,
        'status' => 'Cancelled',
        'cancellation_reason' => 'User initiated a new booking request',
        'booking_time' => now(),
    ]);

    // 3. User explicitly cancelled booking
    $userCancelled = Booking::create([
        'user_id' => $user->id,
        'showtime_id' => $showtime->id,
        'booking_code' => 'BK_CANCELLED_123',
        'total_price' => 100000,
        'status' => 'Cancelled',
        'cancellation_reason' => 'User cancelled explicitly',
        'booking_time' => now(),
    ]);

    $historyService = new BookingHistoryService();

    // Check paid tab
    $paidList = $historyService->getUserBookings($user->id, 'paid');
    expect($paidList->total())->toBe(1);
    expect($paidList->first()->booking_code)->toBe('BK_PAID_123');

    // Check cancelled tab -> Should only have explicitly cancelled, NOT the replaced draft
    $cancelledList = $historyService->getUserBookings($user->id, 'cancelled');
    expect($cancelledList->total())->toBe(1);
    expect($cancelledList->first()->booking_code)->toBe('BK_CANCELLED_123');

    // Check all tab -> Should have paid and explicitly cancelled, NOT the replaced draft
    $allList = $historyService->getUserBookings($user->id, 'all');
    expect($allList->total())->toBe(2);
    $allCodes = $allList->pluck('booking_code')->toArray();
    expect($allCodes)->toContain('BK_PAID_123');
    expect($allCodes)->toContain('BK_CANCELLED_123');
    expect($allCodes)->not->toContain('BK_REPLACED_123');

    // Check counts
    $counts = $historyService->getBookingCounts($user->id);
    expect($counts['paid'])->toBe(1);
    expect($counts['cancelled'])->toBe(1);
    expect($counts['all'])->toBe(2);
});

test('checkout reserve updates existing pending booking instead of creating a cancelled duplicate', function () {
    $role = Role::firstOrCreate([
        'role_name' => 'USER'
    ], [
        'description' => 'User role'
    ]);

    $user = User::create([
        'name' => 'Test User 2',
        'email' => 'user2@test.com',
        'password' => bcrypt('password'),
        'role_id' => $role->id,
        'status' => 'ACTIVE',
    ]);

    $cinema = Cinema::create([
        'name' => 'Cinema 2',
        'address' => '456 Test St',
        'city' => 'Hanoi',
    ]);

    $room = Room::create([
        'cinema_id' => $cinema->id,
        'name' => 'Room 2',
        'capacity' => 50,
        'format' => '2D',
    ]);

    $seat1 = Seat::create([
        'room_id' => $room->id,
        'row_name' => 'A',
        'seat_number' => 1,
        'seat_type' => 'Regular',
        'status' => 'AVAILABLE',
    ]);

    $seat2 = Seat::create([
        'room_id' => $room->id,
        'row_name' => 'A',
        'seat_number' => 2,
        'seat_type' => 'Regular',
        'status' => 'AVAILABLE',
    ]);

    $movie = Movie::create([
        'title' => 'Test Movie 2',
        'duration' => 120,
        'status' => 'NOW_SHOWING',
        'release_date' => now()->subDays(5),
    ]);

    $showtime = Showtime::create([
        'movie_id' => $movie->id,
        'room_id' => $room->id,
        'start_time' => now()->addHours(3),
        'end_time' => now()->addHours(5),
        'status' => Showtime::STATUS_SCHEDULED,
    ]);

    \App\Models\TicketPrice::create([
        'showtime_id' => $showtime->id,
        'seat_type' => 'Regular',
        'price' => 100000,
        'status' => 'ACTIVE',
    ]);

    $bookingService = new BookingService();

    // 1. User holds seats at checkout init
    $initialBookingId = $bookingService->createBooking(
        $user->id,
        $showtime->id,
        [$seat1->id, $seat2->id],
        'ONLINE'
    );

    $initialBooking = Booking::find($initialBookingId);
    $initialBookingCode = $initialBooking->booking_code;

    expect(Booking::where('user_id', $user->id)->count())->toBe(1);

    // 2. User confirms payment on checkout page (calls checkout.reserve)
    $response = $this->actingAs($user)->postJson(route('checkout.reserve'), [
        'showtime_id' => $showtime->id,
        'seat_ids' => "{$seat1->id},{$seat2->id}",
        'payment_method' => 'VNPAY',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    // Ensure NO new duplicate booking was created, and initial booking is NOT cancelled!
    $userBookings = Booking::where('user_id', $user->id)->get();
    expect($userBookings->count())->toBe(1);
    expect($userBookings->first()->id)->toBe($initialBookingId);
    expect($userBookings->first()->booking_code)->toBe($initialBookingCode);
    expect($userBookings->first()->status)->toBe('PROCESSING');

    // 3. User completes payment
    $bookingService->completePayment($initialBookingId, 'VNPAY');

    $finalBooking = Booking::find($initialBookingId);
    expect($finalBooking->status)->toBe('Paid');

    // 4. In history, user should see exactly 1 Paid booking, 0 Cancelled bookings
    $historyService = new BookingHistoryService();
    $counts = $historyService->getBookingCounts($user->id);
    expect($counts['paid'])->toBe(1);
    expect($counts['cancelled'])->toBe(0);
    expect($counts['all'])->toBe(1);
});

test('checkout init preserves combos when user updates seats', function () {
    $role = Role::firstOrCreate([
        'role_name' => 'USER'
    ], [
        'description' => 'User role'
    ]);

    $user = User::create([
        'name' => 'Test User 3',
        'email' => 'user3@test.com',
        'password' => bcrypt('password'),
        'role_id' => $role->id,
        'status' => 'ACTIVE',
    ]);

    $cinema = Cinema::create([
        'name' => 'Cinema 3',
        'address' => '789 Test St',
        'city' => 'Hanoi',
    ]);

    $room = Room::create([
        'cinema_id' => $cinema->id,
        'name' => 'Room 3',
        'capacity' => 50,
        'format' => '2D',
    ]);

    $seat1 = Seat::create([
        'room_id' => $room->id,
        'row_name' => 'A',
        'seat_number' => 1,
        'seat_type' => 'Regular',
        'status' => 'AVAILABLE',
    ]);

    $seat2 = Seat::create([
        'room_id' => $room->id,
        'row_name' => 'B',
        'seat_number' => 1,
        'seat_type' => 'Regular',
        'status' => 'AVAILABLE',
    ]);

    $movie = Movie::create([
        'title' => 'Test Movie 3',
        'duration' => 120,
        'status' => 'NOW_SHOWING',
        'release_date' => now()->subDays(5),
    ]);

    $showtime = Showtime::create([
        'movie_id' => $movie->id,
        'room_id' => $room->id,
        'start_time' => now()->addHours(3),
        'end_time' => now()->addHours(5),
        'status' => Showtime::STATUS_SCHEDULED,
    ]);

    \App\Models\TicketPrice::create([
        'showtime_id' => $showtime->id,
        'seat_type' => 'Regular',
        'price' => 100000,
        'status' => 'ACTIVE',
    ]);

    $combo = \App\Models\Combo::create([
        'name' => 'Combo Bap Nuoc VIP',
        'price' => 80000,
        'status' => 'ACTIVE',
    ]);

    config(['booking.seat_hold.allow_boundary_orphan_seat' => true]);

    // 1. Initial checkout init with seat 1 and combo
    $response = $this->actingAs($user)->post(route('checkout.init'), [
        'showtime_id' => $showtime->id,
        'seat_ids' => "{$seat1->id}",
        'combos' => json_encode([
            $combo->id => ['qty' => 2]
        ]),
    ]);

    $response->assertRedirect(route('checkout', ['showtime_id' => $showtime->id]));

    // Check DB has combo recorded
    $booking = Booking::where('user_id', $user->id)->where('status', 'Pending')->first();
    expect($booking)->not->toBeNull();
    $bookingCombos = DB::table('booking_combos')->where('booking_id', $booking->id)->get();
    expect($bookingCombos->count())->toBe(1);
    expect($bookingCombos->first()->combo_id)->toBe($combo->id);
    expect($bookingCombos->first()->quantity)->toBe(2);

    // 2. User goes back to seat map, adds seat 2, and submits init with preserved combos
    $response2 = $this->actingAs($user)->from(route('booking.select-seats', ['showtime' => $showtime->id]))->post(route('checkout.init'), [
        'showtime_id' => $showtime->id,
        'seat_ids' => "{$seat1->id},{$seat2->id}",
        'combos' => json_encode([
            $combo->id => ['qty' => 2]
        ]),
    ]);

    $response2->assertRedirect(route('checkout', ['showtime_id' => $showtime->id]));

    // Check new active pending booking still preserved the combos
    $newPendingBooking = Booking::where('user_id', $user->id)->where('status', 'Pending')->first();
    expect($newPendingBooking)->not->toBeNull();
    $newBookingCombos = DB::table('booking_combos')->where('booking_id', $newPendingBooking->id)->get();
    expect($newBookingCombos->count())->toBe(1);
    expect($newBookingCombos->first()->combo_id)->toBe($combo->id);
    expect($newBookingCombos->first()->quantity)->toBe(2);
});


