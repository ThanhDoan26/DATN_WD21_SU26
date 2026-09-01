<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Role;
use App\Models\Room;
use App\Models\Showtime;
use App\Models\User;
use App\Services\MovieStatusValidationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ShowtimeStatusValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Cinema $cinema;
    protected Room $room;
    protected Movie $movie;
    protected MovieStatusValidationService $service;

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
            'title' => 'Avengers Secret Wars',
            'status' => Movie::STATUS_NOW_SHOWING,
            'duration' => 120,
            'age_rating' => 'T13',
            'release_date' => Carbon::now()->subDays(5),
            'trailer_url' => 'https://youtube.com/watch?v=123',
            'poster_url' => 'posters/avengers.jpg',
            'format' => ['2D'],
        ]);

        $this->service = new MovieStatusValidationService();
    }

    /** 1. Time-based Rules: SCHEDULED or PENDING requires start_time > now() */
    public function test_scheduled_or_pending_requires_future_start_time(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->validateShowtimeStatusRules(null, [
            'movie_id' => $this->movie->id,
            'start_time' => Carbon::now()->subHours(1)->format('Y-m-d H:i:s'),
            'status' => Showtime::STATUS_SCHEDULED,
        ], $this->movie);
    }

    /** 1b. Time-based Rules: SCHEDULED requires start_time >= movie.release_date */
    public function test_scheduled_requires_start_time_after_movie_release_date(): void
    {
        $futureMovie = Movie::create([
            'title' => 'Future Movie',
            'status' => Movie::STATUS_NOW_SHOWING,
            'duration' => 120,
            'age_rating' => 'P',
            'release_date' => Carbon::now()->addDays(10),
            'trailer_url' => 'https://youtube.com/watch?v=future',
            'poster_url' => 'posters/future.jpg',
            'format' => ['2D'],
        ]);

        $this->expectException(ValidationException::class);

        $this->service->validateShowtimeStatusRules(null, [
            'movie_id' => $futureMovie->id,
            'start_time' => Carbon::now()->addDays(2)->format('Y-m-d H:i:s'),
            'status' => Showtime::STATUS_SCHEDULED,
        ], $futureMovie);
    }

    /** 1c. Time-based Rules: ONGOING requires start_time <= now() AND end_time >= now() */
    public function test_ongoing_requires_start_time_past_and_end_time_future(): void
    {
        // Case: start_time is in the future -> should fail
        try {
            $this->service->validateShowtimeStatusRules(null, [
                'movie_id' => $this->movie->id,
                'start_time' => Carbon::now()->addHours(1)->format('Y-m-d H:i:s'),
                'end_time' => Carbon::now()->addHours(3)->format('Y-m-d H:i:s'),
                'status' => Showtime::STATUS_ONGOING,
            ], $this->movie);
            $this->fail('Expected ValidationException when setting ONGOING with future start_time');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
        }

        // Case: valid ONGOING -> start_time = -30 mins, end_time = +60 mins -> should pass
        $this->service->validateShowtimeStatusRules(null, [
            'movie_id' => $this->movie->id,
            'start_time' => Carbon::now()->subMinutes(30)->format('Y-m-d H:i:s'),
            'end_time' => Carbon::now()->addMinutes(60)->format('Y-m-d H:i:s'),
            'status' => Showtime::STATUS_ONGOING,
        ], $this->movie);
        $this->assertTrue(true);
    }

    /** 1d. Time-based Rules: FINISHED / COMPLETED requires end_time < now() */
    public function test_finished_requires_end_time_in_past(): void
    {
        // Case: end_time in future -> should fail
        try {
            $this->service->validateShowtimeStatusRules(null, [
                'movie_id' => $this->movie->id,
                'start_time' => Carbon::now()->subHours(1)->format('Y-m-d H:i:s'),
                'end_time' => Carbon::now()->addHours(1)->format('Y-m-d H:i:s'),
                'status' => Showtime::STATUS_COMPLETED,
            ], $this->movie);
            $this->fail('Expected ValidationException when setting COMPLETED with future end_time');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
        }

        // Case: end_time in past -> should pass
        $this->service->validateShowtimeStatusRules(null, [
            'movie_id' => $this->movie->id,
            'start_time' => Carbon::now()->subHours(3)->format('Y-m-d H:i:s'),
            'end_time' => Carbon::now()->subHour()->format('Y-m-d H:i:s'),
            'status' => Showtime::STATUS_COMPLETED,
        ], $this->movie);
        $this->assertTrue(true);
    }

    /** 2. Movie Status: movie.status === 'SCHEDULED' allows showtime to be SCHEDULED or PENDING */
    public function test_movie_scheduled_allows_scheduled_and_pending(): void
    {
        $scheduledMovie = Movie::create([
            'title' => 'Scheduled Movie',
            'status' => Movie::STATUS_SCHEDULED,
            'duration' => 100,
            'age_rating' => 'P',
            'release_date' => Carbon::now()->addMonth(),
            'trailer_url' => 'https://youtube.com/watch?v=sched',
            'poster_url' => 'posters/sched.jpg',
            'format' => ['2D'],
        ]);

        // Setting SCHEDULED status on a SCHEDULED movie -> must pass
        $this->service->validateShowtimeStatusRules(null, [
            'movie_id' => $scheduledMovie->id,
            'start_time' => Carbon::now()->addMonth()->addHours(2)->format('Y-m-d H:i:s'),
            'status' => Showtime::STATUS_SCHEDULED,
        ], $scheduledMovie);
        $this->assertTrue(true);

        // Setting PENDING status on a SCHEDULED movie -> must also pass
        $this->service->validateShowtimeStatusRules(null, [
            'movie_id' => $scheduledMovie->id,
            'start_time' => Carbon::now()->addMonth()->addHours(2)->format('Y-m-d H:i:s'),
            'status' => Showtime::STATUS_PENDING,
        ], $scheduledMovie);
        $this->assertTrue(true);
    }

    /** 3. Active Booking Protection: if showtime has bookings, block changing status to PENDING or CANCELLED */
    public function test_active_booking_protection_blocks_pending_and_cancelled(): void
    {
        $showtime = Showtime::create([
            'movie_id' => $this->movie->id,
            'room_id' => $this->room->id,
            'start_time' => Carbon::now()->addDays(2),
            'end_time' => Carbon::now()->addDays(2)->addHours(2),
            'status' => Showtime::STATUS_SCHEDULED,
            'surcharge' => 0,
        ]);

        Booking::create([
            'user_id' => $this->admin->id,
            'showtime_id' => $showtime->id,
            'booking_code' => 'TEST_BOOKING_001',
            'booking_time' => Carbon::now(),
            'total_price' => 100000,
            'status' => 'SUCCESS',
            'payment_method' => 'VNPAY',
        ]);

        // Try changing to PENDING -> must fail
        try {
            $this->service->validateShowtimeStatusRules($showtime, [
                'movie_id' => $this->movie->id,
                'start_time' => Carbon::now()->addDays(2)->format('Y-m-d H:i:s'),
                'status' => Showtime::STATUS_PENDING,
            ], $this->movie);
            $this->fail('Expected ValidationException when changing status to PENDING with active bookings');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
            $this->assertStringContainsString('PENDING', $e->errors()['status'][0]);
        }

        // Try changing to CANCELLED -> must fail
        try {
            $this->service->validateShowtimeStatusRules($showtime, [
                'movie_id' => $this->movie->id,
                'start_time' => Carbon::now()->addDays(2)->format('Y-m-d H:i:s'),
                'status' => Showtime::STATUS_CANCELLED,
            ], $this->movie);
            $this->fail('Expected ValidationException when changing status to CANCELLED with active bookings');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
            $this->assertStringContainsString('CANCELLED', $e->errors()['status'][0]);
        }
    }

    /** 4. Terminal State Lock: if existing status is FINISHED/COMPLETED or CANCELLED, block update */
    public function test_terminal_state_lock_blocks_updates(): void
    {
        $completedShowtime = Showtime::create([
            'movie_id' => $this->movie->id,
            'room_id' => $this->room->id,
            'start_time' => Carbon::now()->subDays(2),
            'end_time' => Carbon::now()->subDays(2)->addHours(2),
            'status' => Showtime::STATUS_COMPLETED,
            'surcharge' => 0,
        ]);

        $cancelledShowtime = Showtime::create([
            'movie_id' => $this->movie->id,
            'room_id' => $this->room->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addHours(2),
            'status' => Showtime::STATUS_CANCELLED,
            'surcharge' => 0,
        ]);

        // Attempt update on COMPLETED showtime -> must fail
        try {
            $this->service->validateShowtimeStatusRules($completedShowtime, [
                'movie_id' => $this->movie->id,
                'start_time' => Carbon::now()->subDays(2)->format('Y-m-d H:i:s'),
                'status' => Showtime::STATUS_COMPLETED,
            ], $this->movie);
            $this->fail('Expected ValidationException when editing COMPLETED showtime');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
            $this->assertStringContainsString('kết thúc', $e->errors()['status'][0]);
        }

        // Attempt update on CANCELLED showtime -> must fail
        try {
            $this->service->validateShowtimeStatusRules($cancelledShowtime, [
                'movie_id' => $this->movie->id,
                'start_time' => Carbon::now()->addDays(1)->format('Y-m-d H:i:s'),
                'status' => Showtime::STATUS_SCHEDULED,
            ], $this->movie);
            $this->fail('Expected ValidationException when editing CANCELLED showtime');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
            $this->assertStringContainsString('kết thúc', $e->errors()['status'][0]);
        }
    }
}
