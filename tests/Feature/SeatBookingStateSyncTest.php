<?php

use App\Models\Booking;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\User;
use App\Services\SeatBookingStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function createTestCinemaRoomAndSeat(): array
{
    $cinema = Cinema::create([
        'name' => 'Test Cinema ' . uniqid(),
        'address' => '123 Test St',
        'city' => 'Hanoi',
        'status' => 'ACTIVE',
    ]);

    $room = Room::create([
        'cinema_id' => $cinema->id,
        'name' => 'Room 1',
        'format' => '2D',
        'total_seats' => 50,
        'status' => 'ACTIVE',
    ]);

    $seat = Seat::create([
        'room_id' => $room->id,
        'row_name' => 'A',
        'seat_number' => 5,
        'seat_type' => 'Regular',
        'status' => Seat::STATUS_AVAILABLE,
    ]);

    $movie = Movie::create([
        'title' => 'Test Movie ' . uniqid(),
        'description' => 'Test Movie Description',
        'duration' => 120,
        'status' => 'ACTIVE',
    ]);

    $showtime = Showtime::create([
        'movie_id' => $movie->id,
        'room_id' => $room->id,
        'start_time' => now()->addHours(2),
        'end_time' => now()->addHours(4),
        'status' => Showtime::STATUS_SCHEDULED,
    ]);

    $user = User::create([
        'name' => 'Test Customer',
        'email' => 'user-' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
    ]);

    return compact('cinema', 'room', 'seat', 'movie', 'showtime', 'user');
}

// ─────────────────────────────────────────────────────────────
// CASE 1: AVAILABLE SEAT (No booking, no hold)
// ─────────────────────────────────────────────────────────────
it('allows locking/unlocking when seat has no booking and no hold', function () {
    $data = createTestCinemaRoomAndSeat();
    $service = new SeatBookingStateService();

    $check = $service->checkSeatLockable($data['seat']);

    expect($check['allowed'])->toBeTrue()
        ->and($check['code'])->toBe('OK')
        ->and($check['status'])->toBe(Seat::STATUS_AVAILABLE);
});

// ─────────────────────────────────────────────────────────────
// CASE 2: ACTIVE HOLD (< 10 minutes)
// ─────────────────────────────────────────────────────────────
it('rejects locking when seat is under active hold (< 10 minutes)', function () {
    $data = createTestCinemaRoomAndSeat();

    $booking = Booking::create([
        'user_id' => $data['user']->id,
        'showtime_id' => $data['showtime']->id,
        'total_price' => 100000,
        'status' => 'Pending',
        'booking_code' => 'BK-' . uniqid(),
        'booking_time' => now()->subMinutes(3), // 3 mins ago (< 10 mins)
    ]);

    DB::table('booked_seats')->insert([
        'booking_id' => $booking->id,
        'seat_id' => $data['seat']->id,
        'price_at_booking' => 100000,
        'status' => 'RESERVED',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = new SeatBookingStateService();
    $check = $service->checkSeatLockable($data['seat']);

    expect($check['allowed'])->toBeFalse()
        ->and($check['code'])->toBe('SEAT_HAS_ACTIVE_HOLD')
        ->and($check['status'])->toBe('HELD');
});

// ─────────────────────────────────────────────────────────────
// CASE 3: EXPIRED HOLD (> 10 minutes)
// ─────────────────────────────────────────────────────────────
it('allows locking when seat hold is expired (> 10 minutes) and not paid', function () {
    $data = createTestCinemaRoomAndSeat();

    $booking = Booking::create([
        'user_id' => $data['user']->id,
        'showtime_id' => $data['showtime']->id,
        'total_price' => 100000,
        'status' => 'Pending',
        'booking_code' => 'BK-' . uniqid(),
        'booking_time' => now()->subMinutes(15), // 15 mins ago (> 10 mins)
    ]);

    DB::table('booked_seats')->insert([
        'booking_id' => $booking->id,
        'seat_id' => $data['seat']->id,
        'price_at_booking' => 100000,
        'status' => 'RESERVED',
        'created_at' => now()->subMinutes(15),
        'updated_at' => now()->subMinutes(15),
    ]);

    $service = new SeatBookingStateService();
    $check = $service->checkSeatLockable($data['seat']);

    expect($check['allowed'])->toBeTrue()
        ->and($check['code'])->toBe('OK');
});

// ─────────────────────────────────────────────────────────────
// CASE 4: PAID / COMPLETED BOOKING
// ─────────────────────────────────────────────────────────────
it('rejects locking when seat is booked/paid', function () {
    $data = createTestCinemaRoomAndSeat();

    $booking = Booking::create([
        'user_id' => $data['user']->id,
        'showtime_id' => $data['showtime']->id,
        'total_price' => 100000,
        'status' => 'Paid',
        'booking_code' => 'BK-' . uniqid(),
        'booking_time' => now()->subHours(1),
        'payment_time' => now()->subHours(1),
    ]);

    DB::table('booked_seats')->insert([
        'booking_id' => $booking->id,
        'seat_id' => $data['seat']->id,
        'price_at_booking' => 100000,
        'status' => 'PAID',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = new SeatBookingStateService();
    $check = $service->checkSeatLockable($data['seat']);

    expect($check['allowed'])->toBeFalse()
        ->and($check['code'])->toBe('SEAT_ALREADY_BOOKED')
        ->and($check['status'])->toBe('BOOKED');
});

// ─────────────────────────────────────────────────────────────
// CASE 5: CANCELLED BOOKING
// ─────────────────────────────────────────────────────────────
it('allows locking when previous booking was cancelled', function () {
    $data = createTestCinemaRoomAndSeat();

    $booking = Booking::create([
        'user_id' => $data['user']->id,
        'showtime_id' => $data['showtime']->id,
        'total_price' => 100000,
        'status' => 'Cancelled',
        'booking_code' => 'BK-' . uniqid(),
        'booking_time' => now()->subHours(2),
        'cancelled_at' => now()->subHours(1),
    ]);

    DB::table('booked_seats')->insert([
        'booking_id' => $booking->id,
        'seat_id' => $data['seat']->id,
        'price_at_booking' => 100000,
        'status' => 'CANCELLED',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = new SeatBookingStateService();
    $check = $service->checkSeatLockable($data['seat']);

    expect($check['allowed'])->toBeTrue()
        ->and($check['code'])->toBe('OK');
});

// ─────────────────────────────────────────────────────────────
// CASE 6: MULTI-SHOWTIME / ROOM-LEVEL PROTECTION
// ─────────────────────────────────────────────────────────────
it('protects physical seat if any active showtime has a hold or booking', function () {
    $data = createTestCinemaRoomAndSeat();

    // Create Showtime 2 for the same room (later today)
    $showtime2 = Showtime::create([
        'movie_id' => $data['movie']->id,
        'room_id' => $data['room']->id,
        'start_time' => now()->addHours(6),
        'end_time' => now()->addHours(8),
        'status' => Showtime::STATUS_SCHEDULED,
    ]);

    // Hold seat in Showtime 2 only
    $booking = Booking::create([
        'user_id' => $data['user']->id,
        'showtime_id' => $showtime2->id,
        'total_price' => 100000,
        'status' => 'Pending',
        'booking_code' => 'BK-' . uniqid(),
        'booking_time' => now()->subMinutes(2),
    ]);

    DB::table('booked_seats')->insert([
        'booking_id' => $booking->id,
        'seat_id' => $data['seat']->id,
        'price_at_booking' => 100000,
        'status' => 'RESERVED',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = new SeatBookingStateService();
    $check = $service->checkSeatLockable($data['seat']);

    expect($check['allowed'])->toBeFalse()
        ->and($check['code'])->toBe('SEAT_HAS_ACTIVE_HOLD');
});

// ─────────────────────────────────────────────────────────────
// CASE 7: ENRICH SEATS FOR MAP / API
// ─────────────────────────────────────────────────────────────
it('enriches seat collection with correct is_held, is_booked, can_toggle, business_status attributes', function () {
    $data = createTestCinemaRoomAndSeat();

    $seatHeld = $data['seat'];

    $seatBooked = Seat::create([
        'room_id' => $data['room']->id,
        'row_name' => 'A',
        'seat_number' => 6,
        'seat_type' => 'Regular',
        'status' => Seat::STATUS_AVAILABLE,
    ]);

    $seatFree = Seat::create([
        'room_id' => $data['room']->id,
        'row_name' => 'A',
        'seat_number' => 7,
        'seat_type' => 'Regular',
        'status' => Seat::STATUS_AVAILABLE,
    ]);

    // Create hold on seatHeld
    $bookingHeld = Booking::create([
        'user_id' => $data['user']->id,
        'showtime_id' => $data['showtime']->id,
        'total_price' => 100000,
        'status' => 'Pending',
        'booking_code' => 'BK-HELD',
        'booking_time' => now()->subMinutes(2),
    ]);
    DB::table('booked_seats')->insert([
        'booking_id' => $bookingHeld->id,
        'seat_id' => $seatHeld->id,
        'price_at_booking' => 100000,
        'status' => 'RESERVED',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Create paid booking on seatBooked
    $bookingPaid = Booking::create([
        'user_id' => $data['user']->id,
        'showtime_id' => $data['showtime']->id,
        'total_price' => 100000,
        'status' => 'Paid',
        'booking_code' => 'BK-PAID',
        'booking_time' => now()->subHours(1),
        'payment_time' => now()->subHours(1),
    ]);
    DB::table('booked_seats')->insert([
        'booking_id' => $bookingPaid->id,
        'seat_id' => $seatBooked->id,
        'price_at_booking' => 100000,
        'status' => 'PAID',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = new SeatBookingStateService();
    $seats = $service->enrichSeatsWithBookingState(
        collect([$seatHeld, $seatBooked, $seatFree]),
        $data['room']->id
    );

    $heldResult = $seats->firstWhere('id', $seatHeld->id);
    expect($heldResult->is_held)->toBeTrue()
        ->and($heldResult->is_booked)->toBeFalse()
        ->and($heldResult->can_toggle)->toBeFalse()
        ->and($heldResult->business_status)->toBe('HELD');

    $bookedResult = $seats->firstWhere('id', $seatBooked->id);
    expect($bookedResult->is_held)->toBeFalse()
        ->and($bookedResult->is_booked)->toBeTrue()
        ->and($bookedResult->can_toggle)->toBeFalse()
        ->and($bookedResult->business_status)->toBe('BOOKED');

    $freeResult = $seats->firstWhere('id', $seatFree->id);
    expect($freeResult->is_held)->toBeFalse()
        ->and($freeResult->is_booked)->toBeFalse()
        ->and($freeResult->can_toggle)->toBeTrue()
        ->and($freeResult->business_status)->toBe(Seat::STATUS_AVAILABLE);
});

// ─────────────────────────────────────────────────────────────
// CASE 8: COMPLETE PAYMENT TESTS
// ─────────────────────────────────────────────────────────────
it('allows completePayment to succeed when booking status is Pending', function () {
    $data = createTestCinemaRoomAndSeat();

    $booking = Booking::create([
        'user_id' => $data['user']->id,
        'showtime_id' => $data['showtime']->id,
        'total_price' => 100000,
        'status' => 'Pending',
        'booking_code' => 'BK-PEND-' . uniqid(),
        'booking_time' => now()->subMinutes(1),
    ]);

    DB::table('booked_seats')->insert([
        'booking_id' => $booking->id,
        'seat_id' => $data['seat']->id,
        'price_at_booking' => 100000,
        'status' => 'RESERVED',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $bookingService = new \App\Services\BookingService();
    $result = $bookingService->completePayment($booking->id, 'MOCK_PAYMENT', []);

    expect($result)->toBeTrue();

    $updatedBooking = Booking::find($booking->id);
    expect($updatedBooking->status)->toBe('Paid')
        ->and($updatedBooking->payment_method)->toBe('MOCK_PAYMENT')
        ->and($updatedBooking->payment_time)->not->toBeNull();

    $bookedSeat = DB::table('booked_seats')->where('booking_id', $booking->id)->first();
    expect($bookedSeat->status)->toBe('PAID');
});

it('allows completePayment to succeed when booking status is PROCESSING', function () {
    $data = createTestCinemaRoomAndSeat();

    $booking = Booking::create([
        'user_id' => $data['user']->id,
        'showtime_id' => $data['showtime']->id,
        'total_price' => 100000,
        'status' => 'PROCESSING',
        'booking_code' => 'BK-PROC-' . uniqid(),
        'booking_time' => now()->subMinutes(1),
    ]);

    DB::table('booked_seats')->insert([
        'booking_id' => $booking->id,
        'seat_id' => $data['seat']->id,
        'price_at_booking' => 100000,
        'status' => 'RESERVED',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $bookingService = new \App\Services\BookingService();
    $result = $bookingService->completePayment($booking->id, 'VNPAY', []);

    expect($result)->toBeTrue();

    $updatedBooking = Booking::find($booking->id);
    expect($updatedBooking->status)->toBe('Paid')
        ->and($updatedBooking->payment_method)->toBe('VNPAY')
        ->and($updatedBooking->payment_time)->not->toBeNull();

    $bookedSeat = DB::table('booked_seats')->where('booking_id', $booking->id)->first();
    expect($bookedSeat->status)->toBe('PAID');
});

it('throws exception when completePayment is called on an already Paid booking', function () {
    $data = createTestCinemaRoomAndSeat();

    $booking = Booking::create([
        'user_id' => $data['user']->id,
        'showtime_id' => $data['showtime']->id,
        'total_price' => 100000,
        'status' => 'Paid',
        'booking_code' => 'BK-PAID-' . uniqid(),
        'booking_time' => now()->subMinutes(5),
    ]);

    $bookingService = new \App\Services\BookingService();

    expect(fn () => $bookingService->completePayment($booking->id, 'VNPAY', []))
        ->toThrow(\Exception::class, "Không thể thanh toán booking này. Status: Paid.");
});

it('throws exception when completePayment is called for a non-existent booking', function () {
    $bookingService = new \App\Services\BookingService();
    $nonExistentId = 9999999;

    expect(fn () => $bookingService->completePayment($nonExistentId, 'VNPAY', []))
        ->toThrow(\Exception::class, "Booking $nonExistentId không tồn tại");
});

// ─────────────────────────────────────────────────────────────
// CASE 9: SEAT ROOM INTEGRITY TESTS
// ─────────────────────────────────────────────────────────────
it('throws exception when createBooking is called with seats from different rooms', function () {
    $dataRoom1 = createTestCinemaRoomAndSeat();

    // Create a second room and seat in the same cinema
    $room2 = Room::create([
        'cinema_id' => $dataRoom1['room']->cinema_id,
        'name' => 'Room 2',
        'format' => '2D',
        'total_seats' => 50,
        'status' => 'ACTIVE',
    ]);

    $seatRoom2 = Seat::create([
        'room_id' => $room2->id,
        'row_name' => 'A',
        'seat_number' => 1,
        'seat_type' => 'Regular',
        'status' => Seat::STATUS_AVAILABLE,
    ]);

    $bookingService = new \App\Services\BookingService();

    // Showtime belongs to room 1, but we request seats from both room 1 and room 2
    expect(fn () => $bookingService->createBooking(
        $dataRoom1['user']->id,
        $dataRoom1['showtime']->id,
        [$dataRoom1['seat']->id, $seatRoom2->id]
    ))->toThrow(\Exception::class, "không thuộc phòng chiếu của suất chiếu này");
});

it('throws exception in SeatSelectionValidationService when seat does not belong to showtime room', function () {
    $dataRoom1 = createTestCinemaRoomAndSeat();

    $room2 = Room::create([
        'cinema_id' => $dataRoom1['room']->cinema_id,
        'name' => 'Room 2',
        'format' => '2D',
        'total_seats' => 50,
        'status' => 'ACTIVE',
    ]);

    $seatRoom2 = Seat::create([
        'room_id' => $room2->id,
        'row_name' => 'A',
        'seat_number' => 1,
        'seat_type' => 'Regular',
        'status' => Seat::STATUS_AVAILABLE,
    ]);

    $validator = new \App\Services\SeatSelectionValidationService();

    expect(fn () => $validator->validateSelectedSeats(
        $dataRoom1['showtime']->id,
        [$seatRoom2->id]
    ))->toThrow(\Exception::class, "không thuộc phòng chiếu của suất chiếu này");
});


