<?php

use App\Models\Booking;
use App\Models\Category;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Role;
use App\Models\Room;
use App\Models\Showtime;
use App\Models\User;
use App\Services\MovieStatusValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

describe('Movie ENDED Status Transition and Showtime Closure Validation', function () {

    function getAdminUser(): User
    {
        $role = Role::firstOrCreate(['role_name' => 'ADMIN'], ['description' => 'Administrator']);
        return User::factory()->create([
            'role_id' => $role->id,
            'status' => 'ACTIVE',
        ]);
    }

    function createCinemaAndRoom(): Room
    {
        $cinema = Cinema::create([
            'name' => 'Test Cinema ' . uniqid(),
            'address' => '123 Test Street',
            'city' => 'Hanoi',
            'status' => 'ACTIVE',
        ]);

        return Room::create([
            'cinema_id' => $cinema->id,
            'name' => 'Screen 1',
            'format' => '2D',
            'total_seats' => 50,
            'status' => 'ACTIVE',
        ]);
    }

    test('blocks transition to ENDED if active bookings (SUCCESS or Paid) exist for future showtimes', function () {
        $admin = getAdminUser();
        $room = createCinemaAndRoom();

        $movie = Movie::create([
            'title' => 'Test Movie With Active Booking',
            'duration' => 120,
            'status' => Movie::STATUS_NOW_SHOWING,
            'release_date' => now()->subDays(5),
        ]);

        $futureShowtime = Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->addDays(2),
            'end_time' => now()->addDays(2)->addHours(2),
            'status' => Showtime::STATUS_SCHEDULED,
        ]);

        // Tạo active booking cho suất chiếu tương lai
        Booking::create([
            'user_id' => $admin->id,
            'showtime_id' => $futureShowtime->id,
            'total_price' => 100000,
            'status' => 'SUCCESS',
            'booking_code' => 'BK-' . uniqid(),
            'booking_time' => now(),
        ]);

        // Thử cập nhật trạng thái phim sang ENDED
        $response = $this->actingAs($admin)->put(route('admin.movies.update', $movie), [
            'title' => $movie->title,
            'duration' => $movie->duration,
            'status' => Movie::STATUS_ENDED,
        ]);

        $response->assertSessionHasErrors('status');
        $errorMessage = session('errors')->first('status');
        expect($errorMessage)->toBe("Không thể chuyển phim sang 'Ngừng chiếu' vì đang có suất chiếu tương lai đã được đặt vé. Vui lòng hủy các suất chiếu và hoàn tiền cho khách trước.");

        // Đảm bảo trạng thái phim chưa bị đổi
        $movie->refresh();
        expect($movie->status)->toBe(Movie::STATUS_NOW_SHOWING);
    });

    test('blocks transition to ENDED with Paid status bookings as well', function () {
        $admin = getAdminUser();
        $room = createCinemaAndRoom();

        $movie = Movie::create([
            'title' => 'Test Movie With Paid Booking',
            'duration' => 120,
            'status' => Movie::STATUS_NOW_SHOWING,
            'release_date' => now()->subDays(5),
        ]);

        $futureShowtime = Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHours(2),
            'status' => Showtime::STATUS_SCHEDULED,
        ]);

        Booking::create([
            'user_id' => $admin->id,
            'showtime_id' => $futureShowtime->id,
            'total_price' => 90000,
            'status' => 'Paid',
            'booking_code' => 'BK-' . uniqid(),
            'booking_time' => now(),
        ]);

        $response = $this->actingAs($admin)->put(route('admin.movies.update', $movie), [
            'title' => $movie->title,
            'duration' => $movie->duration,
            'status' => Movie::STATUS_ENDED,
        ]);

        $response->assertSessionHasErrors('status');
        $movie->refresh();
        expect($movie->status)->toBe(Movie::STATUS_NOW_SHOWING);
    });

    test('allows transition to ENDED if future showtime only has Cancelled bookings', function () {
        $admin = getAdminUser();
        $room = createCinemaAndRoom();

        $movie = Movie::create([
            'title' => 'Movie With Cancelled Bookings Only',
            'duration' => 120,
            'status' => Movie::STATUS_NOW_SHOWING,
            'release_date' => now()->subDays(10),
        ]);

        $futureShowtime = Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->addDays(2),
            'end_time' => now()->addDays(2)->addHours(2),
            'status' => Showtime::STATUS_SCHEDULED,
        ]);

        Booking::create([
            'user_id' => $admin->id,
            'showtime_id' => $futureShowtime->id,
            'total_price' => 100000,
            'status' => 'Cancelled',
            'booking_code' => 'BK-' . uniqid(),
            'booking_time' => now(),
        ]);

        $response = $this->actingAs($admin)->put(route('admin.movies.update', $movie), [
            'title' => $movie->title,
            'duration' => $movie->duration,
            'status' => Movie::STATUS_ENDED,
        ]);

        $response->assertSessionHasNoErrors();
        $movie->refresh();
        expect($movie->status)->toBe(Movie::STATUS_ENDED);

        // Suất chiếu tương lai tự động chuyển sang CANCELLED
        $futureShowtime->refresh();
        expect($futureShowtime->status)->toBe(Showtime::STATUS_CANCELLED);
    });

    test('allows transition to ENDED if active bookings are only in PAST showtimes', function () {
        $admin = getAdminUser();
        $room = createCinemaAndRoom();

        $movie = Movie::create([
            'title' => 'Movie With Past Bookings Only',
            'duration' => 120,
            'status' => Movie::STATUS_NOW_SHOWING,
            'release_date' => now()->subDays(30),
        ]);

        // Suất chiếu quá khứ đã có khách đặt
        $pastShowtime = Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->subDays(2),
            'end_time' => now()->subDays(2)->addHours(2),
            'status' => Showtime::STATUS_COMPLETED,
        ]);

        Booking::create([
            'user_id' => $admin->id,
            'showtime_id' => $pastShowtime->id,
            'total_price' => 120000,
            'status' => 'SUCCESS',
            'booking_code' => 'BK-' . uniqid(),
            'booking_time' => now()->subDays(3),
        ]);

        // Suất chiếu tương lai không có khách đặt
        $futureShowtime = Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->addDays(3),
            'end_time' => now()->addDays(3)->addHours(2),
            'status' => Showtime::STATUS_SCHEDULED,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.movies.update', $movie), [
            'title' => $movie->title,
            'duration' => $movie->duration,
            'status' => Movie::STATUS_ENDED,
        ]);

        $response->assertSessionHasNoErrors();
        $movie->refresh();
        expect($movie->status)->toBe(Movie::STATUS_ENDED);

        $futureShowtime->refresh();
        expect($futureShowtime->status)->toBe(Showtime::STATUS_CANCELLED);

        // Suất chiếu quá khứ vẫn giữ nguyên COMPLETED
        $pastShowtime->refresh();
        expect($pastShowtime->status)->toBe(Showtime::STATUS_COMPLETED);
    });

    test('cascade cancellation updates all upcoming showtimes for the ended movie and leaves other movies unaffected', function () {
        $admin = getAdminUser();
        $room = createCinemaAndRoom();

        $movieA = Movie::create([
            'title' => 'Movie To Be Ended',
            'duration' => 100,
            'status' => Movie::STATUS_NOW_SHOWING,
            'release_date' => now()->subDays(10),
        ]);

        $movieB = Movie::create([
            'title' => 'Other Movie Still Showing',
            'duration' => 110,
            'status' => Movie::STATUS_NOW_SHOWING,
            'release_date' => now()->subDays(5),
        ]);

        // Movie A showtimes
        $showtimeA1 = Showtime::create([
            'movie_id' => $movieA->id,
            'room_id' => $room->id,
            'start_time' => now()->addHours(5),
            'end_time' => now()->addHours(7),
            'status' => Showtime::STATUS_SCHEDULED,
        ]);
        $showtimeA2 = Showtime::create([
            'movie_id' => $movieA->id,
            'room_id' => $room->id,
            'start_time' => now()->addDays(2),
            'end_time' => now()->addDays(2)->addHours(2),
            'status' => Showtime::STATUS_PENDING,
        ]);

        // Movie B showtime
        $showtimeB = Showtime::create([
            'movie_id' => $movieB->id,
            'room_id' => $room->id,
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHours(2),
            'status' => Showtime::STATUS_SCHEDULED,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.movies.update', $movieA), [
            'title' => $movieA->title,
            'duration' => $movieA->duration,
            'status' => Movie::STATUS_ENDED,
        ]);

        $response->assertSessionHasNoErrors();
        $movieA->refresh();
        expect($movieA->status)->toBe(Movie::STATUS_ENDED);

        $showtimeA1->refresh();
        $showtimeA2->refresh();
        $showtimeB->refresh();

        expect($showtimeA1->status)->toBe(Showtime::STATUS_CANCELLED)
            ->and($showtimeA2->status)->toBe(Showtime::STATUS_CANCELLED)
            ->and($showtimeB->status)->toBe(Showtime::STATUS_SCHEDULED);
    });

    test('direct service and model validation methods work as expected', function () {
        $service = new MovieStatusValidationService();
        $room = createCinemaAndRoom();

        $movie = Movie::create([
            'title' => 'Service Unit Test Movie',
            'duration' => 120,
            'status' => Movie::STATUS_NOW_SHOWING,
        ]);

        expect($service->hasActiveFutureBookings($movie))->toBeFalse()
            ->and($movie->hasActiveFutureBookings())->toBeFalse();

        // No exception thrown when no bookings exist
        $service->validateCanTransitionToEnded($movie);

        $futureShowtime = Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHours(2),
            'status' => Showtime::STATUS_SCHEDULED,
        ]);

        Booking::create([
            'showtime_id' => $futureShowtime->id,
            'total_price' => 80000,
            'status' => 'SUCCESS',
            'booking_code' => 'BK-' . uniqid(),
            'booking_time' => now(),
        ]);

        expect($service->hasActiveFutureBookings($movie))->toBeTrue()
            ->and($movie->hasActiveFutureBookings())->toBeTrue();

        expect(fn () => $service->validateCanTransitionToEnded($movie))
            ->toThrow(ValidationException::class);
    });
});
