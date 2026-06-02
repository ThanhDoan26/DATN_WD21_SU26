<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * ========================================
     * INITIAL DATA SEEDING
     * Khởi tạo dữ liệu mặc định cho hệ thống
     * ========================================
     * 
     * Bảng này tạo ra các vai trò (roles) cơ bản
     * Chỉ chạy lần đầu tiên khi khởi tạo hệ thống
     * 
     * Các role sẽ được tạo:
     * 1. USER: Khách hàng, có thể đặt vé online
     * 2. STAFF: Nhân viên rạp, bán vé tại quầy
     * 3. MANAGER: Quản lý rạp, tạo lịch chiếu, điều chỉnh giá
     * 4. ADMIN: Trùm cuối, quản lý tất cả hệ thống
     */
    public function up(): void
    {
        // Kiểm tra nếu bảng roles có dữ liệu rồi thì không insert lại
        if (DB::table('roles')->count() === 0) {
            DB::table('roles')->insert([
                [
                    'role_name' => 'USER',
                    'description' => 'Khách hàng - Đặt vé online',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'role_name' => 'STAFF',
                    'description' => 'Nhân viên rạp - Bán vé tại quầy',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'role_name' => 'MANAGER',
                    'description' => 'Quản lý rạp - Tạo lịch chiếu, điều chỉnh giá',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'role_name' => 'ADMIN',
                    'description' => 'Admin - Quản lý toàn bộ hệ thống',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    public function down(): void
    {
        DB::table('roles')->whereIn('role_name', ['USER', 'STAFF', 'MANAGER', 'ADMIN'])->delete();
    }
};
