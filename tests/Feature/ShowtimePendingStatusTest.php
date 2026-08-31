<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use App\Models\TicketPrice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowtimePendingStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Movie $movie;
    private Cinema $cinema;
    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $role = \App\Models\Role::firstOrCreate(['role_name' => 'ADMIN'], ['description' => 'Administrator']);
        $this->admin = User::factory()->create([
            'role_id' => $role->id,
            'status' => 'ACTIVE',
        ]);

        $this->cinema = Cinema::create([
            'name' => 'CGV Landmark 81',
            'slug' => 'cgv-landmark-81',
            'city' => 'Hồ Chí Minh',
            'address' => '720A Điện Biên Phủ',
            'status' => 'ACTIVE',
        ]);

        $this->room = Room::create([
            'cinema_id' => $this->cinema->id,
            'name' => 'Cinema 1',
            'capacity' => 50,
            'format' => '2D',
            'status' => 'ACTIVE',
        ]);

        $category = Category::create([
            'name' => 'Action',
            'slug' => 'action-' . uniqid(),
        ]);

        $this->movie = Movie::create([
            'title' => 'Mission Impossible 8',
            'status' => Movie::STATUS_NOW_SHOWING,
            'duration' => 120,
            'age_rating' => 'T13',
            'format' => ['2D'],
            'release_date' => Carbon::now()->subDays(5),
            'trailer_url' => 'https://youtube.com/watch?v=test',
            'poster_url' => 'posters/mi8.jpg',
        ]);
        $this->movie->categories()->attach($category->id);
    }

    public function test_admin_can_update_showtime_status_to_pending_and_db_stores_pending(): void
    {
        $showtime = Showtime::create([
            'movie_id' => $this->movie->id,
            'room_id' => $this->room->id,
            'start_time' => Carbon::now()->addDays(2)->setTime(14, 0),
            'end_time' => Carbon::now()->addDays(2)->setTime(16, 15),
            'status' => Showtime::STATUS_SCHEDULED,
            'surcharge' => 0,
        ]);

        TicketPrice::create([
            'showtime_id' => $showtime->id,
            'seat_type' => 'Regular',
            'price' => 75000,
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.showtimes.update', $showtime->id), [
            'movie_id' => $this->movie->id,
            'room_id' => $this->room->id,
            'start_time' => Carbon::now()->addDays(2)->setTime(14, 0)->format('Y-m-d H:i:s'),
            'status' => Showtime::STATUS_PENDING,
            'ticket_prices' => ['Regular' => 75000],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.showtimes.index'));

        $showtime->refresh();
        $this->assertEquals(Showtime::STATUS_PENDING, $showtime->status);
        $this->assertNotEquals(Showtime::STATUS_CANCELLED, $showtime->status);
    }

    public function test_sync_all_statuses_does_not_cancel_or_modify_pending_showtimes(): void
    {
        $pendingShowtime = Showtime::create([
            'movie_id' => $this->movie->id,
            'room_id' => $this->room->id,
            'start_time' => Carbon::now()->addDays(3)->setTime(10, 0),
            'end_time' => Carbon::now()->addDays(3)->setTime(12, 15),
            'status' => Showtime::STATUS_PENDING,
            'surcharge' => 0,
        ]);

        Showtime::syncAllStatuses();

        $pendingShowtime->refresh();
        $this->assertEquals(Showtime::STATUS_PENDING, $pendingShowtime->status);
    }

    public function test_admin_index_displays_pending_badge_correctly(): void
    {
        $pendingShowtime = Showtime::create([
            'movie_id' => $this->movie->id,
            'room_id' => $this->room->id,
            'start_time' => Carbon::now()->addDays(3)->setTime(10, 0),
            'end_time' => Carbon::now()->addDays(3)->setTime(12, 15),
            'status' => Showtime::STATUS_PENDING,
            'surcharge' => 0,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.showtimes.index'));

        $response->assertStatus(200);
        $response->assertSee('<span class="badge bg-warning text-dark">Chờ công bố (PENDING)</span>', false);
    }

    public function test_pending_showtime_is_hidden_from_customer_booking_and_details(): void
    {
        $pendingShowtime = Showtime::create([
            'movie_id' => $this->movie->id,
            'room_id' => $this->room->id,
            'start_time' => Carbon::now()->addDays(3)->setTime(10, 0),
            'end_time' => Carbon::now()->addDays(3)->setTime(12, 15),
            'status' => Showtime::STATUS_PENDING,
            'surcharge' => 0,
        ]);

        // Customer cannot book online
        $this->assertFalse($pendingShowtime->isOnlineBookable());

        // Booking dates API does not return this date if only pending showtimes exist
        $dateResponse = $this->getJson(route('api.booking.dates', [
            'movie_id' => $this->movie->id,
            'cinema_id' => $this->cinema->id,
        ]));

        $dateResponse->assertStatus(200);
        $this->assertEmpty($dateResponse->json('data'));

        // Booking selectSeats redirects to home with error
        $selectSeatsResponse = $this->get(route('booking.select-seats', $pendingShowtime->id));
        $selectSeatsResponse->assertRedirect(route('home'));
    }
}
