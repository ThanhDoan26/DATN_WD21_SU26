<?php

use App\Models\Booking;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Support\Facades\DB;

it('rejects booking requests with more than ten seats', function () {
    $service = new BookingService();

    expect(fn () => $service->createBooking(1, 1, range(1, 11)))
        ->toThrow(Exception::class, 'Bạn chỉ được đặt tối đa 10 vé cho mỗi đơn.');
});

it('counts existing tickets per movie instead of across all movies', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test-' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
    ]);

    $movieA = Movie::create([
        'title' => 'Movie A',
        'description' => 'Test movie A',
        'duration' => 120,
        'status' => 'ACTIVE',
    ]);

    $movieB = Movie::create([
        'title' => 'Movie B',
        'description' => 'Test movie B',
        'duration' => 120,
        'status' => 'ACTIVE',
    ]);

    $cinema = Cinema::create([
        'name' => 'Test Cinema',
        'address' => 'Test Address',
        'city' => 'Test City',
        'status' => 'ACTIVE',
    ]);

    $room = Room::create([
        'cinema_id' => $cinema->id,
        'name' => 'Room 1',
        'format' => '2D',
        'total_seats' => 100,
        'status' => 'ACTIVE',
    ]);

    for ($i = 1; $i <= 11; $i++) {
        Seat::create([
            'room_id' => $room->id,
            'row_name' => 'A',
            'seat_number' => $i,
            'seat_type' => 'Regular',
            'status' => 'AVAILABLE',
        ]);
    }

    $showtimeA = Showtime::create([
        'movie_id' => $movieA->id,
        'room_id' => $room->id,
        'start_time' => now()->addDay(),
        'end_time' => now()->addDay()->addHours(2),
        'status' => Showtime::STATUS_SCHEDULED,
    ]);

    $showtimeB = Showtime::create([
        'movie_id' => $movieB->id,
        'room_id' => $room->id,
        'start_time' => now()->addDays(2),
        'end_time' => now()->addDays(2)->addHours(2),
        'status' => Showtime::STATUS_SCHEDULED,
    ]);

    $bookingA = Booking::create([
        'user_id' => $user->id,
        'showtime_id' => $showtimeA->id,
        'total_price' => 0,
        'status' => 'Paid',
        'booking_code' => 'BK-TEST-A',
        'booking_time' => now(),
    ]);

    for ($i = 1; $i <= 10; $i++) {
        DB::table('booked_seats')->insert([
            'booking_id' => $bookingA->id,
            'seat_id' => $i,
            'price_at_booking' => 0,
            'status' => 'RESERVED',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $bookingB = Booking::create([
        'user_id' => $user->id,
        'showtime_id' => $showtimeB->id,
        'total_price' => 0,
        'status' => 'Paid',
        'booking_code' => 'BK-TEST-B',
        'booking_time' => now(),
    ]);

    DB::table('booked_seats')->insert([
        'booking_id' => $bookingB->id,
        'seat_id' => 11,
        'price_at_booking' => 0,
        'status' => 'RESERVED',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = new BookingService();

    expect($service->getUserBookedSeatCount($user->id, $movieA->id))->toBe(10)
        ->and($service->getUserBookedSeatCount($user->id, $movieB->id))->toBe(1);
});
