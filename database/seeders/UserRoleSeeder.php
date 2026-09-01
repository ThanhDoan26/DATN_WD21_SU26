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
<<<<<<< HEAD
        // 1. Lấy hoặc tự động tạo sẵn các Role chuẩn của hệ thống (ADMIN, STAFF, MANAGER, USER)
=======
        // 1. Tạo các Role chuẩn của hệ thống (ADMIN, STAFF, MANAGER, USER)
>>>>>>> 2e6e9fcd4a23f590b539732e4f8a628303f608df
        $adminRole   = Role::firstOrCreate(['role_name' => 'ADMIN'], ['description' => 'Quản trị viên hệ thống']);
        $staffRole   = Role::firstOrCreate(['role_name' => 'STAFF'], ['description' => 'Nhân viên rạp']);
        $managerRole = Role::firstOrCreate(['role_name' => 'MANAGER'], ['description' => 'Quản lý rạp']);
        $userRole    = Role::firstOrCreate(['role_name' => 'USER'], ['description' => 'Khách hàng xem phim']);

<<<<<<< HEAD
        // 2. Lấy Rạp đầu tiên để gán cho Staff/Manager (Tránh dính lỗi 403 CheckCinemaAssignment)
        $defaultCinema = Cinema::first();
        $cinemaId = $defaultCinema ? $defaultCinema->id : null;

        $users = [
=======
        // 2. Lấy hoặc tạo sẵn các Rạp để gán cho Staff/Manager
        $defaultCinema = Cinema::firstOrCreate(
            ['name' => 'movieGo Sư Vạn Hạnh'],
            [
                'address' => '123 Sư Vạn Hạnh, Phường 12, Quận 10, TP.HCM',
                'city' => 'Hồ Chí Minh',
                'phone' => '028.3838.3838',
                'email' => 'svh@moviego.vn',
                'status' => 'ACTIVE'
            ]
        );
        $cinemaId = $defaultCinema->id;

        $hanoiCinema = Cinema::firstOrCreate(
            ['name' => 'movieGo Vincom Ba Triệu'],
            [
                'address' => '191 Bà Triệu, Hai Bà Trưng, Hà Nội',
                'city' => 'Hà Nội',
                'phone' => '024.3974.8888',
                'email' => 'batrieu@moviego.vn',
                'status' => 'ACTIVE'
            ]
        );

        $danangCinema = Cinema::firstOrCreate(
            ['name' => 'movieGo Vincom Đà Nẵng'],
            [
                'address' => '910A Ngô Quyền, Sơn Trà, Đà Nẵng',
                'city' => 'Đà Nẵng',
                'phone' => '0236.399.6688',
                'email' => 'danang@moviego.vn',
                'status' => 'ACTIVE'
            ]
        );

        // 3. Danh sách Users (2 Admins, 3 Managers, 5 Staffs, 10 Customers)
        $users = [
            // Admins (2)
>>>>>>> 2e6e9fcd4a23f590b539732e4f8a628303f608df
            [
                'name'      => 'System Admin',
                'email'     => 'admin@moviego.vn',
                'role_id'   => $adminRole->id,
                'cinema_id' => null,
<<<<<<< HEAD
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
=======
                'phone'     => '0901112233',
            ],
            [
                'name'      => 'Super Admin Pro',
                'email'     => 'superadmin@moviego.com',
                'role_id'   => $adminRole->id,
                'cinema_id' => null,
                'phone'     => '0902223344',
            ],

            // Managers (3)
            [
                'name'      => 'Manager Sư Vạn Hạnh (HCM)',
                'email'     => 'manager@moviego.vn',
                'role_id'   => $managerRole->id,
                'cinema_id' => $cinemaId,
                'phone'     => '0903334455',
            ],
            [
                'name'      => 'Manager Ba Triệu (Hà Nội)',
                'email'     => 'manager.hn@moviego.vn',
                'role_id'   => $managerRole->id,
                'cinema_id' => $hanoiCinema->id,
                'phone'     => '0904445566',
            ],
            [
                'name'      => 'Manager Vincom Đà Nẵng',
                'email'     => 'manager.dn@moviego.vn',
                'role_id'   => $managerRole->id,
                'cinema_id' => $danangCinema->id,
                'phone'     => '0905556677',
            ],

            // Staffs (5)
            [
                'name'      => 'Cinema Staff HCM 1',
                'email'     => 'staff@moviego.vn',
                'role_id'   => $staffRole->id,
                'cinema_id' => $cinemaId,
                'phone'     => '0911112233',
            ],
            [
                'name'      => 'Cinema Staff HCM 2',
                'email'     => 'staff2@moviego.vn',
                'role_id'   => $staffRole->id,
                'cinema_id' => $cinemaId,
                'phone'     => '0912223344',
            ],
            [
                'name'      => 'Cinema Staff Hà Nội 1',
                'email'     => 'staff.hn@moviego.vn',
                'role_id'   => $staffRole->id,
                'cinema_id' => $hanoiCinema->id,
                'phone'     => '0913334455',
            ],
            [
                'name'      => 'Cinema Staff Hà Nội 2',
                'email'     => 'staff.hn2@moviego.vn',
                'role_id'   => $staffRole->id,
                'cinema_id' => $hanoiCinema->id,
                'phone'     => '0914445566',
            ],
            [
                'name'      => 'Cinema Staff Đà Nẵng',
                'email'     => 'staff.dn@moviego.vn',
                'role_id'   => $staffRole->id,
                'cinema_id' => $danangCinema->id,
                'phone'     => '0915556677',
            ],

            // Customers (10+)
            [
                'name'      => 'Nguyễn Văn Minh',
                'email'     => 'khachhang@gmail.com',
                'role_id'   => $userRole->id,
                'cinema_id' => null,
                'phone'     => '0981112233',
            ],
            [
                'name'      => 'Huy Gumball',
                'email'     => 'huygumball02@gmail.com',
                'role_id'   => $userRole->id,
                'cinema_id' => null,
                'phone'     => '0982223344',
            ],
            [
                'name'      => 'Trần Thị Bình',
                'email'     => 'customer.binh@gmail.com',
                'role_id'   => $userRole->id,
                'cinema_id' => null,
                'phone'     => '0983334455',
            ],
            [
                'name'      => 'Lê Hoàng Cường',
                'email'     => 'customer.cuong@gmail.com',
                'role_id'   => $userRole->id,
                'cinema_id' => null,
                'phone'     => '0984445566',
            ],
            [
                'name'      => 'Phạm Thị Dung',
                'email'     => 'customer.dung@gmail.com',
                'role_id'   => $userRole->id,
                'cinema_id' => null,
                'phone'     => '0985556677',
            ],
            [
                'name'      => 'Hoàng Minh Em',
                'email'     => 'customer.em@gmail.com',
                'role_id'   => $userRole->id,
                'cinema_id' => null,
                'phone'     => '0986667788',
            ],
            [
                'name'      => 'Đỗ Thu Giang',
                'email'     => 'customer.giang@gmail.com',
                'role_id'   => $userRole->id,
                'cinema_id' => null,
                'phone'     => '0987778899',
            ],
            [
                'name'      => 'Vũ Hải Hưng',
                'email'     => 'customer.hung@gmail.com',
                'role_id'   => $userRole->id,
                'cinema_id' => null,
                'phone'     => '0988889900',
            ],
            [
                'name'      => 'Bùi Kim Loan',
                'email'     => 'customer.loan@gmail.com',
                'role_id'   => $userRole->id,
                'cinema_id' => null,
                'phone'     => '0989990011',
            ],
            [
                'name'      => 'Phan Tuấn Nam',
                'email'     => 'customer.nam@gmail.com',
                'role_id'   => $userRole->id,
                'cinema_id' => null,
                'phone'     => '0971112233',
            ],
            [
                'name'      => 'Ngô Ngọc Phượng',
                'email'     => 'customer.phuong@gmail.com',
                'role_id'   => $userRole->id,
                'cinema_id' => null,
                'phone'     => '0972223344',
>>>>>>> 2e6e9fcd4a23f590b539732e4f8a628303f608df
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name'              => $userData['name'],
<<<<<<< HEAD
                    'password'          => Hash::make('password'),
                    'role_id'           => $userData['role_id'],
                    'cinema_id'         => $userData['cinema_id'],
=======
                    'password'          => Hash::make('password123'),
                    'role_id'           => $userData['role_id'],
                    'cinema_id'         => $userData['cinema_id'],
                    'phone'             => $userData['phone'],
>>>>>>> 2e6e9fcd4a23f590b539732e4f8a628303f608df
                    'status'            => 'ACTIVE',
                    'email_verified_at' => now(),
                ]
            );
        }

        if ($this->command) {
<<<<<<< HEAD
            $this->command->info('✅ Đã khởi tạo các tài khoản phân quyền mặc định thành công!');
=======
            $this->command->info('✅ Đã khởi tạo ' . count($users) . ' tài khoản phân quyền mẫu (Admins, Managers, Staffs, Customers)!');
>>>>>>> 2e6e9fcd4a23f590b539732e4f8a628303f608df
        }
    }
}
