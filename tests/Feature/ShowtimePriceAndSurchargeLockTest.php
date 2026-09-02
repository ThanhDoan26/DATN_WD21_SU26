<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Role;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\TicketPrice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowtimePriceAndSurchargeLockTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Cinema $cinema;
    protected Room $room;
    protected Movie $movie;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['role_name' => 'ADMIN'], ['description' => 'Administrator']);
        $this->admin = User::factory()->create([
            'role_id' => $role->id,
            'status' => 'ACTIVE',
        ]);

        $this->cinema = Cinema::create([
            'name' => 'MovieGo Central',
            'city' => 'Hà Nội',
            'address' => '123 Kim Ma',
            'status' => 'ACTIVE',
        ]);

        $this->room = Room::create([
            'cinema_id' => $this->cinema->id,
            'name' => 'Screen 1',
            'format' => '2D',
            'status' => 'ACTIVE',
        ]);

        $this->movie = Movie::create([
            'title' => 'Dune 2',
            'status' => Movie::STATUS_NOW_SHOWING,
            'duration' => 120,
            'age_rating' => 'T13',
            'release_date' => Carbon::now()->subDays(5),
            'trailer_url' => 'https://youtube.com/watch?v=123',
            'poster_url' => 'posters/dune.jpg',
            'format' => ['2D'],
        ]);
    }

    public function test_showtime_without_bookings_can_update_prices_and_surcharge(): void
    {
        $showtime = Showtime::create([
            'movie_id' => $this->movie->id,
            'room_id' => $this->room->id,
            'start_time' => Carbon::now()->addDays(2)->setHour(14)->setMinute(0),
            'end_time' => Carbon::now()->addDays(2)->setHour(16)->setMinute(15),
            'status' => Showtime::STATUS_SCHEDULED,
            'surcharge' => 10000,
        ]);

        TicketPrice::create([
            'showtime_id' => $showtime->id,
            'seat_type' => 'Regular',
            'price' => 80000,
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.showtimes.update', $showtime->id), [
            'movie_id' => $this->movie->id,
            'room_id' => $this->room->id,
            'start_time' => Carbon::now()->addDays(2)->setHour(14)->setMinute(0)->format('Y-m-d H:i:s'),
            'status' => Showtime::STATUS_SCHEDULED,
            'surcharge' => 20000,
            'ticket_prices' => [
                'Regular' => 90000,
            ],
        ]);

        $response->assertRedirect(route('admin.showtimes.index'));
        $showtime->refresh();
        $this->assertEquals(20000, (float)$showtime->surcharge);
        $this->assertEquals(90000, (float)$showtime->ticketPrices()->where('seat_type', 'Regular')->first()->price);
    }

    public function test_showtime_with_bookings_cannot_change_surcharge(): void
    {
        $showtime = Showtime::create([
            'movie_id' => $this->movie->id,
            'room_id' => $this->room->id,
            'start_time' => Carbon::now()->addDays(2)->setHour(14)->setMinute(0),
            'end_time' => Carbon::now()->addDays(2)->setHour(16)->setMinute(15),
            'status' => Showtime::STATUS_SCHEDULED,
            'surcharge' => 15000,
        ]);

        TicketPrice::create([
            'showtime_id' => $showtime->id,
            'seat_type' => 'Regular',
            'price' => 80000,
            'status' => 'ACTIVE',
        ]);

        // Create an active paid booking for this showtime
        Booking::create([
            'user_id' => $this->admin->id,
            'showtime_id' => $showtime->id,
            'booking_code' => 'BOOK123',
            'booking_time' => Carbon::now(),
            'total_price' => 95000,
            'status' => Booking::STATUS_PAID,
            'payment_method' => 'VNPAY',
        ]);

        $response = $this->actingAs($this->admin)->from(route('admin.showtimes.edit', $showtime->id))->put(route('admin.showtimes.update', $showtime->id), [
            'movie_id' => $this->movie->id,
            'room_id' => $this->room->id,
            'start_time' => $showtime->start_time->format('Y-m-d H:i:s'),
            'status' => Showtime::STATUS_SCHEDULED,
            'surcharge' => 25000, // Attempt to modify surcharge
            'ticket_prices' => [
                'Regular' => 80000,
            ],
        ]);

        $response->assertRedirect(route('admin.showtimes.edit', $showtime->id));
        $response->assertSessionHasErrors('surcharge');
        $showtime->refresh();
        $this->assertEquals(15000, (float)$showtime->surcharge);
    }

    public function test_showtime_with_bookings_cannot_change_ticket_prices(): void
    {
        $showtime = Showtime::create([
            'movie_id' => $this->movie->id,
            'room_id' => $this->room->id,
            'start_time' => Carbon::now()->addDays(2)->setHour(14)->setMinute(0),
            'end_time' => Carbon::now()->addDays(2)->setHour(16)->setMinute(15),
            'status' => Showtime::STATUS_SCHEDULED,
            'surcharge' => 15000,
        ]);

        TicketPrice::create([
            'showtime_id' => $showtime->id,
            'seat_type' => 'Regular',
            'price' => 80000,
            'status' => 'ACTIVE',
        ]);

        // Create an active paid booking for this showtime
        Booking::create([
            'user_id' => $this->admin->id,
            'showtime_id' => $showtime->id,
            'booking_code' => 'BOOK124',
            'booking_time' => Carbon::now(),
            'total_price' => 95000,
            'status' => Booking::STATUS_PAID,
            'payment_method' => 'VNPAY',
        ]);

        $response = $this->actingAs($this->admin)->from(route('admin.showtimes.edit', $showtime->id))->put(route('admin.showtimes.update', $showtime->id), [
            'movie_id' => $this->movie->id,
            'room_id' => $this->room->id,
            'start_time' => $showtime->start_time->format('Y-m-d H:i:s'),
            'status' => Showtime::STATUS_SCHEDULED,
            'surcharge' => 15000,
            'ticket_prices' => [
                'Regular' => 120000, // Attempt to modify ticket price
            ],
        ]);

        $response->assertRedirect(route('admin.showtimes.edit', $showtime->id));
        $response->assertSessionHasErrors('ticket_prices');
        $showtime->refresh();
        $this->assertEquals(80000, (float)$showtime->ticketPrices()->where('seat_type', 'Regular')->first()->price);
    }

    public function test_showtime_with_bookings_retains_prices_when_inputs_omitted(): void
    {
        $showtime = Showtime::create([
            'movie_id' => $this->movie->id,
            'room_id' => $this->room->id,
            'start_time' => Carbon::now()->addDays(2)->setHour(14)->setMinute(0),
            'end_time' => Carbon::now()->addDays(2)->setHour(16)->setMinute(15),
            'status' => Showtime::STATUS_SCHEDULED,
            'surcharge' => 15000,
        ]);

        TicketPrice::create([
            'showtime_id' => $showtime->id,
            'seat_type' => 'Regular',
            'price' => 80000,
            'status' => 'ACTIVE',
        ]);

        Booking::create([
            'user_id' => $this->admin->id,
            'showtime_id' => $showtime->id,
            'booking_code' => 'BOOK125',
            'booking_time' => Carbon::now(),
            'total_price' => 95000,
            'status' => Booking::STATUS_PAID,
            'payment_method' => 'VNPAY',
        ]);

        // Submitting with empty/omitted surcharge and ticket prices (disabled in UI)
        $response = $this->actingAs($this->admin)->put(route('admin.showtimes.update', $showtime->id), [
            'status' => Showtime::STATUS_SCHEDULED,
        ]);

        $response->assertRedirect(route('admin.showtimes.index'));
        $showtime->refresh();
        $this->assertEquals(15000, (float)$showtime->surcharge);
        $this->assertEquals(80000, (float)$showtime->ticketPrices()->where('seat_type', 'Regular')->first()->price);
    }
}
