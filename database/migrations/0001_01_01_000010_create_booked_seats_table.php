<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ========================================
     * MODULE: Booking & Transactions
     * Bảng: booked_seats
     * ========================================
     * Mô tả: Chi tiết vé - danh sách các ghế đã được đặt trong mỗi đơn hàng
     * 
     * Ví dụ:
     * - Booking ID 1001: Khách mua 3 vé cho suất chiếu Avatar 10:00 ngày 02/06/2026
     *   ├─ Ghế A1 (Regular): 75,000 VNĐ
     *   ├─ Ghế A2 (Regular): 75,000 VNĐ
     *   └─ Ghế B5 (VIP): 120,000 VNĐ
     *   => total_price booking = 270,000 VNĐ
     * 
     * 🚨 CRITICAL - RACE CONDITION PROTECTION:
     * ========================================
     * Đây là điểm dễ xảy ra bug nhất: 2 khách cùng lúc select ghế H9 cho suất chiếu 20:00
     * 
     * ⚠️ SOLUTION: Phải dùng Database-level locking
     * 
     * === TRONG APPLICATION CODE (Laravel/PHP) ===
     * 
     * // 1. Bắt đầu transaction
     * DB::transaction(function () {
     *     // 2. Lock các hàng (rows) trong booked_seats - ghế H9, suất chiếu 20:00
     *     //    Chỉ 1 request được giữ lock, các request khác phải đợi
     *     $lockedSeats = DB::table('booked_seats')
     *         ->join('seats', 'booked_seats.seat_id', '=', 'seats.id')
     *         ->where('booked_seats.showtime_id', $showtimeId)
     *         ->whereIn('seats.id', $selectedSeatIds)
     *         ->lockForUpdate()  // SELECT FOR UPDATE (MySQL/PostgreSQL)
     *         ->get();
     * 
     *     // 3. Kiểm tra xem ghế đã bị đặt hay chưa
     *     if ($lockedSeats->count() > 0) {
     *         throw new Exception('Ghế đã được đặt bởi khách khác!');
     *     }
     * 
     *     // 4. Chèn danh sách ghế mới (safe vì có lock)
     *     foreach ($selectedSeatIds as $seatId) {
     *         DB::table('booked_seats')->insert([
     *             'booking_id' => $bookingId,
     *             'seat_id' => $seatId,
     *             'price_at_booking' => $priceAtBooking,
     *             'created_at' => now(),
     *         ]);
     *     }
     * }, 5); // Retry 5 lần nếu transaction fail
     * 
     * === HOẶC dùng Pessimistic Locking trong Eloquent ===
     * 
     * $bookedSeats = BookedSeat::where('showtime_id', $showtimeId)
     *     ->whereIn('seat_id', $selectedSeatIds)
     *     ->lockForUpdate()  // SELECT ... FOR UPDATE
     *     ->get();
     * 
     * === LẬP TRÌNH VIÊN PHẢI:
     * - Set isolation level = READ COMMITTED hoặc REPEATABLE READ
     * - Dùng FOR UPDATE khi select + insert vào booked_seats
     * - Đặt timeout phù hợp (ví dụ: 30 giây)
     * - Handle DeadlockException và retry
     * 
     * ON DELETE CASCADE: Hủy booking → xóa chi tiết vé
     *                   Xóa ghế → xóa booked_seats (cascad từ seats → rooms → cinemas)
     *                   ⚠️ Nhưng thực tế KHÔNG NÊN xóa ghế (ghế là fixed asset)
     */
    public function up(): void
    {
        Schema::create('booked_seats', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key: Đơn hàng chứa ghế này
            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->onDelete('cascade');
            
            // Foreign Key: Ghế vật lý
            $table->foreignId('seat_id')
                ->constrained('seats')
                ->onDelete('restrict'); // Cảnh báo: không xóa ghế nếu đã có booking
            
            // Giá vé tại thời điểm mua (SNAPSHOT của ticket_prices)
            // 💡 Lý do lưu tường minh:
            //    - Nếu sau đó quản lý thay đổi giá, lịch sử booking vẫn đúng
            //    - Báo cáo doanh thu/kế toán không bị sai
            //    - Khách hàng khiếu nại có bằng chứng giá cũ
            $table->decimal('price_at_booking', 10, 2);
            
            // Status của vé trong booking này
            // - RESERVED: Ghế được đặt (chưa thanh toán)
            // - PAID: Đã thanh toán xong
            // - USED: Khách đã sử dụng (checkin tại rạp)
            // - CANCELLED: Bị hủy (hoàn tiền)
            $table->string('status')->default('RESERVED');
            
            // QR Code để scan khi vào rạp
            $table->string('qr_code')->nullable();
            
            // Thời gian checkin (nếu khách đã vào rạp)
            $table->dateTime('checked_in_at')->nullable();
            
            $table->timestamps();
            
            // INDEXES
            $table->index('booking_id');
            $table->index('seat_id');
            $table->index('status');
            
            // UNIQUE: Một ghế không thể được đặt 2 lần trong cùng 1 booking
            $table->unique(['booking_id', 'seat_id']);
            
            // INDEX: Tìm ghế đã đặt theo suất chiếu (JOIN qua booking → showtime_id)
            // ⚠️ Nên add composite index: (booking_id, status) để tìm vé chưa hủy
            $table->index(['booking_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booked_seats');
    }
};
