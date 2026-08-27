<?php

use App\Models\BookedSeat;
use App\Models\Booking;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\TicketPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects same pending online selection back to checkout instead of generating a duplicate booking', function () {
    $customer = User::create([
        'name' => 'Customer',
        'email' => 'customer_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role_id' => 1,
        'status' => 'ACTIVE',
    ]);

    $cinema = Cinema::create([
        'name' => 'Cinema A',
        'address' => '123 Street',
        'city' => 'Hanoi',
        'status' => 'ACTIVE',
    ]);

    $room = Room::create([
        'cinema_id' => $cinema->id,
        'name' => 'Room 1',
        'format' => '2D',
        'total_seats' => 20,
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
        'title' => 'Test Movie',
        'description' => 'Movie Description',
        'duration' => 120,
        'status' => 'NOW_SHOWING',
    ]);

    $showtime = Showtime::create([
        'movie_id' => $movie->id,
        'room_id' => $room->id,
        'start_time' => now()->addHour(),
        'end_time' => now()->addHours(2),
        'status' => Showtime::STATUS_SCHEDULED,
        'surcharge' => 0,
    ]);

    TicketPrice::create([
        'showtime_id' => $showtime->id,
        'seat_type' => 'Regular',
        'price' => 60000,
        'status' => 'ACTIVE',
    ]);

    $existingBooking = Booking::create([
        'user_id' => $customer->id,
        'showtime_id' => $showtime->id,
        'total_price' => 60000,
        'status' => 'Pending',
        'booking_code' => 'BK-ONLINE-OLD-' . uniqid(),
        'booking_time' => now(),
    ]);

    BookedSeat::create([
        'booking_id' => $existingBooking->id,
        'seat_id' => $seat->id,
        'price_at_booking' => 60000,
        'status' => 'RESERVED',
    ]);

    $this->actingAs($customer);
    $response = $this->post(route('checkout.init'), [
        'showtime_id' => $showtime->id,
        'seat_ids' => [$seat->id],
    ]);

    $response->assertRedirect(route('checkout', ['showtime_id' => $showtime->id]));
    expect(Booking::where('user_id', $customer->id)->where('showtime_id', $showtime->id)->count())->toBe(1);
});

it('blocks staff checkout when seat is already selected by online customer', function () {
    $cinema = Cinema::create([
        'name' => 'Cinema A',
        'address' => '123 Street',
        'city' => 'Hanoi',
        'status' => 'ACTIVE',
    ]);

    $room = Room::create([
        'cinema_id' => $cinema->id,
        'name' => 'Room 1',
        'format' => '2D',
        'total_seats' => 20,
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
        'title' => 'Test Movie',
        'description' => 'Movie Description',
        'duration' => 120,
        'status' => 'NOW_SHOWING',
    ]);

    $showtime = Showtime::create([
        'movie_id' => $movie->id,
        'room_id' => $room->id,
        'start_time' => now()->addHour(),
        'end_time' => now()->addHours(2),
        'status' => Showtime::STATUS_SCHEDULED,
        'surcharge' => 0,
    ]);

    TicketPrice::create([
        'showtime_id' => $showtime->id,
        'seat_type' => 'Regular',
        'price' => 60000,
        'status' => 'ACTIVE',
    ]);

    $customer = User::create([
        'name' => 'Customer',
        'email' => 'customer_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role_id' => 1,
        'status' => 'ACTIVE',
    ]);

    $onlineBooking = Booking::create([
        'user_id' => $customer->id,
        'showtime_id' => $showtime->id,
        'total_price' => 60000,
        'status' => 'Pending',
        'booking_code' => 'BK-ONLINE-' . uniqid(),
        'booking_time' => now(),
    ]);

    BookedSeat::create([
        'booking_id' => $onlineBooking->id,
        'seat_id' => $seat->id,
        'price_at_booking' => 60000,
        'status' => 'RESERVED',
    ]);

    $staff = User::create([
        'name' => 'Staff Cinema A',
        'email' => 'staff_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role_id' => 2,
        'cinema_id' => $cinema->id,
        'status' => 'ACTIVE',
    ]);

    $response = $this->actingAs($staff)->postJson(route('staff.walkin.reserve'), [
        'showtime_id' => $showtime->id,
        'seat_ids' => [$seat->id],
        'payment_method' => 'CASH',
    ]);

    $response->assertStatus(409);
    $response->assertJsonPath('success', false);
    $response->assertJsonFragment(['message' => 'Ghế A1 đã được khách chọn và đã có người đặt/giữ. Vui lòng chọn ghế khác.']);
});

it('redirects online checkout when staff already holds the selected seat before customer pays', function () {
    $customer = User::create([
        'name' => 'Customer',
        'email' => 'customer_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role_id' => 1,
        'status' => 'ACTIVE',
    ]);

    $cinema = Cinema::create([
        'name' => 'Cinema A',
        'address' => '123 Street',
        'city' => 'Hanoi',
        'status' => 'ACTIVE',
    ]);

    $room = Room::create([
        'cinema_id' => $cinema->id,
        'name' => 'Room 1',
        'format' => '2D',
        'total_seats' => 20,
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
        'title' => 'Test Movie',
        'description' => 'Movie Description',
        'duration' => 120,
        'status' => 'NOW_SHOWING',
    ]);

    $showtime = Showtime::create([
        'movie_id' => $movie->id,
        'room_id' => $room->id,
        'start_time' => now()->addHour(),
        'end_time' => now()->addHours(2),
        'status' => Showtime::STATUS_SCHEDULED,
        'surcharge' => 0,
    ]);

    TicketPrice::create([
        'showtime_id' => $showtime->id,
        'seat_type' => 'Regular',
        'price' => 60000,
        'status' => 'ACTIVE',
    ]);

    $staff = User::create([
        'name' => 'Staff Cinema A',
        'email' => 'staff_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role_id' => 2,
        'cinema_id' => $cinema->id,
        'status' => 'ACTIVE',
    ]);

    $staffBooking = Booking::create([
        'user_id' => null,
        'showtime_id' => $showtime->id,
        'total_price' => 60000,
        'status' => 'Pending',
        'booking_code' => 'BK-WALKIN-' . uniqid(),
        'booking_time' => now(),
        'booking_source' => 'walk_in',
    ]);

    BookedSeat::create([
        'booking_id' => $staffBooking->id,
        'seat_id' => $seat->id,
        'price_at_booking' => 60000,
        'status' => 'RESERVED',
    ]);

    $this->actingAs($customer);
    $response = $this->post(route('checkout.init'), [
        'showtime_id' => $showtime->id,
        'seat_ids' => [$seat->id],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', fn ($message) => str_contains($message, 'Ghế') && str_contains($message, 'đã được'));
});
