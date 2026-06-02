Database Schema Guide - Hệ thống Đặt vé Xem phim trực tuyến

================================================================================
1. TỔNG QUAN CẤU TRÚC
================================================================================

Module 1: Auth & RBAC (Xác thực & Phân quyền)
├─ roles: Vai trò (USER, STAFF, MANAGER, ADMIN)
└─ users: Tài khoản người dùng

Module 2: Cinema Core (Quản lý Rạp)
├─ cinemas: Danh sách rạp chiếu phim
├─ rooms: Phòng chiếu (mỗi rạp có nhiều phòng)
└─ seats: Sơ đồ ghế vật lý (mỗi phòng có nhiều ghế)

Module 3: Movies & Showtimes (Quản lý Phim)
├─ movies: Danh sách phim
├─ showtimes: Lịch chiếu (mỗi phim có nhiều suất chiếu)
└─ ticket_prices: Giá vé linh hoạt theo suất chiếu + loại ghế

Module 4: Booking & Transactions (Đặt vé & Giao dịch)
├─ bookings: Đơn hàng mua vé
└─ booked_seats: Chi tiết vé (danh sách ghế trong mỗi đơn hàng)

================================================================================
2. MIGRATION FILES
================================================================================

Chạy migration theo thứ tự sau:

```bash
php artisan migrate
```

Thứ tự sẽ tự động được sắp xếp:
1. create_roles_table
2. create_cinemas_table
3. create_users_table
4. create_movies_table
5. create_rooms_table
6. create_seats_table
7. create_showtimes_table
8. create_ticket_prices_table
9. create_bookings_table
10. create_booked_seats_table
11. seed_initial_roles (Auto seed roles)

================================================================================
3. TÍNH NĂNG AN TOÀN & HIỆU SUẤT
================================================================================

✅ Data Integrity:
   - Foreign Keys với ON DELETE CASCADE/RESTRICT phù hợp
   - UNIQUE constraints để chống trùng dữ liệu
   - Composite indexes để tối ưu query

✅ Security:
   - password_hash phải được bcrypt hash trước lưu (sử dụng Hash::make())
   - Không bao giờ log mật khẩu plaintext
   - Validate input trước insert

✅ Concurrency Control (CRITICAL):
   - Row-level locking bằng SELECT ... FOR UPDATE
   - Transaction isolation level = READ COMMITTED
   - Retry logic cho DeadlockException

✅ Timestamps:
   - Tất cả bảng chính đều có created_at, updated_at
   - bookings thêm payment_time, cancelled_at để track

================================================================================
4. CÁC QUERY PHỔ BIẾN
================================================================================

### A. Tìm kiếm Phim & Suất chiếu

-- Tìm tất cả suất chiếu của phim "Avatar" (tất cả rạp, tất cả phòng)
SELECT s.*, m.title, m.duration, r.name as room_name, c.name as cinema_name
FROM showtimes s
JOIN movies m ON s.movie_id = m.id
JOIN rooms r ON s.room_id = r.id
JOIN cinemas c ON r.cinema_id = c.id
WHERE m.title LIKE '%Avatar%'
  AND s.status = 'SCHEDULED'
  AND s.start_time >= NOW()
ORDER BY s.start_time ASC;

-- Tìm suất chiếu theo rạp & ngày
SELECT s.*, m.title, r.name, tp.price
FROM showtimes s
JOIN movies m ON s.movie_id = m.id
JOIN rooms r ON s.room_id = r.id
JOIN ticket_prices tp ON s.id = tp.showtime_id
WHERE r.cinema_id = 1
  AND DATE(s.start_time) = '2026-06-02'
  AND s.status = 'SCHEDULED'
ORDER BY s.start_time ASC;

### B. Kiểm tra Ghế Trống

-- Lấy danh sách ghế trống của suất chiếu (showtime_id = 5)
SELECT s.* 
FROM seats s
WHERE s.room_id = (
    SELECT room_id FROM showtimes WHERE id = 5
)
  AND s.status = 'AVAILABLE'
  AND s.id NOT IN (
    -- Loại trừ ghế đã được đặt (PAID hoặc RESERVED)
    SELECT bs.seat_id 
    FROM booked_seats bs
    JOIN bookings b ON bs.booking_id = b.id
    WHERE b.showtime_id = 5
      AND b.status IN ('Paid', 'Pending')
  )
ORDER BY s.row_name, s.seat_number;

### C. Tạo Booking với Race Condition Protection ⚠️

-- PHP/Laravel Code
use Illuminate\Support\Facades\DB;

public function createBooking($userId, $showtimeId, $selectedSeatIds)
{
    // ❗ CRITICAL: Dùng transaction + locking
    try {
        return DB::transaction(function () use ($userId, $showtimeId, $selectedSeatIds) {
            
            // Step 1: Lock các hàng ghế (chỉ 1 request được giữ lock, các request khác phải đợi)
            $lockedSeats = DB::table('booked_seats')
                ->join('seats', 'booked_seats.seat_id', '=', 'seats.id')
                ->join('bookings', 'booked_seats.booking_id', '=', 'bookings.id')
                ->where('bookings.showtime_id', $showtimeId)
                ->whereIn('seats.id', $selectedSeatIds)
                ->where('bookings.status', '!=', 'Cancelled')
                ->lockForUpdate()  // SELECT ... FOR UPDATE (MySQL/PostgreSQL)
                ->get();
            
            // Step 2: Kiểm tra xem ghế đã bị đặt hay chưa
            if ($lockedSeats->count() > 0) {
                throw new Exception('Một hoặc nhiều ghế đã được đặt. Vui lòng chọn ghế khác!');
            }
            
            // Step 3: Tính tổng giá từ ticket_prices
            $ticketPrices = DB::table('ticket_prices')
                ->where('showtime_id', $showtimeId)
                ->where('status', 'ACTIVE')
                ->get()
                ->keyBy('seat_type');
            
            $totalPrice = 0;
            $seatDetails = [];
            
            foreach ($selectedSeatIds as $seatId) {
                $seat = DB::table('seats')->find($seatId);
                $price = $ticketPrices[$seat->seat_type]->price ?? 0;
                $totalPrice += $price;
                $seatDetails[] = [
                    'seat_id' => $seatId,
                    'price_at_booking' => $price,
                ];
            }
            
            // Step 4: Tạo booking
            $booking = DB::table('bookings')->insertGetId([
                'user_id' => $userId,
                'showtime_id' => $showtimeId,
                'total_price' => $totalPrice,
                'status' => 'Pending',
                'payment_method' => null,
                'booking_time' => now(),
                'booking_code' => 'BK' . uniqid(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Step 5: Insert booked_seats (safe vì có lock từ step 1)
            foreach ($seatDetails as $detail) {
                DB::table('booked_seats')->insert([
                    'booking_id' => $booking,
                    'seat_id' => $detail['seat_id'],
                    'price_at_booking' => $detail['price_at_booking'],
                    'status' => 'RESERVED',
                    'qr_code' => $this->generateQRCode(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            return $booking;
            
        }, 5); // Retry tối đa 5 lần nếu deadlock
        
    } catch (\Illuminate\Database\QueryException $e) {
        if ($e->getCode() === '40001') { // Serialization failure
            throw new Exception('Có quá nhiều khách đặt vé cùng lúc, vui lòng thử lại!');
        }
        throw $e;
    }
}

### D. Cập nhật Status Booking khi Thanh Toán

-- Sau khi khách thanh toán thành công (Momo, VNPay)
UPDATE bookings 
SET status = 'Paid', 
    payment_method = 'VNPay',
    payment_time = NOW()
WHERE id = 1001 AND status = 'Pending';

-- Update status các vé trong booking
UPDATE booked_seats 
SET status = 'PAID' 
WHERE booking_id = 1001;

### E. Báo cáo Doanh Thu (groupby ngày, theo rạp)

SELECT 
    DATE(b.payment_time) as booking_date,
    c.name as cinema_name,
    m.title as movie_title,
    s.seat_type,
    COUNT(*) as qty,
    SUM(bs.price_at_booking) as revenue
FROM booked_seats bs
JOIN bookings b ON bs.booking_id = b.id
JOIN showtimes sh ON b.showtime_id = sh.id
JOIN movies m ON sh.movie_id = m.id
JOIN rooms r ON sh.room_id = r.id
JOIN cinemas c ON r.cinema_id = c.id
WHERE b.status = 'Paid'
  AND b.payment_time >= '2026-06-01'
GROUP BY booking_date, cinema_name, movie_title, s.seat_type
ORDER BY booking_date DESC, revenue DESC;

================================================================================
5. TRANSACTION ISOLATION LEVEL (Quan trọng!)
================================================================================

🔴 ĐỪNG dùng DEFAULT: READ UNCOMMITTED (bẩn, mất consistency)
🟠 TRÁNH: SERIALIZABLE (quá chặt, performance tệ)
🟢 DÙNG: READ COMMITTED hoặc REPEATABLE READ

Trong Laravel, set tại database.php:

'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', 3306),
    'database' => env('DB_DATABASE'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => false,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        // Set isolation level
        PDO::ATTR_AUTOCOMMIT => 0,
    ]) : [],
],

Hoặc trực tiếp trong connection:
DB::statement("SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED");

================================================================================
6. KIỂM TRA DEADLOCK & OPTIMIZATION
================================================================================

-- Nếu có deadlock, check query plan
EXPLAIN SELECT bs.* FROM booked_seats bs WHERE seat_id IN (1,2,3);

-- Xem các lock hiện tại (MySQL)
SHOW OPEN TABLES WHERE In_use > 0;

-- Xem transaction đang chạy
SHOW ENGINE INNODB STATUS;

-- Tối ưu: Thêm compound index nếu cần
ALTER TABLE booked_seats ADD INDEX idx_booking_status (booking_id, status);

================================================================================
7. SEED DỮ LIỆU THỬ NGHIỆM
================================================================================

Tạo file: database/seeders/MovieCinemaSeeder.php

<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MovieCinemaSeeder extends Seeder
{
    public function run()
    {
        // 1. Tạo rạp
        $cinema = DB::table('cinemas')->insertGetId([
            'name' => 'CGV Sư Vạn Hạnh',
            'address' => '123 Sư Vạn Hạnh, Q.10, TPHCM',
            'city' => 'Hồ Chí Minh',
            'phone' => '0123456789',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // 2. Tạo phòng chiếu
        $room = DB::table('rooms')->insertGetId([
            'cinema_id' => $cinema,
            'name' => 'Cinema 1',
            'format' => 'IMAX',
            'total_seats' => 100,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // 3. Tạo ghế (5 hàng, 10 ghế/hàng)
        $rowNames = ['A', 'B', 'C', 'D', 'E'];
        foreach ($rowNames as $row) {
            for ($i = 1; $i <= 10; $i++) {
                $seatType = ($row === 'E') ? 'VIP' : 'Regular';
                DB::table('seats')->insert([
                    'room_id' => $room,
                    'row_name' => $row,
                    'seat_number' => $i,
                    'seat_type' => $seatType,
                    'status' => 'AVAILABLE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        // 4. Tạo phim
        $movie = DB::table('movies')->insertGetId([
            'title' => 'Avatar',
            'description' => 'Câu chuyện tình yêu trên hành tinh Pandora',
            'director' => 'James Cameron',
            'duration' => 162,
            'age_rating' => 'P',
            'status' => 'NOW_SHOWING',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // 5. Tạo suất chiếu
        $showtime = DB::table('showtimes')->insertGetId([
            'movie_id' => $movie,
            'room_id' => $room,
            'start_time' => now()->addHours(2),
            'end_time' => now()->addHours(4)->addMinutes(42),
            'status' => 'SCHEDULED',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // 6. Tạo giá vé
        DB::table('ticket_prices')->insert([
            [
                'showtime_id' => $showtime,
                'seat_type' => 'Regular',
                'price' => 75000.00,
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'showtime_id' => $showtime,
                'seat_type' => 'VIP',
                'price' => 120000.00,
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        
        // 7. Tạo tài khoản admin
        $adminRole = DB::table('roles')->where('role_name', 'ADMIN')->first();
        DB::table('users')->insert([
            'role_id' => $adminRole->id,
            'cinema_id' => null,
            'full_name' => 'Admin User',
            'email' => 'admin@cinema.local',
            'phone' => '0912345678',
            'password_hash' => Hash::make('admin123'),
            'loyalty_points' => 0,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

Chạy: php artisan db:seed --class=MovieCinemaSeeder

================================================================================
8. CÓ THỂ THÊM CỦA FUTURE
================================================================================

- Promotions/Discount table (giảm giá)
- Payment_transactions table (chi tiết giao dịch thanh toán)
- Auditing table (log tất cả thay đổi quan trọng)
- Seat_availability view (materialized view để tối ưu query ghế trống)
- Invoice/Receipt table (hóa đơn điện tử)

================================================================================
Created with ❤️ by Senior Database Engineer
Xây dựng ngày: 02/06/2026
================================================================================
