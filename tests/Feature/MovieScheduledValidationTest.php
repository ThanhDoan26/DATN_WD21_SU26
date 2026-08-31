<?php

use App\Exceptions\MovieScheduledException;
use App\Models\Category;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Role;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\TicketPrice;
use App\Models\User;
use App\Services\BookingService;
use App\Services\MovieStatusValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function getAdminUser(): User
{
    $role = Role::firstOrCreate(['role_name' => 'ADMIN'], ['description' => 'Administrator']);
    return User::factory()->create([
        'role_id' => $role->id,
        'status' => 'ACTIVE',
    ]);
}

function getStaffUser(Cinema $cinema): User
{
    $role = Role::firstOrCreate(['role_name' => 'STAFF'], ['description' => 'Staff']);
    return User::factory()->create([
        'role_id' => $role->id,
        'cinema_id' => $cinema->id,
        'status' => 'ACTIVE',
    ]);
}

function createSampleCinemaAndRoom(): array
{
    $cinema = Cinema::create([
        'name' => 'CGV Landmark ' . uniqid(),
        'address' => '720A Dien Bien Phu',
        'city' => 'HCM',
        'status' => 'ACTIVE',
    ]);

    $room = Room::create([
        'cinema_id' => $cinema->id,
        'name' => 'Hall 1',
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

    return [$cinema, $room, $seat];
}

/* =========================================================================
 * 1. Data Completeness Validation (Required Fields & Past Release Date)
 * ========================================================================= */

describe('Scheduled Movie Data Completeness Validation', function () {
    test('it throws validation exception when required metadata is missing', function () {
        $service = new MovieStatusValidationService();

        // 1. Missing Title
        expect(fn () => $service->validateScheduledMetadata([
            'poster' => UploadedFile::fake()->image('poster.jpg'),
            'trailer_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration' => 120,
            'age_rating' => 'T13',
            'categories' => [1],
            'release_date' => now()->addDays(10)->toDateTimeString(),
        ]))->toThrow(ValidationException::class);

        // 2. Missing Poster
        expect(fn () => $service->validateScheduledMetadata([
            'title' => 'Avatar 3',
            'trailer_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration' => 120,
            'age_rating' => 'T13',
            'categories' => [1],
            'release_date' => now()->addDays(10)->toDateTimeString(),
        ]))->toThrow(ValidationException::class);

        // 3. Missing Trailer URL
        expect(fn () => $service->validateScheduledMetadata([
            'title' => 'Avatar 3',
            'poster' => UploadedFile::fake()->image('poster.jpg'),
            'duration' => 120,
            'age_rating' => 'T13',
            'categories' => [1],
            'release_date' => now()->addDays(10)->toDateTimeString(),
        ]))->toThrow(ValidationException::class);

        // 4. Missing/Invalid Duration
        expect(fn () => $service->validateScheduledMetadata([
            'title' => 'Avatar 3',
            'poster' => UploadedFile::fake()->image('poster.jpg'),
            'trailer_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration' => 0,
            'age_rating' => 'T13',
            'categories' => [1],
            'release_date' => now()->addDays(10)->toDateTimeString(),
        ]))->toThrow(ValidationException::class);

        // 5. Missing Age Rating
        expect(fn () => $service->validateScheduledMetadata([
            'title' => 'Avatar 3',
            'poster' => UploadedFile::fake()->image('poster.jpg'),
            'trailer_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration' => 120,
            'categories' => [1],
            'release_date' => now()->addDays(10)->toDateTimeString(),
        ]))->toThrow(ValidationException::class);

        // 6. Missing Genre / Category
        expect(fn () => $service->validateScheduledMetadata([
            'title' => 'Avatar 3',
            'poster' => UploadedFile::fake()->image('poster.jpg'),
            'trailer_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration' => 120,
            'age_rating' => 'T13',
            'categories' => [],
            'release_date' => now()->addDays(10)->toDateTimeString(),
        ]))->toThrow(ValidationException::class);

        // 7. Missing release_date
        expect(fn () => $service->validateScheduledMetadata([
            'title' => 'Avatar 3',
            'poster' => UploadedFile::fake()->image('poster.jpg'),
            'trailer_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration' => 120,
            'age_rating' => 'T13',
            'categories' => [1],
        ]))->toThrow(ValidationException::class);
    });

    test('it rejects past release_date for scheduled movie', function () {
        $service = new MovieStatusValidationService();

        // Release date in the past
        expect(fn () => $service->validateScheduledMetadata([
            'title' => 'Avatar 3',
            'poster' => UploadedFile::fake()->image('poster.jpg'),
            'trailer_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration' => 120,
            'age_rating' => 'T13',
            'categories' => [1],
            'release_date' => now()->subDay()->toDateTimeString(),
        ]))->toThrow(ValidationException::class);
    });

    test('it rejects presale_date later than release_date', function () {
        $service = new MovieStatusValidationService();

        expect(fn () => $service->validateScheduledMetadata([
            'title' => 'Avatar 3',
            'poster' => UploadedFile::fake()->image('poster.jpg'),
            'trailer_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration' => 120,
            'age_rating' => 'T13',
            'categories' => [1],
            'release_date' => now()->addDays(10)->toDateTimeString(),
            'presale_date' => now()->addDays(15)->toDateTimeString(),
        ]))->toThrow(ValidationException::class);
    });

    test('admin store endpoint validates scheduled movie metadata', function () {
        Storage::fake('public');
        $admin = getAdminUser();
        $category = Category::create(['name' => 'Action', 'slug' => 'action-' . uniqid()]);

        // Missing trailer_url & release_date in past
        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Upcoming Blockbuster',
            'status' => 'SCHEDULED',
            'duration' => 120,
            'age_rating' => 'T13',
            'categories' => [$category->id],
            'release_date' => now()->subDay()->toDateTimeString(),
        ]);

        $response->assertSessionHasErrors(['poster', 'trailer_url', 'release_date']);
    });
});

/* =========================================================================
 * 2. Showtime Creation for Scheduled Movie Allows SCHEDULED and PENDING
 * ========================================================================= */

describe('Showtime Status for Scheduled Movie', function () {
    test('creating showtime for scheduled movie defaults and saves as SCHEDULED when selected', function () {
        $category = Category::create(['name' => 'Sci-Fi', 'slug' => 'sci-fi-' . uniqid()]);
        $movie = Movie::create([
            'title' => 'Dune Part 3',
            'status' => Movie::STATUS_SCHEDULED,
            'duration' => 150,
            'age_rating' => 'T13',
            'format' => ['2D'],
            'trailer_url' => 'https://youtube.com/watch?v=test',
            'poster_url' => 'posters/test.jpg',
            'release_date' => now()->addDays(30),
        ]);
        $movie->categories()->attach($category->id);

        [$cinema, $room, $seat] = createSampleCinemaAndRoom();
        $admin = getAdminUser();

        $startTime = now()->addDays(31)->setTime(19, 0);

        $response = $this->actingAs($admin)->post(route('admin.showtimes.store'), [
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => $startTime->format('Y-m-d H:i:s'),
            'status' => Showtime::STATUS_SCHEDULED,
            'ticket_prices' => [
                'Regular' => 75000,
            ],
        ]);

        $showtime = Showtime::where('movie_id', $movie->id)->first();
        expect($showtime)->not->toBeNull();
        expect($showtime->status)->toBe(Showtime::STATUS_SCHEDULED);
        expect($showtime->isOnlineBookable())->toBeTrue();
        expect($showtime->isWalkInBookable())->toBeTrue();
    });

    test('admin can optionally choose PENDING status for draft showtime', function () {
        $category = Category::create(['name' => 'Action', 'slug' => 'action-' . uniqid()]);
        $movie = Movie::create([
            'title' => 'Avatar 3 Test',
            'status' => Movie::STATUS_SCHEDULED,
            'duration' => 180,
            'age_rating' => 'T13',
            'format' => ['2D'],
            'trailer_url' => 'https://youtube.com/watch?v=test',
            'poster_url' => 'posters/test.jpg',
            'release_date' => now()->addDays(20),
        ]);
        $movie->categories()->attach($category->id);

        [$cinema, $room, $seat] = createSampleCinemaAndRoom();
        $admin = getAdminUser();

        $startTime = now()->addDays(21)->setTime(19, 0);

        $response = $this->actingAs($admin)->post(route('admin.showtimes.store'), [
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => $startTime->format('Y-m-d H:i:s'),
            'status' => Showtime::STATUS_PENDING,
            'ticket_prices' => [
                'Regular' => 80000,
            ],
        ]);

        $showtime = Showtime::where('movie_id', $movie->id)->first();
        expect($showtime)->not->toBeNull();
        expect($showtime->status)->toBe(Showtime::STATUS_PENDING);
        expect($showtime->isOnlineBookable())->toBeFalse();
    });
});

/* =========================================================================
 * 3. Ticket Sales Allowed for SCHEDULED Showtimes of Scheduled Movie
 * ========================================================================= */

describe('Ticket Sales for Scheduled Movie', function () {
    test('BookingService allows booking when showtime is SCHEDULED', function () {
        $movie = Movie::create([
            'title' => 'Scheduled Movie Test',
            'status' => Movie::STATUS_SCHEDULED,
            'duration' => 120,
            'release_date' => now()->addDays(10),
        ]);

        [$cinema, $room, $seat] = createSampleCinemaAndRoom();

        $showtime = Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->addDays(11),
            'end_time' => now()->addDays(11)->addHours(2),
            'status' => Showtime::STATUS_SCHEDULED,
            'surcharge' => 0,
        ]);

        TicketPrice::create([
            'showtime_id' => $showtime->id,
            'seat_type' => 'Regular',
            'price' => 60000,
            'status' => 'ACTIVE',
        ]);

        $service = new BookingService();

        $bookingId = $service->createBooking(
            null,
            $showtime->id,
            [$seat->id],
            'ONLINE',
            null,
            [],
            ['booking_source' => 'online']
        );

        $booking = \App\Models\Booking::find($bookingId);
        expect($booking)->not->toBeNull();
        expect($booking->status)->toBe('Pending');
    });

    test('Web Checkout reserve endpoint succeeds for SCHEDULED showtime of scheduled movie', function () {
        $movie = Movie::create([
            'title' => 'Scheduled Movie Test 2',
            'status' => Movie::STATUS_SCHEDULED,
            'duration' => 120,
            'release_date' => now()->addDays(10),
        ]);

        [$cinema, $room, $seat] = createSampleCinemaAndRoom();

        $showtime = Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->addDays(11),
            'end_time' => now()->addDays(11)->addHours(2),
            'status' => Showtime::STATUS_SCHEDULED,
            'surcharge' => 0,
        ]);

        TicketPrice::create([
            'showtime_id' => $showtime->id,
            'seat_type' => 'Regular',
            'price' => 60000,
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create(['status' => 'ACTIVE']);

        $response = $this->actingAs($user)->postJson(route('checkout.reserve'), [
            'showtime_id' => $showtime->id,
            'seat_ids' => [$seat->id],
            'customer_name' => 'Nguyen Van A',
            'customer_phone' => '0987654321',
            'customer_email' => 'a@gmail.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    });

    test('Staff POS walk-in reserve endpoint succeeds for SCHEDULED showtime', function () {
        $movie = Movie::create([
            'title' => 'Scheduled Movie Test 3',
            'status' => Movie::STATUS_SCHEDULED,
            'duration' => 120,
            'release_date' => now()->addDays(10),
        ]);

        [$cinema, $room, $seat] = createSampleCinemaAndRoom();

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

        $staff = getStaffUser($cinema);

        $response = $this->actingAs($staff)->postJson(route('staff.walkin.reserve'), [
            'showtime_id' => $showtime->id,
            'seat_ids' => [$seat->id],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    });
});

/* =========================================================================
 * 4. Automatic Status Transition Logic (Trigger / Cronjob)
 * ========================================================================= */

describe('Automatic Status Transition Logic', function () {
    test('transitions movie from SCHEDULED to PRE_ORDER when CURRENT_TIMESTAMP >= presale_date', function () {
        $movie = Movie::create([
            'title' => 'Pre-order Transition Movie',
            'status' => Movie::STATUS_SCHEDULED,
            'duration' => 120,
            'presale_date' => now()->subMinute(), // Presale has arrived
            'release_date' => now()->addDays(5),
        ]);

        [$cinema, $room, $seat] = createSampleCinemaAndRoom();

        $pendingShowtime = Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->addDays(6),
            'end_time' => now()->addDays(6)->addHours(2),
            'status' => Showtime::STATUS_PENDING,
            'surcharge' => 0,
        ]);

        // Run sync
        $updated = Movie::syncAllStatuses();

        expect($updated)->toBeGreaterThanOrEqual(1);

        $movie->refresh();
        expect($movie->status)->toBe(Movie::STATUS_PRE_ORDER);
        expect($movie->isTicketSalesOpen())->toBeTrue();

        $pendingShowtime->refresh();
        expect($pendingShowtime->status)->toBe(Showtime::STATUS_SCHEDULED);
    });

    test('transitions movie from SCHEDULED to NOW_SHOWING when CURRENT_TIMESTAMP >= release_date', function () {
        $movie = Movie::create([
            'title' => 'Now Showing Transition Movie',
            'status' => Movie::STATUS_SCHEDULED,
            'duration' => 120,
            'release_date' => now()->subMinute(), // Release date has arrived
        ]);

        [$cinema, $room, $seat] = createSampleCinemaAndRoom();

        $pendingShowtime = Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->addHour(),
            'end_time' => now()->addHours(3),
            'status' => Showtime::STATUS_PENDING,
            'surcharge' => 0,
        ]);

        // Run sync
        $updated = Movie::syncAllStatuses();

        expect($updated)->toBeGreaterThanOrEqual(1);

        $movie->refresh();
        expect($movie->status)->toBe(Movie::STATUS_NOW_SHOWING);
        expect($movie->isTicketSalesOpen())->toBeTrue();

        $pendingShowtime->refresh();
        expect($pendingShowtime->status)->toBe(Showtime::STATUS_SCHEDULED);
    });

    test('artisan command movies:sync-statuses executes successfully', function () {
        $movie = Movie::create([
            'title' => 'Artisan Sync Test',
            'status' => Movie::STATUS_SCHEDULED,
            'duration' => 120,
            'release_date' => now()->subMinute(),
        ]);

        $exitCode = Artisan::call('movies:sync-statuses');
        expect($exitCode)->toBe(0);

        $movie->refresh();
        expect($movie->status)->toBe(Movie::STATUS_NOW_SHOWING);
    });
});
