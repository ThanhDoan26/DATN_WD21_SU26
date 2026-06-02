<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ========================================
     * MODULE: Movies & Showtimes
     * Bảng: showtimes
     * ========================================
     * Mô tả: Lịch chiếu (Suất chiếu) của phim trong phòng
     * 
     * Ví dụ:
     * - Phim "Avatar" (movie_id=1) chiếu tại Rạp CGV (room_id=5) lúc 10:00 - 12:30 vào ngày 02/06/2026
     * - Cùng phim "Avatar" có thể chiếu tại nhiều suất khác nhau (10:00, 12:30, 15:00, 18:00, 20:30, 22:00)
     * - Cùng phim "Avatar" có thể chiếu tại nhiều rạp/phòng khác nhau
     * 
     * Từ room_id có thể suy ra cinema_id (qua JOIN với rooms table)
     * 
     * ON DELETE CASCADE: Xóa phim → xóa suất chiếu
     *                   Xóa phòng → xóa suất chiếu
     *                   Suất chiếu bị xóa → booking cũng bị xóa (CASCADE tiếp)
     *                   ⚠️ Chú ý: Chỉ xóa suất chiếu nếu nó chưa có booking
     */
    public function up(): void
    {
        Schema::create('showtimes', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key: Phim
            $table->foreignId('movie_id')
                ->constrained('movies')
                ->onDelete('cascade');
            
            // Foreign Key: Phòng chiếu
            $table->foreignId('room_id')
                ->constrained('rooms')
                ->onDelete('cascade');
            
            // Thời gian bắt đầu suất chiếu
            $table->dateTime('start_time');
            
            // Thời gian kết thúc suất chiếu
            // 💡 Có thể tính từ start_time + duration của movie, nhưng lưu tường minh để dễ query
            $table->dateTime('end_time');
            
            // Trạng thái suất chiếu
            // - SCHEDULED: Chưa chiếu
            // - ONGOING: Đang chiếu
            // - COMPLETED: Đã chiếu xong
            // - CANCELLED: Bị hủy
            $table->string('status')->default('SCHEDULED');
            
            $table->timestamps();
            
            // INDEXES: Các truy vấn phổ biến
            $table->index('movie_id');
            $table->index('room_id');
            $table->index('start_time');
            $table->index('status');
            
            // Composite index: Query suất chiếu theo phim + thời gian
            $table->index(['movie_id', 'start_time']);
            // Composite index: Query suất chiếu theo phòng + thời gian (để check conflict)
            $table->index(['room_id', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('showtimes');
    }
};
