<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ========================================
     * MODULE: Movies & Showtimes
     * Bảng: ticket_prices
     * ========================================
     * Mô tả: Giá vé linh hoạt theo từng suất chiếu và loại ghế
     * 
     * Logic giá vé:
     * - Mỗi suất chiếu có thể có giá khác nhau tùy loại ghế
     * - Ví dụ: Suất 10:00 (showtime_id=1)
     *   + Ghế Regular: 75,000 VNĐ
     *   + Ghế VIP: 120,000 VNĐ
     *   + Ghế Sweetbox: 200,000 VNĐ
     * 
     * - Suất 20:00 (showtime_id=2) cùng phim nhưng có thể giá khác
     *   + Ghế Regular: 100,000 VNĐ (giờ vàng)
     *   + Ghế VIP: 150,000 VNĐ
     *   + Ghế Sweetbox: 250,000 VNĐ
     * 
     * 💡 Ưu điểm:
     * - Dễ dàng điều chỉnh giá theo giờ (surge pricing)
     * - Dễ dàng tạo khuyến mãi (giảm giá tại các giờ vàng)
     * - Flexibility cao
     * 
     * ON DELETE CASCADE: Xóa suất chiếu → xóa giá vé
     *                   (Booking vẫn giữ nguyên vì có price_at_booking)
     */
    public function up(): void
    {
        Schema::create('ticket_prices', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key: Suất chiếu
            $table->foreignId('showtime_id')
                ->constrained('showtimes')
                ->onDelete('cascade');
            
            // Loại ghế (Regular, VIP, Sweetbox)
            // ❓ Lưu tường minh thay vì FK sang seats vì:
            //    - ticket_prices cần có giá cho mỗi loại ghế (không cần biết seat_id cụ thể)
            //    - Giảm complexity của query
            //    - Dễ thêm loại ghế mới
            $table->string('seat_type');
            
            // Giá vé (DECIMAL: precision=10, scale=2 = 99,999,999.99)
            // VNĐ không có số lẻ, nhưng DECIMAL(10,2) là standard
            $table->decimal('price', 10, 2);
            
            // Trạng thái giá (ACTIVE, INACTIVE)
            // Để disable giá cũ mà không cần xóa (audit trail)
            $table->string('status')->default('ACTIVE');
            
            $table->timestamps();
            
            // INDEXES
            $table->index('showtime_id');
            $table->index('seat_type');
            
            // UNIQUE: Một suất chiếu không thể có 2 giá cho cùng loại ghế
            $table->unique(['showtime_id', 'seat_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_prices');
    }
};
