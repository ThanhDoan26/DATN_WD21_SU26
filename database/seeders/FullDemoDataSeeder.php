<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Movie;
use App\Models\Cinema;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\TicketPrice;
use App\Models\Coupon;
use App\Models\Combo;
use App\Models\Review;
use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;

class FullDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Đang khởi tạo dữ liệu mẫu toàn diện cho website movieGo...');

        // =====================================================================
        // 1. TẠO VAI TRÒ VÀ USER MẪU
        // =====================================================================
        $adminRole = Role::firstOrCreate(['role_name' => 'ADMIN'], ['description' => 'Quản trị viên hệ thống']);
        $userRole  = Role::firstOrCreate(['role_name' => 'CUSTOMER'], ['description' => 'Khách hàng xem phim']);

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@moviego.com'],
            [
                'name' => 'Quản Trị Viên',
                'password' => Hash::make('password123'),
                'role_id' => $adminRole->id,
                'status' => 'ACTIVE',
                'email_verified_at' => now(),
            ]
        );

        $demoUser = User::firstOrCreate(
            ['email' => 'khachhang@gmail.com'],
            [
                'name' => 'Nguyễn Văn Minh',
                'password' => Hash::make('password123'),
                'role_id' => $userRole->id,
                'status' => 'ACTIVE',
                'email_verified_at' => now(),
            ]
        );

        // =====================================================================
        // 2. THỂ LOẠI PHIM (CATEGORIES)
        // =====================================================================
        $categoryData = [
            ['name' => 'Hành động',    'description' => 'Kịch tính, gay cấn với các màn rượt đuổi và cháy nổ đỉnh cao.'],
            ['name' => 'Viễn tưởng',   'description' => 'Khám phá thế giới tương lai, vũ trụ kỳ ảo và công nghệ tân tiến.'],
            ['name' => 'Kinh dị',      'description' => 'Thử thách lòng can đảm với những hiện tượng ma mị và rùng rợn.'],
            ['name' => 'Hài hước',     'description' => 'Mang lại tiếng cười sảng khoái và giây phút thư giãn dễ chịu.'],
            ['name' => 'Tâm lý',       'description' => 'Đi sâu vào diễn biến tâm lý, cảm xúc chiều sâu của con người.'],
            ['name' => 'Tình cảm',     'description' => 'Những câu chuyện tình yêu ngọt ngào, lãng mạn và xúc động.'],
            ['name' => 'Hoạt hình',    'description' => 'Thế giới hoạt hình sắc màu dành cho cả gia đình và trẻ em.'],
            ['name' => 'Phiêu lưu',    'description' => 'Hành trình khám phá những vùng đất mới đầy hiểm nguy và bí ẩn.'],
        ];

        $categories = [];
        foreach ($categoryData as $c) {
            $categories[$c['name']] = Category::firstOrCreate(
                ['slug' => Str::slug($c['name'])],
                ['name' => $c['name'], 'description' => $c['description']]
            );
        }

        // =====================================================================
        // 3. DANH SÁCH PHIM MẪU ĐẦY ĐỦ (MOVIES)
        // =====================================================================
        $moviesData = [
            [
                'title' => 'Deadpool & Wolverine',
                'description' => 'Deadpool tái xuất cùng sự trở lại bùng nổ của Wolverine trong chuyến hành trình giải cứu thế giới đầy những pha hành động nghẹt thở và hài hước.',
                'director' => 'Shawn Levy',
                'cast' => 'Ryan Reynolds, Hugh Jackman, Emma Corrin',
                'poster_url' => 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=73_1biulkYk',
                'duration' => 128,
                'age_rating' => 'T18',
                'format' => ['IMAX', '2D Phụ Đề'],
                'status' => 'NOW_SHOWING',
                'language' => 'Tiếng Anh - Phụ đề Tiếng Việt',
                'country' => 'Mỹ',
                'cat_names' => ['Hành động', 'Viễn tưởng', 'Hài hước'],
            ],
            [
                'title' => 'Inside Out 2 - Những Mảnh Ghép Cảm Xúc 2',
                'description' => 'Riley bước vào tuổi dậy thì cùng sự xuất hiện của các cảm xúc mới như Lo Âu, Ghen Tị, Xấu Hổ và Chán Nản, tạo nên cuộc phiêu lưu đầy thú vị trong tâm trí.',
                'director' => 'Kelsey Mann',
                'cast' => 'Amy Poehler, Maya Hawke, Kensington Tallman',
                'poster_url' => 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=LEjhY15eCx0',
                'duration' => 96,
                'age_rating' => 'P',
                'format' => ['2D Lồng Tiếng', '2D Phụ Đề'],
                'status' => 'NOW_SHOWING',
                'language' => 'Lồng tiếng Tiếng Việt',
                'country' => 'Mỹ',
                'cat_names' => ['Hoạt hình', 'Hài hước'],
            ],
            [
                'title' => 'Lật Mặt 7: Một Điều Ước',
                'description' => 'Câu chuyện cảm động về tình mẫu tử thiêng liêng của người mẹ già nuôi dạy 5 người con khôn lớn. Tác phẩm gây sốt phòng vé Việt Nam.',
                'director' => 'Lý Hải',
                'cast' => 'Thanh Hiền, Trương Minh Cường, Đinh Y Nhung',
                'poster_url' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'duration' => 138,
                'age_rating' => 'K',
                'format' => ['2D'],
                'status' => 'NOW_SHOWING',
                'language' => 'Tiếng Việt',
                'country' => 'Việt Nam',
                'cat_names' => ['Tâm lý', 'Tình cảm'],
            ],
            [
                'title' => 'Dune: Hành Tinh Cát 2',
                'description' => 'Paul Atreides hợp lực cùng Chani và người Fremen để trả thù những kẻ đã hủy hoại gia đình anh, đối mặt với sự lựa chọn giữa tình yêu và số phận vũ trụ.',
                'director' => 'Denis Villeneuve',
                'cast' => 'Timothée Chalamet, Zendaya, Rebecca Ferguson',
                'poster_url' => 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=Way9Dexny3w',
                'duration' => 166,
                'age_rating' => 'T16',
                'format' => ['3D', 'IMAX'],
                'status' => 'NOW_SHOWING',
                'language' => 'Tiếng Anh - Phụ đề Tiếng Việt',
                'country' => 'Mỹ',
                'cat_names' => ['Viễn tưởng', 'Hành động', 'Phiêu lưu'],
            ],
            [
                'title' => 'Godzilla x Kong: Đế Chế Mới',
                'description' => 'Hai quái thú huyền thoại Godzilla và Kong buộc phải liên minh để đối đầu với một mối đe dọa khổng lồ ẩn nấp trong lòng Trái Đất.',
                'director' => 'Adam Wingard',
                'cast' => 'Rebecca Hall, Brian Tyree Henry, Dan Stevens',
                'poster_url' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=lV1OOlGwExM',
                'duration' => 115,
                'age_rating' => 'T13',
                'format' => ['4DX', '3D'],
                'status' => 'NOW_SHOWING',
                'language' => 'Tiếng Anh - Phụ đề Tiếng Việt',
                'country' => 'Mỹ',
                'cat_names' => ['Hành động', 'Viễn tưởng'],
            ],
            [
                'title' => 'Avatar 3: Dòng Chảy Của Nước',
                'description' => 'Siêu phẩm công nghệ điện ảnh đưa khán giả trở lại đại dương hành tinh Pandora với những cuộc chiến bảo vệ gia đình kỳ vĩ.',
                'director' => 'James Cameron',
                'cast' => 'Sam Worthington, Zoe Saldana, Sigourney Weaver',
                'poster_url' => 'https://images.unsplash.com/photo-1518173946687-a4c8a383392e?w=600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=d9MyW72ELq0',
                'duration' => 192,
                'age_rating' => 'T13',
                'format' => ['IMAX', '3D', '4DX'],
                'status' => 'COMING_SOON',
                'language' => 'Tiếng Anh - Phụ đề Tiếng Việt',
                'country' => 'Mỹ',
                'cat_names' => ['Viễn tưởng', 'Phiêu lưu'],
            ],
            [
                'title' => 'Joker: Folie à Deux',
                'description' => 'Arthur Fleck gặp gỡ Harley Quinn tại trại tâm thần Arkham, mở ra chương mới đen tối và lãng mạn đầy ám ảnh.',
                'director' => 'Todd Phillips',
                'cast' => 'Joaquin Phoenix, Lady Gaga, Zazie Beetz',
                'poster_url' => 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=_OKAwz2MsJs',
                'duration' => 138,
                'age_rating' => 'T18',
                'format' => ['2D Phụ Đề'],
                'status' => 'COMING_SOON',
                'language' => 'Tiếng Anh - Phụ đề Tiếng Việt',
                'country' => 'Mỹ',
                'cat_names' => ['Tâm lý', 'Kinh dị'],
            ],
            [
                'title' => 'Kung Fu Panda 4',
                'description' => 'Po chuẩn bị trở thành Thủ Lĩnh Tinh Thần của Thung Lũng Bình Yên và tìm kiếm người kế vị Thần Long Đại Hiệp mới.',
                'director' => 'Mike Mitchell',
                'cast' => 'Jack Black, Awkwafina, Viola Davis',
                'poster_url' => 'https://images.unsplash.com/photo-1535498730771-e735b998cd64?w=600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=_inKs4eeHiI',
                'duration' => 94,
                'age_rating' => 'P',
                'format' => ['2D Lồng Tiếng'],
                'status' => 'ENDED',
                'language' => 'Lồng tiếng Tiếng Việt',
                'country' => 'Mỹ',
                'cat_names' => ['Hoạt hình', 'Hài hước'],
            ],
        ];

        $createdMovies = [];
        foreach ($moviesData as $mData) {
            $catNames = $mData['cat_names'];
            unset($mData['cat_names']);

            $movie = Movie::updateOrCreate(
                ['title' => $mData['title']],
                $mData
            );

            // Gán danh mục
            $syncIds = [];
            foreach ($catNames as $cName) {
                if (isset($categories[$cName])) {
                    $syncIds[] = $categories[$cName]->id;
                }
            }
            if (!empty($syncIds)) {
                $movie->categories()->sync($syncIds);
            }

            $createdMovies[] = $movie;
        }

        $this->command->info('✓ Đã cập nhật ' . count($createdMovies) . ' bộ phim mẫu.');

        // =====================================================================
        // 4. CỤM RẠP & PHÒNG CHIẾU (CINEMAS & ROOMS)
        // =====================================================================
        $cinemasData = [
            [
                'name' => 'movieGo Sư Vạn Hạnh',
                'address' => '123 Sư Vạn Hạnh, Phường 12, Quận 10, TP.HCM',
                'city' => 'Hồ Chí Minh',
                'phone' => '028.3838.3838',
                'email' => 'svh@moviego.vn',
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'movieGo Landmark 81',
                'address' => 'Tầng 3 Landmark 81, 720A Điện Biên Phủ, Bình Thạnh, TP.HCM',
                'city' => 'Hồ Chí Minh',
                'phone' => '028.7300.8181',
                'email' => 'landmark@moviego.vn',
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'movieGo Vincom Ba Triệu',
                'address' => '191 Bà Triệu, Hai Bà Trưng, Hà Nội',
                'city' => 'Hà Nội',
                'phone' => '024.3974.8888',
                'email' => 'batrieu@moviego.vn',
                'status' => 'ACTIVE',
            ],
        ];

        $createdCinemas = [];
        foreach ($cinemasData as $cData) {
            $cinema = Cinema::updateOrCreate(
                ['name' => $cData['name']],
                $cData
            );
            $createdCinemas[] = $cinema;

            // Tạo phòng chiếu cho rạp nếu chưa có
            $roomTypes = [
                ['name' => 'Phòng 01 (Standard 2D)', 'format' => '2D', 'total_seats' => 60],
                ['name' => 'Phòng 02 (3D Digital)',    'format' => '3D', 'total_seats' => 60],
                ['name' => 'Phòng 03 (IMAX Laser)',   'format' => 'IMAX', 'total_seats' => 80],
            ];

            foreach ($roomTypes as $rData) {
                $room = Room::firstOrCreate(
                    ['cinema_id' => $cinema->id, 'name' => $rData['name']],
                    [
                        'format' => $rData['format'],
                        'total_seats' => $rData['total_seats'],
                        'status' => 'ACTIVE',
                    ]
                );

                // Tạo ghế mẫu cho phòng nếu chưa có
                if ($room->seats()->count() == 0) {
                    $rows = ['A', 'B', 'C', 'D', 'E', 'F'];
                    foreach ($rows as $rIdx => $rowLetter) {
                        for ($num = 1; $num <= 10; $num++) {
                            $type = ($rIdx >= 4) ? 'VIP' : 'Regular';
                            if ($rIdx == 5 && ($num == 5 || $num == 6)) {
                                $type = 'Sweetbox';
                            }
                            Seat::create([
                                'room_id' => $room->id,
                                'row_name' => $rowLetter,
                                'seat_number' => $num,
                                'seat_type' => $type,
                                'status' => 'AVAILABLE',
                            ]);
                        }
                    }
                }
            }
        }

        $this->command->info('✓ Đã khởi tạo các cụm rạp và phòng chiếu.');

        // =====================================================================
        // 5. SUẤT CHIẾU MẪU (SHOWTIMES)
        // =====================================================================
        $nowShowingMovies = Movie::where('status', 'NOW_SHOWING')->get();
        $allRooms = Room::all();

        if ($nowShowingMovies->count() > 0 && $allRooms->count() > 0) {
            $times = ['09:30', '13:15', '16:00', '19:30', '21:45'];

            foreach ($nowShowingMovies as $mIdx => $movie) {
                // Tạo suất chiếu cho 3 ngày tới
                for ($day = 0; $day <= 2; $day++) {
                    $dateStr = now()->addDays($day)->format('Y-m-d');
                    $room = $allRooms->random();

                    $timeStr = $times[$mIdx % count($times)];
                    $startCarbon = Carbon::parse($dateStr . ' ' . $timeStr);
                    $endCarbon   = (clone $startCarbon)->addMinutes($movie->duration + 20);

                    // Kiểm tra không trùng suất chiếu
                    $exists = Showtime::where('room_id', $room->id)
                        ->where('start_time', $startCarbon->toDateTimeString())
                        ->exists();

                    if (!$exists) {
                        $showtime = Showtime::create([
                            'movie_id' => $movie->id,
                            'room_id' => $room->id,
                            'start_time' => $startCarbon,
                            'end_time' => $endCarbon,
                            'status' => 'SCHEDULED',
                            'surcharge' => (in_array('IMAX', (array)$movie->format) || in_array('4DX', (array)$movie->format)) ? 20000 : 0,
                        ]);

                        // Set ticket prices
                        TicketPrice::firstOrCreate(
                            ['showtime_id' => $showtime->id, 'seat_type' => 'Regular'],
                            ['price' => 85000, 'status' => 'ACTIVE']
                        );
                        TicketPrice::firstOrCreate(
                            ['showtime_id' => $showtime->id, 'seat_type' => 'VIP'],
                            ['price' => 110000, 'status' => 'ACTIVE']
                        );
                        TicketPrice::firstOrCreate(
                            ['showtime_id' => $showtime->id, 'seat_type' => 'Sweetbox'],
                            ['price' => 160000, 'status' => 'ACTIVE']
                        );
                    }
                }
            }
        }

        $this->command->info('✓ Đã tạo các suất chiếu mẫu cho phim đang chiếu.');

        // =====================================================================
        // 6. MÃ GIẢM GIÁ MẪU (COUPONS)
        // =====================================================================
        $couponsData = [
            [
                'code' => 'MOVIEGO50K',
                'type' => 'fixed',
                'value' => 50000,
                'min_order_value' => 150000,
                'max_discount_amount' => 50000,
                'quantity' => 100,
                'used_count' => 12,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(30),
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'HSSV20',
                'type' => 'percent',
                'value' => 20,
                'min_order_value' => 80000,
                'max_discount_amount' => 40000,
                'quantity' => 200,
                'used_count' => 45,
                'start_date' => now()->subDays(10),
                'end_date' => now()->addDays(60),
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'XEMPHIMHE',
                'type' => 'fixed',
                'value' => 30000,
                'min_order_value' => 100000,
                'max_discount_amount' => 30000,
                'quantity' => 150,
                'used_count' => 8,
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(20),
                'status' => 'ACTIVE',
            ],
        ];

        foreach ($couponsData as $cpData) {
            Coupon::updateOrCreate(
                ['code' => $cpData['code']],
                $cpData
            );
        }

        $this->command->info('✓ Đã tạo các mã giảm giá ưu đãi.');

        // =====================================================================
        // 7. COMBO BẮP NƯỚC MẪU (COMBOS)
        // =====================================================================
        $combosData = [
            [
                'name' => 'Combo Solo',
                'price' => 79000,
                'description' => '1 Bắp ngọt (Large) + 1 Nước ngọt Coca-Cola (Large). Dành cho 1 người xem phim cực thích.',
                'image' => null,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Combo Couple',
                'price' => 119000,
                'description' => '1 Bắp lớn (Vị Phô mai/Caramel) + 2 Nước ngọt Coca-Cola (Large). Lựa chọn hoàn hảo cho cặp đôi.',
                'image' => null,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Combo Family IMAX',
                'price' => 169000,
                'description' => '2 Bắp lớn + 3 Nước ngọt Lớn + 1 Hộp Snack khoai tây. Trọn vẹn niềm vui cho cả gia đình.',
                'image' => null,
                'status' => 'ACTIVE',
            ],
        ];

        foreach ($combosData as $cbData) {
            Combo::updateOrCreate(
                ['name' => $cbData['name']],
                $cbData
            );
        }

        $this->command->info('✓ Đã tạo danh sách combo bắp nước.');

        // =====================================================================
        // 8. ĐÁNH GIÁ PHIM MẪU (REVIEWS)
        // =====================================================================
        $comments = [
            'Phim hay tuyệt vời! Kỹ xảo hình ảnh và âm thanh sống động đỉnh cao.',
            'Cốt truyện cuốn hút từ đầu đến cuối, rất đáng tiền mua vé IMAX.',
            'Diễn xuất của dàn diễn viên quá nhập vai, đoạn kết làm mình phát khóc.',
            'Phim giải trí tốt, nhiều pha hài hước sảng khoái cho cả gia đình.',
            'Hình ảnh đẹp rực rỡ, âm thanh dội từ mọi hướng trong phòng chiếu!',
        ];

        foreach ($nowShowingMovies as $m) {
            Review::firstOrCreate(
                ['user_id' => $demoUser->id, 'movie_id' => $m->id],
                [
                    'rating' => rand(4, 5),
                    'comment' => $comments[array_rand($comments)],
                    'status' => 'ACTIVE',
                ]
            );
        }

        $this->command->info('🎉 HOÀN TẤT THÊM DỮ LIỆU MẪU TOÀN DIỆN CHO MOVIEGO!');
    }
}
