<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Movie;
use App\Models\Showtime;
use App\Models\Booking;
use App\Models\User;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        // Kiểm tra xem có cinema và room không, nếu không thì tạo
        $cinema = DB::table('cinemas')->first();
        if (!$cinema) {
            $cinema_id = DB::table('cinemas')->insertGetId([
                'name' => 'CGV Sư Vạn Hạnh',
                'address' => '123 Sư Vạn Hạnh, Quận 10, TP.HCM',
                'city' => 'Hồ Chí Minh',
                'phone' => '0283838383',
                'email' => 'cgv.vanhang@cgv.vn',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "✓ Created cinema: CGV Sư Vạn Hạnh\n";
        } else {
            $cinema_id = $cinema->id;
        }

        // Tạo room nếu chưa có
        $room = DB::table('rooms')->where('cinema_id', $cinema_id)->first();
        if (!$room) {
            $room_id = DB::table('rooms')->insertGetId([
                'cinema_id' => $cinema_id,
                'name' => 'Cinema 1',
                'format' => '2D',
                'total_seats' => 60,
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "✓ Created room: Cinema 1\n";
        } else {
            $room_id = $room->id;
        }

        // Tạo phim mẫu
        $movies = [
            [
                'title' => 'Avatar: The Way of Water',
                'description' => 'Tiếp nối câu chuyện tình yêu trên hành tinh Pandora',
                'director' => 'James Cameron',
                'cast' => 'Sam Worthington, Zoe Saldana',
                'duration' => 192,
                'age_rating' => 'P',
                'status' => 'NOW_SHOWING',
            ],
            [
                'title' => 'Twisters',
                'description' => 'Câu chuyện hành động về cuộc săn lùng lốc xoáy',
                'director' => 'Lee Isaac Chung',
                'cast' => 'Daisy Edgar-Jones, Glen Powell',
                'duration' => 123,
                'age_rating' => 'P',
                'status' => 'NOW_SHOWING',
            ],
        ];

        $movieIds = [];
        foreach ($movies as $movie) {
            $exists = DB::table('movies')->where('title', $movie['title'])->first();
            if (!$exists) {
                $id = DB::table('movies')->insertGetId([
                    ...$movie,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $movieIds[] = $id;
            } else {
                $movieIds[] = $exists->id;
            }
        }
        echo "✓ Created " . count($movieIds) . " movies\n";

        // Tạo showtimes
        $showtimes = [];

        // Avatar - 2 suất
        for ($i = 0; $i < 2; $i++) {
            $st = DB::table('showtimes')->insertGetId([
                'movie_id' => $movieIds[0],
                'room_id' => $room_id,
                'start_time' => now()->addDays($i)->setHour(14 + ($i * 2)),
                'end_time' => now()->addDays($i)->setHour(14 + ($i * 2))->addMinutes(192),
                'status' => 'SCHEDULED',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $showtimes[] = $st;
        }

        // Twisters - 2 suất
        for ($i = 0; $i < 2; $i++) {
            $st = DB::table('showtimes')->insertGetId([
                'movie_id' => $movieIds[1],
                'room_id' => $room_id,
                'start_time' => now()->addDays($i + 1)->setHour(16 + ($i * 2)),
                'end_time' => now()->addDays($i + 1)->setHour(16 + ($i * 2))->addMinutes(123),
                'status' => 'SCHEDULED',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $showtimes[] = $st;
        }

        echo "✓ Created " . count($showtimes) . " showtimes\n";

        // Tạo bookings mẫu
        $users = DB::table('users')->where('status', 'ACTIVE')->limit(3)->get();

        $bookingData = [
            [
                'user_id' => $users[0]->id ?? null,
                'showtime_id' => $showtimes[0],
                'total_price' => 225000,
                'status' => 'Paid',
                'payment_method' => 'VNPay',
                'booking_code' => 'BK' . strtoupper(substr(uniqid(), -6)),
            ],
            [
                'user_id' => $users[1]->id ?? null,
                'showtime_id' => $showtimes[1],
                'total_price' => 150000,
                'status' => 'Pending',
                'payment_method' => 'Momo',
                'booking_code' => 'BK' . strtoupper(substr(uniqid(), -6)),
            ],
            [
                'user_id' => null,
                'showtime_id' => $showtimes[2],
                'total_price' => 300000,
                'status' => 'Paid',
                'payment_method' => 'Tiền mặt',
                'booking_code' => 'BK' . strtoupper(substr(uniqid(), -6)),
            ],
            [
                'user_id' => $users[2]->id ?? null,
                'showtime_id' => $showtimes[3],
                'total_price' => 200000,
                'status' => 'Used',
                'payment_method' => 'VNPay',
                'booking_code' => 'BK' . strtoupper(substr(uniqid(), -6)),
            ],
        ];

        foreach ($bookingData as $data) {
            $data['booking_time'] = now()->subDays(rand(1, 5));
            if ($data['status'] === 'Paid' || $data['status'] === 'Used') {
                $data['payment_time'] = $data['booking_time']->addMinutes(rand(10, 60));
            }

            DB::table('bookings')->insert([
                ...$data,
                'created_at' => $data['booking_time'],
                'updated_at' => now(),
            ]);
        }

        echo "✓ Created " . count($bookingData) . " bookings\n";
        echo "\n✨ Seeding completed successfully!\n";
    }
}
