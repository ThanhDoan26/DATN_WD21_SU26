<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * ========================================
     * INTEGRATED: Breeze + Custom Schema
     * ========================================
     * Bảng users tích hợp Breeze authentication + Cinema booking system
     *
     * ⚠️ Foreign keys (role_id, cinema_id) sẽ được add trong migration
     *    add_foreign_keys_to_users_table.php sau khi roles/cinemas tables đã tạo
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // ========== Breeze Auth Fields ==========
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password'); // Breeze dùng 'password' (được hash tự động)
            $table->rememberToken();

            // ========== Cinema System Fields ==========
            // Role ID (foreign key - sẽ add sau)
            $table->unsignedBigInteger('role_id')->nullable();

            // Cinema ID (foreign key - sẽ add sau)
            $table->unsignedBigInteger('cinema_id')->nullable();

            // Thêm phone (optional)
            $table->string('phone')->nullable();

            // Điểm tích lũy (loyalty points) - chỉ cho khách hàng
            $table->integer('loyalty_points')->default(0);

            // Trạng thái tài khoản (ACTIVE, INACTIVE, SUSPENDED)
            $table->string('status')->default('ACTIVE');

            // Timestamps (Laravel standard)
            $table->timestamps();

            // ========== INDEXES ==========
            $table->index('email');
            $table->index('role_id');
            $table->index('cinema_id');
            $table->index('status');
            $table->index('created_at');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
