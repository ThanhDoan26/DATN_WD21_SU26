<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ========================================
     * MODULE: Booking & Transactions
     * Bảng: bookings
     * ========================================
     * Mô tả: Đơn hàng/Giao dịch mua vé
     * 
     * Luồng trạng thái đơn hàng:
     * 1. Pending: Khách hàng vừa tạo đơn, chưa thanh toán
     * 2. Paid: Đã thanh toán xong (đó là vé hợp lệ)
     * 3. Cancelled: Bị hủy (refund về ví của khách)
     * 4. Used: Khách đã checkin/sử dụng vé (tùy chọn, để track khách thực tế vào rạp)
     * 
     * Logic người dùng:
     * - USER (khách hàng): Tạo booking qua frontend → thanh toán → Paid
     * - STAFF (nhân viên quầy): Tạo booking trực tiếp (payment_method = Tiền mặt) → Paid
     * - Không ai được xóa booking nếu status ≠ Pending (audit trail)
     * 
     * ON DELETE RESTRICT: Không được xóa user nếu có booking
     *                    (Vì cần giữ lịch sử người mua để làm báo cáo, kế toán)
     * 
     * Chú ý NULL user_id:
     * - Nếu khách mua tại quầy mà không có tài khoản → user_id = NULL
     * - Hoặc tạo tài khoản Guest chung cho tất cả khách vô tài khoản
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key: Người mua (Nullable - khách mua tại quầy có thể không có tài khoản)
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('restrict'); // Không được xóa user nếu có booking (audit trail)
            
            // Foreign Key: Suất chiếu mà khách đặt vé
            $table->foreignId('showtime_id')
                ->constrained('showtimes')
                ->onDelete('restrict'); // Không được xóa suất chiếu nếu có booking
            
            // Tổng tiền của đơn hàng (tổng của tất cả ghế trong đơn hàng)
            $table->decimal('total_price', 10, 2);
            
            // Trạng thái đơn hàng
            $table->string('status')->default('Pending'); // Pending, Paid, Cancelled, Used
            
            // Phương thức thanh toán
            $table->string('payment_method')->nullable(); // Momo, VNPay, Tiền mặt, Direct Banking, etc.
            
            // Thời gian tạo đơn hàng
            $table->dateTime('booking_time');
            
            // Thời gian thanh toán (Paid lúc nào)
            $table->dateTime('payment_time')->nullable();
            
            // Thời gian hủy đơn (nếu bị cancel)
            $table->dateTime('cancelled_at')->nullable();
            
            // Lý do hủy (nếu có)
            $table->string('cancellation_reason')->nullable();
            
            // Mã booking để khách có thể track (unique code for customer)
            $table->string('booking_code')->unique();
            
            // Ghi chú (thêm thông tin nếu cần)
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // INDEXES: Các truy vấn phổ biến
            $table->index('user_id');
            $table->index('showtime_id');
            $table->index('status');
            $table->index('booking_time');
            $table->index('payment_time');
            
            // Composite index: Tìm booking của user theo trạng thái
            $table->index(['user_id', 'status']);
            // Composite index: Tìm booking theo suất chiếu + status (để check conflict, available seats)
            $table->index(['showtime_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
