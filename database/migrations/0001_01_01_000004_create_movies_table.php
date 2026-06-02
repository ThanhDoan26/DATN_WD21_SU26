<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ========================================
     * MODULE: Movies & Showtimes
     * Bảng: movies
     * ========================================
     * Mô tả: Quản lý danh sách phim đang chiếu/sắp chiếu tại hệ thống
     * 
     * Chú ý:
     * - Bảng movies là global - không gắn với rạp cụ thể (các rạp chia sẻ thông tin phim)
     * - ADMIN sẽ thêm phim vào hệ thống
     * - Một phim có thể chiếu tại nhiều rạp, nhiều phòng, nhiều suất chiếu khác nhau
     * 
     * Status film:
     * - COMING_SOON: Sắp chiếu (chưa có suất chiếu)
     * - NOW_SHOWING: Đang chiếu
     * - ENDED: Ngưng chiếu (không mở bán vé nữa)
     */
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            
            // Tên phim
            $table->string('title');
            
            // Mô tả/nội dung phim
            $table->text('description')->nullable();
            
            // Đạo diễn
            $table->string('director')->nullable();
            
            // Diễn viên (store dạng string, có thể là JSON hoặc comma-separated)
            $table->text('cast')->nullable();
            
            // URL poster (hình nền)
            $table->string('poster_url')->nullable();
            
            // URL trailer
            $table->string('trailer_url')->nullable();
            
            // Thời lượng phim (tính bằng phút)
            $table->integer('duration');
            
            // Xếp hạng độ tuổi (T18, K, P, etc.)
            // T18: Trên 18 tuổi
            // K: Cho cả gia đình (Kids friendly)
            // P: Phổ thông
            $table->string('age_rating')->nullable();
            
            // Trạng thái phim
            $table->string('status')->default('COMING_SOON'); // COMING_SOON, NOW_SHOWING, ENDED
            
            // Ngôn ngữ phim
            $table->string('language')->nullable();
            
            // Quốc gia sản xuất
            $table->string('country')->nullable();
            
            $table->timestamps();
            
            // INDEXES: Tìm kiếm theo tên, status
            $table->index('title');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
