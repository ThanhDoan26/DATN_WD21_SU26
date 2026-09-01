<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\TicketPrice;
use App\Models\User;
use App\Models\BookedSeat;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BookingFlowFixTest extends TestCase
{
    use DatabaseTransactions;

    protected function createTestData(): array
    {
        $cinema = Cinema::create(['name' => 'Cinema Flow ' . uniqid(), 'address' => 'Address 1', 'city' => 'Hà Nội']);
        $room = Room::create(['cinema_id' => $cinema->id, 'name' => 'Room 1', 'total_seats' => 10]);
        $seat1 = Seat::create(['room_id' => $room->id, 'row_name' => 'A', 'seat_number' => 1, 'seat_type' => 'REGULAR']);
        $seat2 = Seat::create(['room_id' => $room->id, 'row_name' => 'A', 'seat_number' => 2, 'seat_type' => 'REGULAR']);
        $movie = Movie::create(['title' => 'Movie Flow ' . uniqid(), 'duration' => 120, 'status' => Movie::STATUS_NOW_SHOWING]);
        $showtime = Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->addHours(2),
            'end_time' => now()->addHours(4),
            'status' => Showtime::STATUS_SCHEDULED,
        ]);

        TicketPrice::create([
            'showtime_id' => $showtime->id,
            'seat_type' => 'REGULAR',
            'price' => 80000,
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create();

        return [$user, $showtime, $seat1, $seat2];
    }

    /**
     * Test 1: User with pending booking sees own seats in myPendingSeats and not in bookedSeats
     */
    public function test_select_seats_recognizes_user_pending_seats(): void
    {
        [$user, $showtime, $seat1, $seat2] = $this->createTestData();

        $bookingTime = now()->subMinutes(2);
        $booking = Booking::create([
            'user_id' => $user->id,
            'showtime_id' => $showtime->id,
            'total_price' => 80000,
            'status' => Booking::STATUS_PENDING,
            'booking_time' => $bookingTime,
            'booking_code' => 'BKFLOW' . uniqid(),
        ]);

        BookedSeat::create([
            'booking_id' => $booking->id,
            'seat_id' => $seat1->id,
            'price_at_booking' => 80000,
            'status' => 'RESERVED',
        ]);

        $response = $this->actingAs($user)->get(route('booking.select-seats', ['showtime' => $showtime->id]));
        $response->assertOk();
        $response->assertViewHas('myPendingSeats', [$seat1->id]);
        $response->assertViewHas('bookedSeats', []);
        
        $viewExpiresAtMs = $response->viewData('expiresAtMs');
        $this->assertNotNull($viewExpiresAtMs);
        $expectedExpiresAtMs = ($bookingTime->timestamp + 600) * 1000;
        $this->assertEquals($expectedExpiresAtMs, $viewExpiresAtMs);
    }

    /**
     * Test 2: F5 on checkout page preserves pending booking and does not redirect to select-seats
     */
    public function test_checkout_index_preserves_pending_booking_on_reload(): void
    {
        [$user, $showtime, $seat1, $seat2] = $this->createTestData();

        $bookingTime = now()->subMinutes(3);
        $booking = Booking::create([
            'user_id' => $user->id,
            'showtime_id' => $showtime->id,
            'total_price' => 80000,
            'status' => Booking::STATUS_PENDING,
            'booking_time' => $bookingTime,
            'booking_code' => 'BKFLOW' . uniqid(),
        ]);

        BookedSeat::create([
            'booking_id' => $booking->id,
            'seat_id' => $seat1->id,
            'price_at_booking' => 80000,
            'status' => 'RESERVED',
        ]);

        session(['current_booking_id' => $booking->id, 'current_showtime_id' => $showtime->id]);

        $response = $this->actingAs($user)->get(route('checkout', [
            'showtime_id' => $showtime->id,
            'booking_id' => $booking->id,
        ]));

        $response->assertOk();
        $response->assertViewIs('checkout');
        $response->assertViewHas('pendingBookingId', $booking->id);

        $viewExpiresAtMs = $response->viewData('expiresAtMs');
        $this->assertNotNull($viewExpiresAtMs);
        $expectedExpiresAtMs = ($bookingTime->timestamp + 600) * 1000;
        $this->assertEquals($expectedExpiresAtMs, $viewExpiresAtMs);
    }
}
