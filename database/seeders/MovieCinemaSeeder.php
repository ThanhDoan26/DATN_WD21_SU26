<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * ========================================
 * MovieCinemaSeeder
 * ========================================
 * Seeder tạo dữ liệu thử nghiệm đầy đủ:
 * - 1 rạp (CGV Sư Vạn Hạnh)
 * - 2 phòng chiếu (Cinema 1, IMAX 2)
 * - 100+ ghế (multiple types: Regular, VIP, Sweetbox)
 * - 3 phim (Avatar, Twisters, Inside Out 3)
 * - 6 suất chiếu (2 suất/phim)
 * - Giá vé linh hoạt
 * - 3 tài khoản admin, manager, staff
 * - 5 tài khoản khách hàng
 * - 2 booking mẫu
 *
 * Chạy: php artisan db:seed --class=MovieCinemaSeeder
 * Hoặc: php artisan migrate:fresh --seed
 */
class MovieCinemaSeeder extends Seeder
{
    public function run(): void
    {
        // ==================================================
        // Step 1: Tạo Rạp
        // ==================================================
        // $cinema = DB::table('cinemas')->insertGetId([
        //     'name' => 'CGV Sư Vạn Hạnh',
        //     'address' => '123 Sư Vạn Hạnh, Quận 10, TP.HCM',
        //     'city' => 'Hồ Chí Minh',
        //     'phone' => '0283838383',
        //     'email' => 'cgv.vanhang@cgv.vn',
        //     'status' => 'ACTIVE',
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // echo "✓ Created cinema: CGV Sư Vạn Hạnh (ID: $cinema)\n";

        // ==================================================
        // Step 2: Tạo Phòng Chiếu
        // ==================================================
    //     $room1 = DB::table('rooms')->insertGetId([
    //         'cinema_id' => $cinema,
    //         'name' => 'Cinema 1',
    //         'format' => '2D',
    //         'total_seats' => 60,
    //         'status' => 'ACTIVE',
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ]);

    //     $room2 = DB::table('rooms')->insertGetId([
    //         'cinema_id' => $cinema,
    //         'name' => 'IMAX 2',
    //         'format' => 'IMAX',
    //         'total_seats' => 80,
    //         'status' => 'ACTIVE',
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ]);

    //     echo "✓ Created rooms: Cinema 1 (ID: $room1), IMAX 2 (ID: $room2)\n";

    //     // ==================================================
    //     // Step 3: Tạo Ghế cho Phòng 1 (6 hàng x 10 ghế)
    //     // ==================================================
    //     $this->createSeats($room1, [
    //         ['A', 10, 'Regular'],
    //         ['B', 10, 'Regular'],
    //         ['C', 10, 'Regular'],
    //         ['D', 10, 'VIP'],
    //         ['E', 10, 'VIP'],
    //         ['F', 10, 'Sweetbox'],
    //     ]);

    //     echo "✓ Created seats for Cinema 1 (60 seats)\n";

    //     // ==================================================
    //     // Step 4: Tạo Ghế cho Phòng 2 (8 hàng x 10 ghế)
    //     // ==================================================
    //     $this->createSeats($room2, [
    //         ['A', 10, 'Regular'],
    //         ['B', 10, 'Regular'],
    //         ['C', 10, 'Regular'],
    //         ['D', 10, 'Regular'],
    //         ['E', 10, 'VIP'],
    //         ['F', 10, 'VIP'],
    //         ['G', 10, 'Sweetbox'],
    //         ['H', 10, 'Sweetbox'],
    //     ]);

    //     echo "✓ Created seats for IMAX 2 (80 seats)\n";

    //     // ==================================================
    //     // Step 5: Tạo Phim
    //     // ==================================================
    //     $movies = [
    //         [
    //             'title' => 'Avatar: The Way of Water',
    //             'description' => 'Tiếp nối câu chuyện tình yêu trên hành tinh Pandora với những cảnh quay dưới nước đặc sắc',
    //             'director' => 'James Cameron',
    //             'cast' => 'Sam Worthington, Zoe Saldana, Kate Winslet',
    //             'duration' => 192,
    //             'age_rating' => 'P',
    //             'status' => 'NOW_SHOWING',
    //         ],
    //         [
    //             'title' => 'Twisters',
    //             'description' => 'Câu chuyện hành động về cuộc săn lùng lốc xoáy tại Oklahoma',
    //             'director' => 'Lee Isaac Chung',
    //             'cast' => 'Daisy Edgar-Jones, Glen Powell',
    //             'duration' => 123,
    //             'age_rating' => 'P',
    //             'status' => 'NOW_SHOWING',
    //         ],
    //         [
    //             'title' => 'Inside Out 2',
    //             'description' => 'Những cảm xúc bên trong con gái lớn thêm một tuổi',
    //             'director' => 'Kelsey Mann',
    //             'cast' => 'Amy Poehler, Phyllis Smith',
    //             'duration' => 96,
    //             'age_rating' => 'K',
    //             'status' => 'COMING_SOON',
    //         ],
    //     ];

    //     $movieIds = [];
    //     foreach ($movies as $movie) {
    //         $id = DB::table('movies')->insertGetId([
    //             ...$movie,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);
    //         $movieIds[] = $id;
    //     }

    //     echo "✓ Created 3 movies: Avatar, Twisters, Inside Out 2\n";

    //     // ==================================================
    //     // Step 6: Tạo Suất Chiếu
    //     // ==================================================
    //     $showtimes = [];

    //     // Avatar - Cinema 1
    //     $st1 = DB::table('showtimes')->insertGetId([
    //         'movie_id' => $movieIds[0],
    //         'room_id' => $room1,
    //         'start_time' => now()->addHours(2)->startOfHour()->addMinutes(0),
    //         'end_time' => now()->addHours(2)->startOfHour()->addMinutes(192),
    //         'status' => 'SCHEDULED',
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ]);
    //     $showtimes[] = $st1;

    //     // Avatar - IMAX 2
    //     $st2 = DB::table('showtimes')->insertGetId([
    //         'movie_id' => $movieIds[0],
    //         'room_id' => $room2,
    //         'start_time' => now()->addHours(5)->startOfHour()->addMinutes(0),
    //         'end_time' => now()->addHours(5)->startOfHour()->addMinutes(192),
    //         'status' => 'SCHEDULED',
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ]);
    //     $showtimes[] = $st2;

    //     // Twisters - Cinema 1
    //     $st3 = DB::table('showtimes')->insertGetId([
    //         'movie_id' => $movieIds[1],
    //         'room_id' => $room1,
    //         'start_time' => now()->addHours(8)->startOfHour()->addMinutes(0),
    //         'end_time' => now()->addHours(8)->startOfHour()->addMinutes(123),
    //         'status' => 'SCHEDULED',
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ]);
    //     $showtimes[] = $st3;

    //     // Twisters - IMAX 2
    //     $st4 = DB::table('showtimes')->insertGetId([
    //         'movie_id' => $movieIds[1],
    //         'room_id' => $room2,
    //         'start_time' => now()->addHours(10)->startOfHour()->addMinutes(0),
    //         'end_time' => now()->addHours(10)->startOfHour()->addMinutes(123),
    //         'status' => 'SCHEDULED',
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ]);
    //     $showtimes[] = $st4;

    //     // Inside Out 2 - Cinema 1 (COMING_SOON)
    //     $st5 = DB::table('showtimes')->insertGetId([
    //         'movie_id' => $movieIds[2],
    //         'room_id' => $room1,
    //         'start_time' => now()->addDays(3)->startOfDay()->addMinutes(600),
    //         'end_time' => now()->addDays(3)->startOfDay()->addMinutes(696),
    //         'status' => 'SCHEDULED',
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ]);
    //     $showtimes[] = $st5;

    //     echo "✓ Created 5 showtimes\n";

    //     // ==================================================
    //     // Step 7: Tạo Giá Vé (Flexible pricing)
    //     // ==================================================
    //     $pricesConfig = [
    //         'Regular' => 75000.00,
    //         'VIP' => 120000.00,
    //         'Sweetbox' => 200000.00,
    //     ];

    //     foreach ($showtimes as $showtimeId) {
    //         // Giá chiếu buổi sáng/chiều (thường)
    //         if ($showtimeId === $st3 || $showtimeId === $st4) {
    //             // Twisters - giá bình thường
    //             $pricesConfig = [
    //                 'Regular' => 75000.00,
    //                 'VIP' => 120000.00,
    //                 'Sweetbox' => 180000.00,
    //             ];
    //         }

    //         foreach ($pricesConfig as $seatType => $price) {
    //             DB::table('ticket_prices')->insert([
    //                 'showtime_id' => $showtimeId,
    //                 'seat_type' => $seatType,
    //                 'price' => $price,
    //                 'status' => 'ACTIVE',
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);
    //         }
    //     }

    //     echo "✓ Created ticket prices for all showtimes\n";

    //     // ==================================================
    //     // Step 8: Tạo Tài Khoản Người Dùng (Breeze + Cinema System)
    //     // ==================================================
    //     // ⚠️ Breeze dùng 'name' và 'password' (auto hash bằng caster)
    //     $roles = DB::table('roles')->get()->keyBy('role_name');

    //     // Admin
    //     $admin = DB::table('users')->insertGetId([
    //         'role_id' => $roles['ADMIN']->id,
    //         'cinema_id' => null,
    //         'name' => 'Nguyễn Văn Admin',
    //         'email' => 'admin@cinema.local',
    //         'phone' => '0901234567',
    //         'password' => Hash::make('admin123'),
    //         'loyalty_points' => 0,
    //         'status' => 'ACTIVE',
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ]);

    //     // Manager
    //     $manager = DB::table('users')->insertGetId([
    //         'role_id' => $roles['MANAGER']->id,
    //         'cinema_id' => $cinema,
    //         'name' => 'Trần Thị Manager',
    //         'email' => 'manager@cgv.local',
    //         'phone' => '0902345678',
    //         'password' => Hash::make('manager123'),
    //         'loyalty_points' => 0,
    //         'status' => 'ACTIVE',
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ]);

    //     // Staff
    //     $staff = DB::table('users')->insertGetId([
    //         'role_id' => $roles['STAFF']->id,
    //         'cinema_id' => $cinema,
    //         'name' => 'Hoàng Văn Staff',
    //         'email' => 'staff@cgv.local',
    //         'phone' => '0903456789',
    //         'password' => Hash::make('staff123'),
    //         'loyalty_points' => 0,
    //         'status' => 'ACTIVE',
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ]);

    //     // Khách hàng
    //     $customers = [];
    //     for ($i = 1; $i <= 5; $i++) {
    //         $customerId = DB::table('users')->insertGetId([
    //             'role_id' => $roles['USER']->id,
    //             'cinema_id' => null,
    //             'name' => "Khách Hàng $i",
    //             'email' => "customer$i@example.com",
    //             'phone' => "090" . str_pad($i, 7, '0', STR_PAD_LEFT),
    //             'password' => Hash::make('user123'),
    //             'loyalty_points' => $i * 100,
    //             'status' => 'ACTIVE',
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);
    //         $customers[] = $customerId;
    //     }

    //     echo "✓ Created 8 users (1 admin, 1 manager, 1 staff, 5 customers)\n";

    //     // ==================================================
    //     // Step 9: Tạo Booking Mẫu
    //     // ==================================================
    //     // Booking 1: Avatar - Cinema 1 - 2 ghế Regular + 1 ghế VIP
    //     $booking1 = DB::table('bookings')->insertGetId([
    //         'user_id' => $customers[0],
    //         'showtime_id' => $st1,
    //         'total_price' => 75000 + 75000 + 120000,
    //         'status' => 'Paid',
    //         'payment_method' => 'VNPay',
    //         'booking_time' => now()->subHours(1),
    //         'payment_time' => now()->subMinutes(30),
    //         'booking_code' => 'BK' . uniqid(),
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ]);

    //     // Booked seats cho booking 1
    //     $seatsForBooking1 = [1, 2, 13]; // A1, A2, D3 (cách lấy seat_id: row * 10 + number - 1)
    //     foreach ($seatsForBooking1 as $index => $seatId) {
    //         $priceAtBooking = $index < 2 ? 75000 : 120000;
    //         DB::table('booked_seats')->insert([
    //             'booking_id' => $booking1,
    //             'seat_id' => $seatId,
    //             'price_at_booking' => $priceAtBooking,
    //             'status' => 'PAID',
    //             'qr_code' => 'QR' . uniqid(),
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);
    //     }

    //     // Booking 2: Twisters - Cinema 1 - 3 ghế Regular (Pending)
    //     $booking2 = DB::table('bookings')->insertGetId([
    //         'user_id' => $customers[1],
    //         'showtime_id' => $st3,
    //         'total_price' => 75000 * 3,
    //         'status' => 'Pending',
    //         'payment_method' => null,
    //         'booking_time' => now(),
    //         'booking_code' => 'BK' . uniqid(),
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ]);

    //     // Booked seats cho booking 2
    //     $seatsForBooking2 = [50, 51, 52]; // B1, B2, B3
    //     foreach ($seatsForBooking2 as $seatId) {
    //         DB::table('booked_seats')->insert([
    //             'booking_id' => $booking2,
    //             'seat_id' => $seatId,
    //             'price_at_booking' => 75000,
    //             'status' => 'RESERVED',
    //             'qr_code' => 'QR' . uniqid(),
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);
    //     }

    //     echo "✓ Created 2 sample bookings (1 Paid, 1 Pending)\n";

    //     echo "\n" . str_repeat("=", 60) . "\n";
    //     echo "✨ Database seeding completed successfully!\n";
    //     echo str_repeat("=", 60) . "\n";
    //     echo "\n📝 Test Credentials:\n";
    //     echo "   Admin: admin@cinema.local / admin123\n";
    //     echo "   Manager: manager@cgv.local / manager123\n";
    //     echo "   Staff: staff@cgv.local / staff123\n";
    //     echo "   Customer 1: customer1@example.com / user123\n";
    //     echo "\n💾 Ready to test! Run: php artisan serve\n\n";
    // }

    /**
     * Helper function: Tạo ghế cho phòng
     *
     * @param int $roomId
     * @param array $config: [['row', count, 'type'], ...]
     */
    // private function createSeats(int $roomId, array $config): void
    // {
    //     foreach ($config as [$row, $count, $type]) {
    //         for ($i = 1; $i <= $count; $i++) {
    //             DB::table('seats')->insert([
    //                 'room_id' => $roomId,
    //                 'row_name' => $row,
    //                 'seat_number' => $i,
    //                 'seat_type' => $type,
    //                 'status' => 'AVAILABLE',
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);
    //         }
    //     }
    }
}
