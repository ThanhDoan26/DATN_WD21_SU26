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
use App\Models\Role;
use App\Models\BookedSeat;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HeavyLogicFixesTest extends TestCase
{
    use DatabaseTransactions;

    protected function getAdmin(): User
    {
        $role = Role::firstOrCreate(['role_name' => 'ADMIN'], ['description' => 'Administrator']);
        return User::firstOrCreate([
            'email' => 'admin_heavy_test@moviego.vn'
        ], [
            'name' => 'Admin Heavy Test',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
            'status' => 'ACTIVE',
        ]);
    }

    /**
     * Test 1: Soft-deleted movie does not block creating a new movie with the same title
     */
    public function test_can_create_movie_with_same_title_as_soft_deleted_movie(): void
    {
        $admin = $this->getAdmin();
        $title = 'Avengers Secret Wars ' . uniqid();

        // Create movie and then soft delete it
        $softDeletedMovie = Movie::create([
            'title' => $title,
            'duration' => 150,
            'status' => Movie::STATUS_COMING_SOON,
            'release_date' => now()->addMonths(2)->format('Y-m-d'),
        ]);
        $softDeletedMovie->delete();
        $this->assertSoftDeleted('movies', ['id' => $softDeletedMovie->id]);

        // Creating a new movie with the same title should succeed
        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => $title,
            'duration' => 160,
            'status' => Movie::STATUS_COMING_SOON,
            'release_date' => now()->addMonths(3)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('admin.movies.index'));
        $this->assertDatabaseHas('movies', [
            'title' => $title,
            'deleted_at' => null,
        ]);
    }

    /**
     * Test 2: Active duplicate movie title fails validation
     */
    public function test_cannot_create_movie_with_same_title_as_active_movie(): void
    {
        $admin = $this->getAdmin();
        $title = 'Avatar The Way Of Water ' . uniqid();

        Movie::create([
            'title' => $title,
            'duration' => 190,
            'status' => Movie::STATUS_NOW_SHOWING,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => $title,
            'duration' => 190,
            'status' => Movie::STATUS_NOW_SHOWING,
        ]);

        $response->assertSessionHasErrors('title');
    }

    /**
     * Test 3: Soft-deleted cinema does not block creating a new cinema with the same name
     */
    public function test_can_create_cinema_with_same_name_as_soft_deleted_cinema(): void
    {
        $admin = $this->getAdmin();
        $cinemaName = 'CGV Vincom Landmark ' . uniqid();

        $softDeletedCinema = Cinema::create([
            'name' => $cinemaName,
            'address' => '72 Le Thanh Ton, Q1',
            'city' => 'Hồ Chí Minh',
            'status' => 'ACTIVE',
        ]);
        $softDeletedCinema->delete();
        $this->assertSoftDeleted('cinemas', ['id' => $softDeletedCinema->id]);

        // Creating new cinema with same name should succeed
        $response = $this->actingAs($admin)->post(route('admin.cinemas.store'), [
            'name' => $cinemaName,
            'address' => '72 Le Thanh Ton, Ben Nghe, Q1',
            'city' => 'Hồ Chí Minh',
            'status' => 'ACTIVE',
        ]);

        $response->assertRedirect(route('admin.cinemas.index'));
        $this->assertDatabaseHas('cinemas', [
            'name' => $cinemaName,
            'deleted_at' => null,
        ]);
    }

    /**
     * Test 4: Booking Model mutator and accessor normalize status to lowercase
     */
    public function test_booking_status_is_normalized_to_lowercase(): void
    {
        $booking = new Booking();
        $booking->status = 'Pending';
        $this->assertEquals('pending', $booking->status);
        $this->assertTrue($booking->isPending());

        $booking->status = 'PAID';
        $this->assertEquals('paid', $booking->status);
        $this->assertTrue($booking->isPaid());

        $booking->status = 'Cancelled';
        $this->assertEquals('cancelled', $booking->status);
        $this->assertTrue($booking->isCancelled());

        $booking->status = 'EXPIRED';
        $this->assertEquals('expired', $booking->status);
        $this->assertTrue($booking->isExpired());
    }

    /**
     * Test 5: API release-hold-seats cancels pending booking and returns success
     */
    public function test_beacon_release_hold_seats_cancels_pending_booking(): void
    {
        $cinema = Cinema::create(['name' => 'Cinema Beta ' . uniqid(), 'address' => 'Address 1', 'city' => 'Hà Nội']);
        $room = Room::create(['cinema_id' => $cinema->id, 'name' => 'Room 1', 'total_seats' => 10]);
        $seat = Seat::create(['room_id' => $room->id, 'row_name' => 'A', 'seat_number' => 1, 'seat_type' => 'REGULAR']);
        $movie = Movie::create(['title' => 'Movie Beta ' . uniqid(), 'duration' => 120, 'status' => Movie::STATUS_NOW_SHOWING]);
        $showtime = Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->addHours(2),
            'end_time' => now()->addHours(4),
            'status' => Showtime::STATUS_SCHEDULED,
        ]);

        $user = User::factory()->create();

        $booking = Booking::create([
            'user_id' => $user->id,
            'showtime_id' => $showtime->id,
            'total_price' => 100000,
            'status' => Booking::STATUS_PENDING,
            'booking_time' => now(),
            'booking_code' => 'BKTEST' . uniqid(),
        ]);

        BookedSeat::create([
            'booking_id' => $booking->id,
            'seat_id' => $seat->id,
            'price_at_booking' => 100000,
            'status' => 'RESERVED',
        ]);

        // Call Beacon endpoint
        $response = $this->postJson('/api/v1/bookings/release-hold-seats', [
            'booking_id' => $booking->id,
            'showtime_id' => $showtime->id,
            'seat_ids' => $seat->id,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        // Check booking is cancelled and seat is cancelled
        $freshBooking = Booking::find($booking->id);
        $this->assertEquals(Booking::STATUS_CANCELLED, $freshBooking->status);
        $this->assertDatabaseHas('booked_seats', [
            'booking_id' => $booking->id,
            'seat_id' => $seat->id,
            'status' => 'CANCELLED',
        ]);
    }

    /**
     * Test 6: Restoring a movie with duplicate title (active movie exists) is blocked
     */
    public function test_restore_movie_blocked_when_duplicate_title_exists(): void
    {
        $admin = $this->getAdmin();
        $title = 'Duplicate Restore Movie ' . uniqid();

        // 1. Create a movie and soft-delete it
        $trashedMovie = Movie::create([
            'title' => $title,
            'duration' => 120,
            'status' => Movie::STATUS_COMING_SOON,
            'release_date' => now()->addMonths(1)->format('Y-m-d'),
        ]);
        $trashedMovie->delete();
        $this->assertSoftDeleted('movies', ['id' => $trashedMovie->id]);

        // 2. Create a NEW active movie with the same title
        Movie::create([
            'title' => $title,
            'duration' => 130,
            'status' => Movie::STATUS_COMING_SOON,
            'release_date' => now()->addMonths(2)->format('Y-m-d'),
        ]);

        // 3. Attempt to restore the trashed movie – must be blocked
        $response = $this->actingAs($admin)
            ->post(route('admin.movies.restore', $trashedMovie->id));

        $response->assertRedirect(route('admin.movies.trashed'));
        $response->assertSessionHas('error');

        // The trashed movie must remain soft-deleted
        $this->assertSoftDeleted('movies', ['id' => $trashedMovie->id]);
    }

    /**
     * Test 7: Restoring a cinema with duplicate name (active cinema exists) is blocked
     */
    public function test_restore_cinema_blocked_when_duplicate_name_exists(): void
    {
        $admin = $this->getAdmin();
        $cinemaName = 'Duplicate Restore Cinema ' . uniqid();

        // 1. Create a cinema and soft-delete it
        $trashedCinema = Cinema::create([
            'name' => $cinemaName,
            'address' => '1 Test Street',
            'city' => 'Hà Nội',
        ]);
        $trashedCinema->delete();
        $this->assertSoftDeleted('cinemas', ['id' => $trashedCinema->id]);

        // 2. Create a NEW active cinema with the same name
        Cinema::create([
            'name' => $cinemaName,
            'address' => '2 Test Street',
            'city' => 'Hà Nội',
        ]);

        // 3. Attempt to restore the trashed cinema – must be blocked
        $response = $this->actingAs($admin)
            ->post(route('admin.cinemas.restore', $trashedCinema->id));

        $response->assertRedirect(route('admin.cinemas.index'));
        $response->assertSessionHas('error');

        // The trashed cinema must remain soft-deleted
        $this->assertSoftDeleted('cinemas', ['id' => $trashedCinema->id]);
    }
}
