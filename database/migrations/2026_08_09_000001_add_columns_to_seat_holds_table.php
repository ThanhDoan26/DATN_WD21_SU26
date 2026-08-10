<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bổ sung columns cho bảng seat_holds (hiện đang trống chỉ có id + timestamps).
     *
     * Mục đích: TRACKING hold events cho abuse detection.
     * KHÔNG thay thế Booking/BookedSeat — đây KHÔNG phải source of truth cho seat availability.
     */
    public function up(): void
    {
        Schema::table('seat_holds', function (Blueprint $table) {
            // Identity
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('showtime_id')
                ->constrained('showtimes')
                ->onDelete('cascade');

            // Liên kết với Pending booking tương ứng
            $table->foreignId('booking_id')
                ->nullable()
                ->constrained('bookings')
                ->onDelete('set null');

            // Số ghế trong hold (để query nhanh, không cần JOIN booked_seats)
            $table->unsignedTinyInteger('seat_count')->default(1);

            // Status: active, completed, expired, released
            $table->string('status', 20)->default('active');

            // IP chỉ là signal phụ cho audit, KHÔNG dùng làm identity
            $table->string('ip_address', 45)->nullable();

            // Thời điểm tạo hold
            $table->timestamp('held_at')->useCurrent();

            // Server-side expiry timestamp
            $table->timestamp('expires_at');

            // Thời điểm release (payment success, user cancel, hoặc auto-cancel)
            $table->timestamp('released_at')->nullable();

            // INDEXES cho abuse detection queries
            $table->index(['user_id', 'status'], 'idx_seat_holds_user_status');
            $table->index(['expires_at'], 'idx_seat_holds_expires');
            $table->index(['user_id', 'status', 'created_at'], 'idx_seat_holds_user_abuse');
        });
    }

    public function down(): void
    {
        Schema::table('seat_holds', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['user_id']);
            $table->dropForeign(['showtime_id']);
            $table->dropForeign(['booking_id']);

            // Drop indexes
            $table->dropIndex('idx_seat_holds_user_status');
            $table->dropIndex('idx_seat_holds_expires');
            $table->dropIndex('idx_seat_holds_user_abuse');

            // Drop columns
            $table->dropColumn([
                'user_id',
                'showtime_id',
                'booking_id',
                'seat_count',
                'status',
                'ip_address',
                'held_at',
                'expires_at',
                'released_at',
            ]);
        });
    }
};
