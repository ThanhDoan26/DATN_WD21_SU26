<?php

use App\Models\User;
use App\Models\Role;
use App\Models\Cinema;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Movie;
use App\Models\Showtime;
use App\Models\TicketPrice;
use App\Models\Combo;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('staff walk-in booking successfully calculates and saves combos to database', function () {
    $staffRole = Role::firstOrCreate([
        'role_name' => 'STAFF'
    ], [
        'description' => 'Staff role'
    ]);

    $cinema = Cinema::create([
        'name' => 'CGV Vincom',
        'address' => '123 Ba Trieu',
        'city' => 'Hanoi',
    ]);

    $staff = User::create([
        'name' => 'Staff Tester',
        'email' => 'staff@cgv.com',
        'password' => bcrypt('password'),
        'role_id' => $staffRole->id,
        'cinema_id' => $cinema->id,
        'status' => 'ACTIVE',
    ]);

    $room = Room::create([
        'cinema_id' => $cinema->id,
        'name' => 'Room 101',
        'capacity' => 100,
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
        'title' => 'Avengers: Secret Wars',
        'duration' => 150,
        'status' => 'NOW_SHOWING',
        'release_date' => now()->subDays(2),
    ]);

    $showtime = Showtime::create([
        'movie_id' => $movie->id,
        'room_id' => $room->id,
        'start_time' => now()->addHours(2),
        'end_time' => now()->addHours(4),
        'status' => Showtime::STATUS_SCHEDULED,
    ]);

    TicketPrice::create([
        'showtime_id' => $showtime->id,
        'seat_type' => 'Regular',
        'price' => 90000,
        'status' => 'ACTIVE',
    ]);

    $combo1 = Combo::create([
        'name' => 'Bắp Ngọt + Nước',
        'price' => 75000,
        'status' => 'ACTIVE',
    ]);

    $combo2 = Combo::create([
        'name' => 'Combo Đôi Super VIP',
        'price' => 120000,
        'status' => 'ACTIVE',
    ]);

    // Expected calculations:
    // 2 seats @ 90,000 = 180,000
    // 2x combo1 @ 75,000 = 150,000
    // 1x combo2 @ 120,000 = 120,000
    // Total = 180,000 + 150,000 + 120,000 = 450,000

    $response = $this->actingAs($staff)->postJson(route('staff.walkin.reserve'), [
        'showtime_id' => $showtime->id,
        'seat_ids' => "{$seat1->id},{$seat2->id}",
        'combos' => [
            $combo1->id => ['qty' => 2],
            $combo2->id => ['qty' => 1],
        ],
        'payment_method' => 'CASH',
        'customer_name' => 'Khach Tai Quay',
        'customer_phone' => '0987654321',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'isWalkIn' => true,
    ]);

    $booking = Booking::latest('id')->first();
    expect($booking)->not->toBeNull();
    expect((float) $booking->total_price)->toBe(450000.0);
    expect($booking->status)->toBe('Paid');
    expect($booking->payment_method)->toBe('CASH');

    // Verify booking_combos table
    $savedCombos = DB::table('booking_combos')
        ->where('booking_id', $booking->id)
        ->orderBy('combo_id')
        ->get();

    expect($savedCombos->count())->toBe(2);
    expect($savedCombos[0]->combo_id)->toBe($combo1->id);
    expect($savedCombos[0]->quantity)->toBe(2);
    expect((float) $savedCombos[0]->price)->toBe(75000.0);

    expect($savedCombos[1]->combo_id)->toBe($combo2->id);
    expect($savedCombos[1]->quantity)->toBe(1);
    expect((float) $savedCombos[1]->price)->toBe(120000.0);

    // Verify Success page displays combos
    $successResponse = $this->actingAs($staff)->get(route('staff.walkin.success', ['booking_id' => $booking->id]));
    $successResponse->assertStatus(200);
    $successResponse->assertSee('Bắp Ngọt + Nước');
    $successResponse->assertSee('Combo Đôi Super VIP');
    $successResponse->assertSee(number_format(450000));
});

test('staff walk-in booking with flat quantity payload formats properly', function () {
    $staffRole = Role::firstOrCreate([
        'role_name' => 'STAFF'
    ], [
        'description' => 'Staff role'
    ]);

    $cinema = Cinema::create([
        'name' => 'CGV Landmark',
        'address' => 'Landmark 81',
        'city' => 'HCMC',
    ]);

    $staff = User::create([
        'name' => 'Staff Tester 2',
        'email' => 'staff2@cgv.com',
        'password' => bcrypt('password'),
        'role_id' => $staffRole->id,
        'cinema_id' => $cinema->id,
        'status' => 'ACTIVE',
    ]);

    $room = Room::create([
        'cinema_id' => $cinema->id,
        'name' => 'Room IMAX',
        'capacity' => 100,
        'format' => 'IMAX',
    ]);

    $seat = Seat::create([
        'room_id' => $room->id,
        'row_name' => 'B',
        'seat_number' => 5,
        'seat_type' => 'Regular',
        'status' => 'AVAILABLE',
    ]);

    $movie = Movie::create([
        'title' => 'Dune Part 3',
        'duration' => 180,
        'status' => 'NOW_SHOWING',
        'release_date' => now()->subDays(1),
    ]);

    $showtime = Showtime::create([
        'movie_id' => $movie->id,
        'room_id' => $room->id,
        'start_time' => now()->addHours(1),
        'end_time' => now()->addHours(4),
        'status' => Showtime::STATUS_SCHEDULED,
    ]);

    TicketPrice::create([
        'showtime_id' => $showtime->id,
        'seat_type' => 'Regular',
        'price' => 150000,
        'status' => 'ACTIVE',
    ]);

    $combo = Combo::create([
        'name' => 'Combo Solo',
        'price' => 60000,
        'status' => 'ACTIVE',
    ]);

    // Flat payload { combo_id: qty }
    $response = $this->actingAs($staff)->postJson(route('staff.walkin.reserve'), [
        'showtime_id' => $showtime->id,
        'seat_ids' => "{$seat->id}",
        'combos' => [
            $combo->id => 3, // 3 x 60,000 = 180,000
        ],
        'payment_method' => 'CASH',
    ]);

    $response->assertStatus(200);

    $booking = Booking::latest('id')->first();
    expect((float) $booking->total_price)->toBe(330000.0); // 150,000 + 180,000

    $savedCombos = DB::table('booking_combos')->where('booking_id', $booking->id)->get();
    expect($savedCombos->count())->toBe(1);
    expect($savedCombos->first()->quantity)->toBe(3);
});
