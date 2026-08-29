<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Cinema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Lấy hoặc tự động tạo sẵn các Role chuẩn của hệ thống (ADMIN, STAFF, MANAGER, USER)
        $adminRole   = Role::firstOrCreate(['role_name' => 'ADMIN'], ['description' => 'Quản trị viên hệ thống']);
        $staffRole   = Role::firstOrCreate(['role_name' => 'STAFF'], ['description' => 'Nhân viên rạp']);
        $managerRole = Role::firstOrCreate(['role_name' => 'MANAGER'], ['description' => 'Quản lý rạp']);
        $userRole    = Role::firstOrCreate(['role_name' => 'USER'], ['description' => 'Khách hàng xem phim']);

        // 2. Lấy hoặc tạo sẵn Rạp mặc định để gán cho Staff/Manager (Tránh dính lỗi 403 CheckCinemaAssignment / SQL Error)
        $defaultCinema = Cinema::firstOrCreate(
            ['name' => 'movieGo Sư Vạn Hạnh'],
            [
                'address' => '123 Sư Vạn Hạnh, Phường 12, Quận 10, TP.HCM',
                'description' => 'Rạp chiếu phim hiện đại với đầy đủ tiện nghi chuẩn quốc tế.',
                'city' => 'TP.HCM',
                'status' => 'ACTIVE'
            ]
        );
        $cinemaId = $defaultCinema->id;

        $users = [
            [
                'name'      => 'System Admin',
                'email'     => 'admin@moviego.vn',
                'role_id'   => $adminRole->id,
                'cinema_id' => null,
            ],
            [
                'name'      => 'Cinema Staff',
                'email'     => 'staff@moviego.vn',
                'role_id'   => $staffRole->id,
                'cinema_id' => $cinemaId,
            ],
            [
                'name'      => 'Cinema Manager',
                'email'     => 'manager@moviego.vn',
                'role_id'   => $managerRole->id,
                'cinema_id' => $cinemaId,
            ],
            [
                'name'      => 'Customer User',
                'email'     => 'huygumball02@gmail.com',
                'role_id'   => $userRole->id,
                'cinema_id' => null,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name'              => $userData['name'],
                    'password'          => Hash::make('password'),
                    'role_id'           => $userData['role_id'],
                    'cinema_id'         => $userData['cinema_id'],
                    'status'            => 'ACTIVE',
                    'email_verified_at' => now(),
                ]
            );
        }

        if ($this->command) {
            $this->command->info('✅ Đã khởi tạo các tài khoản phân quyền mặc định thành công!');
        }
    }
}
