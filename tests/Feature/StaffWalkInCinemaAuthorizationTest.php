<?php

use App\Models\Booking;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\TicketPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function createCinemaWithRoomAndShowtime(string $cinemaName): array
{
    $cinema = Cinema::create([
        'name' => $cinemaName . ' ' . uniqid(),
        'address' => '123 Address',
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
        'seat_number' => 1,
        'seat_type' => 'Regular',
        'status' => Seat::STATUS_AVAILABLE,
    ]);

    $movie = Movie::create([
        'title' => 'Test Movie ' . uniqid(),
        'description' => 'Movie Description',
        'duration' => 120,
        'status' => 'NOW_SHOWING',
    ]);

    $showtime = Showtime::create([
        'movie_id' => $movie->id,
        'room_id' => $room->id,
        'start_time' => now()->addHour(),
        'end_time' => now()->addHours(3),
        'status' => Showtime::STATUS_SCHEDULED,
        'surcharge' => 0,
    ]);

    TicketPrice::create([
        'showtime_id' => $showtime->id,
        'seat_type' => 'Regular',
        'price' => 60000,
        'status' => 'ACTIVE',
    ]);

    return [
        'cinema' => $cinema,
        'room' => $room,
        'seat' => $seat,
        'movie' => $movie,
        'showtime' => $showtime,
    ];
}

it('prevents staff of Cinema A from viewing seat map of Cinema B showtime', function () {
    $cinemaAData = createCinemaWithRoomAndShowtime('Cinema A');
    $cinemaBData = createCinemaWithRoomAndShowtime('Cinema B');

    $staffA = User::create([
        'name' => 'Staff Cinema A',
        'email' => 'staffA_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role' => 'STAFF',
        'cinema_id' => $cinemaAData['cinema']->id,
        'status' => 'ACTIVE',
    ]);

    $response = $this->actingAs($staffA)->get(route('staff.walkin.seats', ['showtime' => $cinemaBData['showtime']->id]));

    $response->assertStatus(403);
});

it('prevents staff of Cinema A from accessing checkout with Cinema B showtime', function () {
    $cinemaAData = createCinemaWithRoomAndShowtime('Cinema A');
    $cinemaBData = createCinemaWithRoomAndShowtime('Cinema B');

    $staffA = User::create([
        'name' => 'Staff Cinema A',
        'email' => 'staffA_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role' => 'STAFF',
        'cinema_id' => $cinemaAData['cinema']->id,
        'status' => 'ACTIVE',
    ]);

    $response = $this->actingAs($staffA)->get(route('staff.walkin.checkout', [
        'showtime_id' => $cinemaBData['showtime']->id,
        'seat_ids' => $cinemaBData['seat']->id,
    ]));

    $response->assertStatus(403);
});

it('prevents staff of Cinema A from reserving seats for Cinema B showtime', function () {
    $cinemaAData = createCinemaWithRoomAndShowtime('Cinema A');
    $cinemaBData = createCinemaWithRoomAndShowtime('Cinema B');

    $staffA = User::create([
        'name' => 'Staff Cinema A',
        'email' => 'staffA_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role' => 'STAFF',
        'cinema_id' => $cinemaAData['cinema']->id,
        'status' => 'ACTIVE',
    ]);

    $initialBookingsCount = Booking::count();

    $response = $this->actingAs($staffA)->postJson(route('staff.walkin.reserve'), [
        'showtime_id' => $cinemaBData['showtime']->id,
        'seat_ids' => [$cinemaBData['seat']->id],
        'payment_method' => 'CASH',
    ]);

    $response->assertStatus(403);
    $response->assertJson([
        'success' => false,
    ]);

    // Ensure no booking or hold was created in database
    expect(Booking::count())->toBe($initialBookingsCount);
    expect(DB::table('booked_seats')->where('seat_id', $cinemaBData['seat']->id)->count())->toBe(0);
});

it('prevents staff of Cinema A from viewing success page of Cinema B booking', function () {
    $cinemaAData = createCinemaWithRoomAndShowtime('Cinema A');
    $cinemaBData = createCinemaWithRoomAndShowtime('Cinema B');

    $staffA = User::create([
        'name' => 'Staff Cinema A',
        'email' => 'staffA_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role' => 'STAFF',
        'cinema_id' => $cinemaAData['cinema']->id,
        'status' => 'ACTIVE',
    ]);

    $bookingB = Booking::create([
        'user_id' => null,
        'showtime_id' => $cinemaBData['showtime']->id,
        'total_price' => 60000,
        'status' => 'Paid',
        'booking_code' => 'BK-B-' . uniqid(),
        'booking_time' => now(),
    ]);

    $response = $this->actingAs($staffA)->get(route('staff.walkin.success', ['booking_id' => $bookingB->id]));

    $response->assertStatus(403);
});

it('allows staff of Cinema A to view seat map and reserve for Cinema A showtime', function () {
    $cinemaAData = createCinemaWithRoomAndShowtime('Cinema A');

    $staffA = User::create([
        'name' => 'Staff Cinema A',
        'email' => 'staffA_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role' => 'STAFF',
        'cinema_id' => $cinemaAData['cinema']->id,
        'status' => 'ACTIVE',
    ]);

    // Can access seat map
    $seatResponse = $this->actingAs($staffA)->get(route('staff.walkin.seats', ['showtime' => $cinemaAData['showtime']->id]));
    $seatResponse->assertStatus(200);

    // Can reserve
    $reserveResponse = $this->actingAs($staffA)->postJson(route('staff.walkin.reserve'), [
        'showtime_id' => $cinemaAData['showtime']->id,
        'seat_ids' => [$cinemaAData['seat']->id],
        'payment_method' => 'CASH',
    ]);

    $reserveResponse->assertStatus(200);
    $reserveResponse->assertJson([
        'success' => true,
    ]);

    $createdBooking = Booking::where('showtime_id', $cinemaAData['showtime']->id)->first();
    expect($createdBooking)->not->toBeNull();
    expect($createdBooking->status)->toBe('Paid');
});
