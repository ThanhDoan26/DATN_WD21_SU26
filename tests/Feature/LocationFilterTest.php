<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_can_set_location_in_session(): void
    {
        $response = $this->postJson('/api/set-location', [
            'city' => 'Hà Nội',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'location' => 'Hà Nội',
                'label' => 'Hà Nội',
            ]);

        $this->assertEquals('Hà Nội', session('user_location'));
    }

    public function test_api_can_reset_location_to_all(): void
    {
        session(['user_location' => 'Hà Nội']);

        $response = $this->postJson('/api/set-location', [
            'city' => 'ALL',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'location' => 'ALL',
                'label' => 'Toàn quốc',
            ]);

        $this->assertNull(session('user_location'));
    }

    public function test_switch_location_route_redirects_and_updates_session(): void
    {
        $response = $this->get('/location/set/' . urlencode('TP. Hồ Chí Minh'));

        $response->assertRedirect();
        $this->assertEquals('TP. Hồ Chí Minh', session('user_location'));

        $resetResponse = $this->get('/location/set/ALL');
        $resetResponse->assertRedirect();
        $this->assertNull(session('user_location'));
    }

    public function test_api_locations_returns_active_cities_and_provinces(): void
    {
        Cinema::create([
            'name' => 'Cinema HN 1',
            'address' => '123 Pho Hue',
            'city' => 'Hà Nội',
            'status' => 'ACTIVE',
        ]);

        Cinema::create([
            'name' => 'Cinema HN 2',
            'address' => '456 Cau Giay',
            'city' => 'Hà Nội',
            'status' => 'ACTIVE',
        ]);

        Cinema::create([
            'name' => 'Cinema HCM 1',
            'address' => '789 Nguyen Hue',
            'city' => 'Hồ Chí Minh',
            'status' => 'ACTIVE',
        ]);

        $response = $this->getJson('/api/locations');

        $response->assertOk()
            ->assertJsonStructure([
                'current',
                'cinema_cities',
                'all_provinces',
            ]);

        $data = $response->json();
        $this->assertCount(2, $data['cinema_cities']);
    }

    public function test_homepage_and_movie_listings_filter_by_selected_location(): void
    {
        $hanoiCinema = Cinema::create([
            'name' => 'Cinema Ha Noi',
            'address' => '123 Ba Trieu',
            'city' => 'Hà Nội',
            'status' => 'ACTIVE',
        ]);

        $hcmCinema = Cinema::create([
            'name' => 'Cinema HCM',
            'address' => '456 Le Loi',
            'city' => 'Hồ Chí Minh',
            'status' => 'ACTIVE',
        ]);

        $hnRoom = Room::create([
            'cinema_id' => $hanoiCinema->id,
            'name' => 'Room HN 1',
            'type' => 'STANDARD',
            'capacity' => 50,
            'total_rows' => 5,
            'total_columns' => 10,
            'status' => 'ACTIVE',
        ]);

        $hcmRoom = Room::create([
            'cinema_id' => $hcmCinema->id,
            'name' => 'Room HCM 1',
            'type' => 'STANDARD',
            'capacity' => 50,
            'total_rows' => 5,
            'total_columns' => 10,
            'status' => 'ACTIVE',
        ]);

        $movieHN = Movie::create([
            'title' => 'Phim Chiếu Hà Nội',
            'status' => 'NOW_SHOWING',
            'duration' => 120,
        ]);

        $movieHCM = Movie::create([
            'title' => 'Phim Chiếu TP HCM',
            'status' => 'NOW_SHOWING',
            'duration' => 110,
        ]);

        Showtime::create([
            'movie_id' => $movieHN->id,
            'room_id' => $hnRoom->id,
            'start_time' => now()->addHours(2),
            'end_time' => now()->addHours(4),
            'price' => 100000,
            'status' => Showtime::STATUS_SCHEDULED,
        ]);

        Showtime::create([
            'movie_id' => $movieHCM->id,
            'room_id' => $hcmRoom->id,
            'start_time' => now()->addHours(2),
            'end_time' => now()->addHours(4),
            'price' => 100000,
            'status' => Showtime::STATUS_SCHEDULED,
        ]);

        // When viewing with session location 'Hà Nội'
        $response = $this->withSession(['user_location' => 'Hà Nội'])->get('/');
        $response->assertOk();
        $response->assertSee('Hà Nội');
        $response->assertSee('Phim Chiếu Hà Nội');

        // When viewing current movies page with session location 'Hà Nội'
        $currentResponse = $this->withSession(['user_location' => 'Hà Nội'])->get('/phim-dang-chieu');
        $currentResponse->assertOk();
        $currentResponse->assertSee('Phim Chiếu Hà Nội');
        $currentResponse->assertDontSee('Phim Chiếu TP HCM');
    }

    public function test_select_cinema_page_only_shows_cinemas_of_selected_location(): void
    {
        $hanoiCinema = Cinema::create([
            'name' => 'CGV Vincom Hà Nội',
            'address' => '191 Ba Trieu',
            'city' => 'Hà Nội',
            'status' => 'ACTIVE',
        ]);

        $hcmCinema = Cinema::create([
            'name' => 'CGV Sư Vạn Hạnh HCM',
            'address' => '11 Sư Vạn Hạnh',
            'city' => 'Hồ Chí Minh',
            'status' => 'ACTIVE',
        ]);

        $hnRoom = Room::create([
            'cinema_id' => $hanoiCinema->id,
            'name' => 'Room HN 1',
            'type' => 'STANDARD',
            'capacity' => 50,
            'total_rows' => 5,
            'total_columns' => 10,
            'status' => 'ACTIVE',
        ]);

        $hcmRoom = Room::create([
            'cinema_id' => $hcmCinema->id,
            'name' => 'Room HCM 1',
            'type' => 'STANDARD',
            'capacity' => 50,
            'total_rows' => 5,
            'total_columns' => 10,
            'status' => 'ACTIVE',
        ]);

        $movie = Movie::create([
            'title' => 'Dune 2',
            'status' => 'NOW_SHOWING',
            'duration' => 160,
        ]);

        Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $hnRoom->id,
            'start_time' => now()->addHours(2),
            'end_time' => now()->addHours(5),
            'price' => 120000,
            'status' => Showtime::STATUS_SCHEDULED,
        ]);

        Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $hcmRoom->id,
            'start_time' => now()->addHours(2),
            'end_time' => now()->addHours(5),
            'price' => 120000,
            'status' => Showtime::STATUS_SCHEDULED,
        ]);

        // When user has chosen 'Hồ Chí Minh'
        $response = $this->withSession(['user_location' => 'Hồ Chí Minh'])->get("/booking/movie/{$movie->id}/cinema");

        $response->assertOk();
        $response->assertSee('CGV Sư Vạn Hạnh HCM');
        $response->assertDontSee('CGV Vincom Hà Nội');
        $response->assertSee('Hồ Chí Minh (1)');
    }

    public function test_select_cinema_page_renders_cleanly_when_no_cinemas_in_selected_location(): void
    {
        $hanoiCinema = Cinema::create([
            'name' => 'CGV Vincom Hà Nội',
            'address' => '191 Ba Trieu',
            'city' => 'Hà Nội',
            'status' => 'ACTIVE',
        ]);

        $hnRoom = Room::create([
            'cinema_id' => $hanoiCinema->id,
            'name' => 'Room HN 1',
            'type' => 'STANDARD',
            'capacity' => 50,
            'total_rows' => 5,
            'total_columns' => 10,
            'status' => 'ACTIVE',
        ]);

        $movie = Movie::create([
            'title' => 'Deadpool & Wolverine',
            'status' => 'NOW_SHOWING',
            'duration' => 128,
        ]);

        // Only showtime in Hanoi
        Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $hnRoom->id,
            'start_time' => now()->addHours(2),
            'end_time' => now()->addHours(5),
            'price' => 120000,
            'status' => Showtime::STATUS_SCHEDULED,
        ]);

        // User is browsing from 'Đà Nẵng' where no showtimes exist
        $response = $this->withSession(['user_location' => 'Đà Nẵng'])->get("/booking/movie/{$movie->id}/cinema");

        $response->assertOk();
        $response->assertSee('Chưa có rạp chiếu tại Đà Nẵng');
        $response->assertSee('Xem rạp Toàn quốc');
    }
}
