<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng ghi nhận abuse events và booking restrictions.
     *
     * abuse_type = 'warning'     → 3 expired holds trong window, chỉ log
     * abuse_type = 'restriction' → 5 expired holds trong window, block booking tạm thời
     *
     * blocked_until = NULL cho warning, timestamp cho restriction.
     */
    public function up(): void
    {
        Schema::create('booking_abuse_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // 'warning' hoặc 'restriction'
            $table->string('abuse_type', 30);

            // Số hold expired tại thời điểm trigger
            $table->unsignedTinyInteger('expired_count')->default(0);

            // Window thời gian đã check (phút)
            $table->unsignedSmallInteger('window_minutes')->default(30);

            // IP tại thời điểm trigger (audit only)
            $table->string('ip_address', 45)->nullable();

            // Chi tiết bổ sung (JSON)
            $table->json('details')->nullable();

            // NULL cho warning, timestamp cho restriction
            $table->timestamp('blocked_until')->nullable();

            $table->timestamps();

            // INDEXES
            $table->index('user_id', 'idx_abuse_user');
            $table->index(['user_id', 'blocked_until'], 'idx_abuse_blocked');
            $table->index('abuse_type', 'idx_abuse_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_abuse_logs');
    }
};
