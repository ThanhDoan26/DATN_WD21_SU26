<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ========================================
     * MODULE: Cinema Core
     * Bảng: rooms
     * ========================================
     * Mô tả: Quản lý phòng chiếu trong mỗi rạp
     * 
     * Ví dụ:
     * - Rạp CGV (cinema_id=1) có 10 phòng (Cinema 1, Cinema 2, ..., IMAX 1)
     * - Mỗi phòng có format khác nhau (2D, 3D, IMAX)
     * - Mỗi phòng có sơ đồ ghế riêng (stored in seats table)
     * 
     * ON DELETE CASCADE: Nếu xóa rạp, toàn bộ phòng của rạp đó cũng bị xóa
     *                   → Suất chiếu cũng bị xóa (CASCADE tiếp)
     *                   → Đơn hàng sẽ phải handle carefully (chỉ xóa booking nếu chưa thanh toán)
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key: Rạp mà phòng thuộc về
            $table->foreignId('cinema_id')
                ->constrained('cinemas')
                ->onDelete('cascade'); // Xóa rạp → xóa phòng
            
            // Tên phòng (VD: Cinema 1, IMAX 2, VIP Room)
            $table->string('name');
            
            // Format phòng (2D, 3D, IMAX, 4DX, etc.)
            $table->string('format')->default('2D');
            
            // Số lượng ghế tổng cộng (tính toán từ seats table)
            // Trường này có thể cache để tăng tốc độ query
            $table->integer('total_seats')->nullable();
            
            // Trạng thái phòng (ACTIVE, MAINTENANCE, CLOSED)
            $table->string('status')->default('ACTIVE');
            
            $table->timestamps();
            
            // INDEXES
            $table->index('cinema_id');
            $table->index('status');
            
            // UNIQUE: Mỗi rạp không thể có 2 phòng cùng tên
            $table->unique(['cinema_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
