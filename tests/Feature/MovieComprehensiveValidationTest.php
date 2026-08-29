<?php

use App\Models\Booking;
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

describe('Comprehensive Movie Status and Data Integrity Validation', function () {

    if (!function_exists('getComprehensiveAdminUser')) {
        function getComprehensiveAdminUser(): User
        {
            $role = Role::firstOrCreate(['role_name' => 'ADMIN'], ['description' => 'Administrator']);
            return User::factory()->create([
                'role_id' => $role->id,
                'status' => 'ACTIVE',
            ]);
        }
    }

    if (!function_exists('createComprehensiveCinemaAndRoom')) {
        function createComprehensiveCinemaAndRoom(): Room
        {
            $cinema = Cinema::create([
                'name' => 'Cinema ' . uniqid(),
                'address' => '123 Test St',
                'city' => 'Hanoi',
                'status' => 'ACTIVE',
            ]);

            return Room::create([
                'cinema_id' => $cinema->id,
                'name' => 'Screen 1',
                'format' => '2D',
                'total_seats' => 60,
                'status' => 'ACTIVE',
            ]);
        }
    }

    // ── 1. State Transition Constraint ──
    test('disallows changing status back from ENDED to any active status', function () {
        $admin = getComprehensiveAdminUser();

        $movie = Movie::create([
            'title' => 'Ended Movie Test',
            'duration' => 120,
            'status' => Movie::STATUS_ENDED,
            'release_date' => now()->subMonths(2),
        ]);

        // Thử chuyển từ ENDED sang NOW_SHOWING
        $response1 = $this->actingAs($admin)->put(route('admin.movies.update', $movie), [
            'title' => $movie->title,
            'duration' => $movie->duration,
            'status' => Movie::STATUS_NOW_SHOWING,
        ]);
        $response1->assertSessionHasErrors('status');
        expect(session('errors')->first('status'))->toBe('Không thể thay đổi trạng thái của phim đã ngừng chiếu (ENDED).');

        // Thử chuyển từ ENDED sang PRE_ORDER
        $response2 = $this->actingAs($admin)->put(route('admin.movies.update', $movie), [
            'title' => $movie->title,
            'duration' => $movie->duration,
            'status' => Movie::STATUS_PRE_ORDER,
            'release_date' => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);
        $response2->assertSessionHasErrors('status');

        // Thử chuyển từ ENDED sang SCHEDULED
        $response3 = $this->actingAs($admin)->put(route('admin.movies.update', $movie), [
            'title' => $movie->title,
            'duration' => $movie->duration,
            'status' => Movie::STATUS_SCHEDULED,
            'release_date' => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);
        $response3->assertSessionHasErrors('status');

        $movie->refresh();
        expect($movie->status)->toBe(Movie::STATUS_ENDED);
    });

    // ── 2. Cascade Showtime Sync ──
    test('auto-publishes associated PENDING showtimes when movie switches to PRE_ORDER or NOW_SHOWING', function () {
        $admin = getComprehensiveAdminUser();
        $room = createComprehensiveCinemaAndRoom();

        $movie = Movie::create([
            'title' => 'Scheduled Movie To Be Published',
            'duration' => 110,
            'status' => Movie::STATUS_COMING_SOON,
            'release_date' => now()->addDays(5),
        ]);

        // Tạo 2 suất chiếu PENDING trong tương lai
        $showtime1 = Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->addDays(6),
            'end_time' => now()->addDays(6)->addHours(2),
            'status' => Showtime::STATUS_PENDING,
        ]);

        $showtime2 = Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(7)->addHours(2),
            'status' => Showtime::STATUS_UNPUBLISHED,
        ]);

        // Cập nhật phim sang PRE_ORDER
        $response = $this->actingAs($admin)->put(route('admin.movies.update', $movie), [
            'title' => $movie->title,
            'duration' => $movie->duration,
            'status' => Movie::STATUS_PRE_ORDER,
            'release_date' => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasNoErrors();
        $movie->refresh();
        expect($movie->status)->toBe(Movie::STATUS_PRE_ORDER);

        // Suất chiếu PENDING và UNPUBLISHED phải được tự động chuyển sang SCHEDULED
        $showtime1->refresh();
        $showtime2->refresh();
        expect($showtime1->status)->toBe(Showtime::STATUS_SCHEDULED)
            ->and($showtime2->status)->toBe(Showtime::STATUS_SCHEDULED);
    });

    // ── 3. Field Immutability Rule ──
    test('blocks editing duration or age_rating if movie has successful bookings', function () {
        $admin = getComprehensiveAdminUser();
        $room = createComprehensiveCinemaAndRoom();

        $movie = Movie::create([
            'title' => 'Immutable Fields Movie',
            'duration' => 120,
            'age_rating' => 'T16',
            'status' => Movie::STATUS_NOW_SHOWING,
            'release_date' => now()->subDays(5),
        ]);

        $showtime = Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHours(2),
            'status' => Showtime::STATUS_SCHEDULED,
        ]);

        // Tạo booking Paid
        Booking::create([
            'user_id' => $admin->id,
            'showtime_id' => $showtime->id,
            'total_price' => 100000,
            'status' => 'Paid',
            'booking_code' => 'BK-' . uniqid(),
            'booking_time' => now(),
        ]);

        // 1. Thử đổi duration -> Bị chặn
        $response1 = $this->actingAs($admin)->put(route('admin.movies.update', $movie), [
            'title' => $movie->title,
            'duration' => 150, // Changed from 120
            'age_rating' => 'T16',
            'status' => Movie::STATUS_NOW_SHOWING,
        ]);
        $response1->assertSessionHasErrors('duration');
        expect(session('errors')->first('duration'))->toBe('Không thể thay đổi thời lượng hoặc độ tuổi của phim đã có giao dịch đặt vé.');

        // 2. Thử đổi age_rating -> Bị chặn
        $response2 = $this->actingAs($admin)->put(route('admin.movies.update', $movie), [
            'title' => $movie->title,
            'duration' => 120,
            'age_rating' => 'T18', // Changed from T16
            'status' => Movie::STATUS_NOW_SHOWING,
        ]);
        $response2->assertSessionHasErrors('age_rating');
        expect(session('errors')->first('age_rating'))->toBe('Không thể thay đổi thời lượng hoặc độ tuổi của phim đã có giao dịch đặt vé.');

        // 3. Giữ nguyên duration và age_rating nhưng đổi title/description -> Cho phép
        $response3 = $this->actingAs($admin)->put(route('admin.movies.update', $movie), [
            'title' => 'Updated Title For Movie With Bookings',
            'description' => 'Updated description is fine',
            'duration' => 120,
            'age_rating' => 'T16',
            'status' => Movie::STATUS_NOW_SHOWING,
        ]);
        $response3->assertSessionHasNoErrors();
        $movie->refresh();
        expect($movie->title)->toBe('Updated Title For Movie With Bookings')
            ->and($movie->duration)->toBe(120)
            ->and($movie->age_rating)->toBe('T16');
    });

    test('allows editing duration and age_rating if movie has NO bookings', function () {
        $admin = getComprehensiveAdminUser();

        $movie = Movie::create([
            'title' => 'New Movie Without Bookings',
            'duration' => 100,
            'age_rating' => 'P',
            'status' => Movie::STATUS_COMING_SOON,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.movies.update', $movie), [
            'title' => 'New Movie Updated',
            'duration' => 135,
            'age_rating' => 'T13',
            'status' => Movie::STATUS_COMING_SOON,
        ]);

        $response->assertSessionHasNoErrors();
        $movie->refresh();
        expect($movie->duration)->toBe(135)
            ->and($movie->age_rating)->toBe('T13');
    });

    // ── 4. Deletion Protection ──
    test('blocks permanent forceDelete if movie has historical bookings or showtimes', function () {
        $admin = getComprehensiveAdminUser();
        $room = createComprehensiveCinemaAndRoom();

        $movie = Movie::create([
            'title' => 'Movie To Soft Delete and Protect Force Delete',
            'duration' => 120,
            'status' => Movie::STATUS_ENDED,
            'release_date' => now()->subMonths(2),
        ]);

        $showtime = Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->subDays(10),
            'end_time' => now()->subDays(10)->addHours(2),
            'status' => Showtime::STATUS_COMPLETED,
        ]);

        Booking::create([
            'user_id' => $admin->id,
            'showtime_id' => $showtime->id,
            'total_price' => 100000,
            'status' => 'Paid',
            'booking_code' => 'BK-' . uniqid(),
            'booking_time' => now()->subDays(10),
        ]);

        // Soft delete phim (cho phép vì không có active showtimes)
        $destroyResponse = $this->actingAs($admin)->delete(route('admin.movies.destroy', $movie));
        $destroyResponse->assertRedirect(route('admin.movies.index'));
        $destroyResponse->assertSessionHas('success');

        expect(Movie::onlyTrashed()->where('id', $movie->id)->exists())->toBeTrue();

        // Thử forceDelete phim đã có lịch sử vé/suất chiếu -> Bị chặn
        $forceResponse = $this->actingAs($admin)->delete(route('admin.movies.forceDelete', $movie->id));
        $forceResponse->assertRedirect(route('admin.movies.trashed'));
        $forceResponse->assertSessionHas('error');
        expect(session('error'))->toContain('Không thể xóa vĩnh viễn phim này vì đã có lịch sử đặt vé hoặc suất chiếu liên quan. Chỉ được phép lưu trữ (Xóa mềm).');

        // Phim vẫn còn trong thùng rác an toàn
        expect(Movie::onlyTrashed()->where('id', $movie->id)->exists())->toBeTrue();
    });
});
