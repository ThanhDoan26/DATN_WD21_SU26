<?php

use App\Models\Category;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Role;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

describe('Showtime Date & Time Boundary Validation Tests', function () {

    function makeAdmin(): User
    {
        $role = Role::firstOrCreate(['role_name' => 'ADMIN'], ['description' => 'Admin']);
        return User::factory()->create([
            'role_id' => $role->id,
            'status' => 'ACTIVE',
        ]);
    }

    function makeManager(Cinema $cinema): User
    {
        $role = Role::firstOrCreate(['role_name' => 'MANAGER'], ['description' => 'Manager']);
        return User::factory()->create([
            'role_id' => $role->id,
            'cinema_id' => $cinema->id,
            'status' => 'ACTIVE',
        ]);
    }

    function setupCinemaAndRoom(): array
    {
        $cinema = Cinema::create([
            'name' => 'BHD Star Test ' . uniqid(),
            'address' => '3/2 Street',
            'city' => 'HCM',
            'status' => 'ACTIVE',
        ]);

        $room = Room::create([
            'cinema_id' => $cinema->id,
            'name' => 'Room 1',
            'format' => '2D',
            'total_seats' => 30,
            'status' => 'ACTIVE',
        ]);

        return [$cinema, $room];
    }

    test('admin cannot create a showtime with start_time in the past', function () {
        $admin = makeAdmin();
        [$cinema, $room] = setupCinemaAndRoom();

        $movie = Movie::create([
            'title' => 'Test Past Showtime Movie',
            'duration' => 120,
            'format' => ['2D'],
            'status' => Movie::STATUS_NOW_SHOWING,
            'release_date' => now()->subDays(10),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.showtimes.store'), [
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->subHours(2)->format('Y-m-d H:i:s'),
            'end_time' => now()->addHours(1)->format('Y-m-d H:i:s'),
            'status' => Showtime::STATUS_SCHEDULED,
            'surcharge' => 0,
            'ticket_prices' => ['Regular' => 90000],
        ]);

        $response->assertSessionHasErrors('start_time');
    });

    test('admin cannot create a showtime before movie release_date when no presale_date exists', function () {
        $admin = makeAdmin();
        [$cinema, $room] = setupCinemaAndRoom();

        // Release date is 5 days in the future (e.g., Aug 30)
        $movie = Movie::create([
            'title' => 'Future Movie No Presale',
            'duration' => 120,
            'format' => ['2D'],
            'status' => Movie::STATUS_SCHEDULED,
            'release_date' => now()->addDays(5),
            'presale_date' => null,
        ]);

        // Attempt to create a showtime 3 days in future (Aug 28, before release_date Aug 30)
        $response = $this->actingAs($admin)->post(route('admin.showtimes.store'), [
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(3)->addHours(2)->format('Y-m-d H:i:s'),
            'status' => Showtime::STATUS_SCHEDULED,
            'surcharge' => 0,
            'ticket_prices' => ['Regular' => 90000],
        ]);

        $response->assertSessionHasErrors('start_time');
    });

    test('admin can create a sneak show showtime when start_time is >= presale_date', function () {
        $admin = makeAdmin();
        [$cinema, $room] = setupCinemaAndRoom();

        // Movie release_date is in 5 days, but presale_date is in 2 days (Sneak show allowed)
        $movie = Movie::create([
            'title' => 'Sneak Show Allowed Movie',
            'duration' => 120,
            'format' => ['2D'],
            'status' => Movie::STATUS_SCHEDULED,
            'release_date' => now()->addDays(5),
            'presale_date' => now()->addDays(2),
        ]);

        // Create showtime on day 3 (between presale_date day 2 and release_date day 5)
        $response = $this->actingAs($admin)->post(route('admin.showtimes.store'), [
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(3)->addHours(2)->format('Y-m-d H:i:s'),
            'status' => Showtime::STATUS_SCHEDULED,
            'surcharge' => 0,
            'ticket_prices' => ['Regular' => 90000],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.showtimes.index'));

        // Because movie is SCHEDULED, created showtime status is forced to PENDING
        $created = Showtime::where('movie_id', $movie->id)->first();
        expect($created)->not->toBeNull();
        expect($created->status)->toBe(Showtime::STATUS_PENDING);
    });

    test('admin cannot create showtime before presale_date when presale_date is set', function () {
        $admin = makeAdmin();
        [$cinema, $room] = setupCinemaAndRoom();

        // presale_date is in 3 days, release_date in 5 days
        $movie = Movie::create([
            'title' => 'Strict Presale Movie',
            'duration' => 120,
            'format' => ['2D'],
            'status' => Movie::STATUS_SCHEDULED,
            'release_date' => now()->addDays(5),
            'presale_date' => now()->addDays(3),
        ]);

        // Attempt to create on day 1 (before presale_date)
        $response = $this->actingAs($admin)->post(route('admin.showtimes.store'), [
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_time' => now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
            'status' => Showtime::STATUS_SCHEDULED,
            'surcharge' => 0,
            'ticket_prices' => ['Regular' => 90000],
        ]);

        $response->assertSessionHasErrors('start_time');
    });

    test('manager endpoint enforces date boundary validation when creating showtime', function () {
        [$cinema, $room] = setupCinemaAndRoom();
        $manager = makeManager($cinema);

        $movie = Movie::create([
            'title' => 'Manager Showtime Validation Movie',
            'duration' => 120,
            'format' => ['2D'],
            'status' => Movie::STATUS_SCHEDULED,
            'release_date' => now()->addDays(4),
            'presale_date' => null,
        ]);

        // Manager attempts to schedule showtime before release_date
        $response = $this->actingAs($manager)->post(route('manager.showtimes.store'), [
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(2)->addHours(2)->format('Y-m-d H:i:s'),
            'status' => Showtime::STATUS_SCHEDULED,
            'surcharge' => 0,
            'ticket_prices' => ['Regular' => 85000],
        ]);

        $response->assertSessionHasErrors('start_time');
    });
});
