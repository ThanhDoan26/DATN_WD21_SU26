<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ========================================
     * MODULE: Cinema Core
     * Bảng: seats
     * ========================================
     * Mô tả: Sơ đồ ghế vật lý của mỗi phòng chiếu
     * 
     * Cấu trúc ghế:
     * - row_name: A, B, C, D, ... (Hàng ghế)
     * - seat_number: 1, 2, 3, ... (Vị trí ghế trong hàng)
     * - seat_type: Thường (Regular), VIP, Sweetbox (sofa)
     * 
     * Ví dụ: Phòng Cinema 1 (room_id=5)
     *   A1 (Regular), A2 (Regular), A3 (Regular), ... A12 (Regular)
     *   B1 (Regular), B2 (Regular), B3 (Regular), ... B12 (Regular)
     *   ...
     *   E1 (VIP), E2 (VIP), E3 (VIP), ... E8 (VIP)
     *   F1 (Sweetbox), F2 (Sweetbox), F3 (Sweetbox), F4 (Sweetbox)
     * 
     * ON DELETE CASCADE: Xóa phòng → xóa toàn bộ ghế
     *                   Ghế bị xóa → booked_seats cũng bị xóa (CASCADE tiếp)
     *                   💡 Điều này OK vì ta chỉ xóa phòng nếu nó chưa có suất chiếu
     */
    public function up(): void
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key: Phòng mà ghế thuộc về
            $table->foreignId('room_id')
                ->constrained('rooms')
                ->onDelete('cascade'); // Xóa phòng → xóa ghế
            
            // Tên hàng ghế (A, B, C, etc.)
            $table->string('row_name');
            
            // Số ghế trong hàng (1, 2, 3, etc.)
            $table->integer('seat_number');
            
            // Loại ghế ảnh hưởng đến giá vé
            // - Regular: Ghế thường
            // - VIP: Ghế VIP (rộng hơn, góc nhìn tốt hơn)
            // - Sweetbox: Ghế sofa/couple seat
            $table->string('seat_type')->default('Regular');
            
            // Trạng thái ghế
            // - AVAILABLE: Ghế còn trống
            // - UNAVAILABLE: Ghế hỏng hoặc không sử dụng được
            // NOTE: Ghế đã được đặt sẽ được track qua bảng booked_seats, không trong bảng seats
            $table->string('status')->default('AVAILABLE');
            
            $table->timestamps();
            
            // INDEXES
            $table->index('room_id');
            $table->index('seat_type');
            $table->index('status');
            
            // UNIQUE: room_id + row_name + seat_number (không thể có 2 ghế trùng vị trí trong 1 phòng)
            $table->unique(['room_id', 'row_name', 'seat_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
