<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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
use App\Models\CinemaReview;
use App\Models\ComboReview;
use App\Models\Booking;
use App\Models\BookedSeat;
use App\Models\BookingCombo;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;

class FullDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Đang khởi tạo dữ liệu mẫu toàn diện cho website movieGo (mỗi chức năng >= 10 dữ liệu)...');

        // =====================================================================
        // 1. THỂ LOẠI PHIM (CATEGORIES - 12 THỂ LOẠI)
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
            ['name' => 'Giật gân',     'description' => 'Hồi hộp đến nghẹt thở với các tình tiết bất ngờ không đoán trước.'],
            ['name' => 'Gia đình',     'description' => 'Phim ấm áp, ý nghĩa phù hợp cho mọi lứa tuổi gia đình cùng xem.'],
            ['name' => 'Khoa học',     'description' => 'Khám phá thế giới khoa học tự nhiên và vũ trụ bao la.'],
            ['name' => 'Âm nhạc',      'description' => 'Giai điệu lôi cuốn, câu chuyện truyền cảm hứng của các nghệ sĩ tài hoa.'],
        ];

        $categories = [];
        foreach ($categoryData as $c) {
            $categories[$c['name']] = Category::firstOrCreate(
                ['name' => $c['name']],
                ['slug' => Str::slug($c['name']), 'description' => $c['description']]
            );
        }
        $this->command->info('✓ Đã cập nhật ' . count($categories) . ' thể loại phim.');

        // =====================================================================
        // 2. DANH SÁCH CỤM RẠP (CINEMAS - 12 CỤM RẠP TOÀN QUỐC)
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
                'name' => 'movieGo Crescent Mall',
                'address' => '101 Tôn Dật Tiên, Tân Phú, Quận 7, TP.HCM',
                'city' => 'Hồ Chí Minh',
                'phone' => '028.5413.7373',
                'email' => 'crescent@moviego.vn',
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
            [
                'name' => 'movieGo Royal City',
                'address' => '72A Nguyễn Trãi, Thượng Đình, Thanh Xuân, Hà Nội',
                'city' => 'Hà Nội',
                'phone' => '024.6664.9999',
                'email' => 'royalcity@moviego.vn',
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'movieGo Metropolis Liễu Giai',
                'address' => '29 Liễu Giai, Ngọc Khánh, Ba Đình, Hà Nội',
                'city' => 'Hà Nội',
                'phone' => '024.3724.6666',
                'email' => 'metropolis@moviego.vn',
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'movieGo Vincom Đà Nẵng',
                'address' => '910A Ngô Quyền, Sơn Trà, Đà Nẵng',
                'city' => 'Đà Nẵng',
                'phone' => '0236.399.6688',
                'email' => 'danang@moviego.vn',
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'movieGo Nguyễn Văn Linh',
                'address' => '160 Nguyễn Văn Linh, Vĩnh Trung, Thanh Khê, Đà Nẵng',
                'city' => 'Đà Nẵng',
                'phone' => '0236.365.8899',
                'email' => 'nguyenvanlinh@moviego.vn',
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'movieGo Vincom Hải Phòng',
                'address' => '1 Lê Thánh Tông, Máy Tơ, Ngô Quyền, Hải Phòng',
                'city' => 'Hải Phòng',
                'phone' => '0225.385.9988',
                'email' => 'haiphong@moviego.vn',
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'movieGo Vincom Cần Thơ',
                'address' => '209 Đường 30 Tháng 4, Xuân Khánh, Ninh Kiều, Cần Thơ',
                'city' => 'Cần Thơ',
                'phone' => '0292.373.8899',
                'email' => 'cantho@moviego.vn',
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'movieGo Vincom Biên Hòa',
                'address' => '1096 Phạm Văn Thuận, Tân Mai, Biên Hòa, Đồng Nai',
                'city' => 'Đồng Nai',
                'phone' => '0251.368.6688',
                'email' => 'bienhoa@moviego.vn',
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'movieGo Nha Trang Center',
                'address' => '20 Trần Phú, Lộc Thọ, Nha Trang, Khánh Hòa',
                'city' => 'Khánh Hòa',
                'phone' => '0258.625.8899',
                'email' => 'nhatrang@moviego.vn',
                'status' => 'ACTIVE',
            ],
        ];

        $createdCinemas = [];
        $createdRooms = [];

        foreach ($cinemasData as $cData) {
            $cinema = Cinema::updateOrCreate(
                ['name' => $cData['name']],
                $cData
            );
            $createdCinemas[] = $cinema;

            // Mỗi rạp tạo ít nhất 2 phòng chiếu
            $roomTypes = [
                ['name' => 'Phòng 01 (Standard 2D)', 'format' => '2D', 'total_seats' => 90],
                ['name' => 'Phòng 02 (IMAX Laser)',   'format' => 'IMAX', 'total_seats' => 90],
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
                $createdRooms[] = $room;

                // Tạo ghế mẫu cho phòng nếu chưa có
                if ($room->seats()->count() == 0) {
                    $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
                    foreach ($rows as $rIdx => $rowLetter) {
                        $numSeats = ($rIdx == 7) ? 6 : 12; // Row H (Sweetbox)
                        for ($num = 1; $num <= $numSeats; $num++) {
                            $type = ($rIdx < 3) ? 'Regular' : (($rIdx < 7) ? 'VIP' : 'Sweetbox');
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
        $this->command->info('✓ Đã cập nhật ' . count($createdCinemas) . ' cụm rạp và ' . count($createdRooms) . ' phòng chiếu.');

        // =====================================================================
        // 3. DANH SÁCH PHIM (MOVIES - 13 BỘ PHIM ĐỦ TRẠNG THÁI)
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
                'poster_url' => 'https://images.unsplash.com/photo-1574267432553-4b4628081c31?w=600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=LEjhY15eCx0',
                'duration' => 96,
                'age_rating' => 'P',
                'format' => ['2D Lồng Tiếng', '2D Phụ Đề'],
                'status' => 'NOW_SHOWING',
                'language' => 'Tiếng Anh - Lồng tiếng & Phụ đề',
                'country' => 'Mỹ',
                'cat_names' => ['Hoạt hình', 'Hài hước', 'Gia đình'],
            ],
            [
                'title' => 'Despicable Me 4 - Kẻ Trộm Mặt Trăng 4',
                'description' => 'Gia đình Gru chào đón thành viên mới Gru Jr., nhưng một kẻ thù nguy hiểm trốn thoát khỏi tù buộc cả gia đình và các Minion phải bước vào cuộc phiêu lưu bảo vệ lẫn nhau.',
                'director' => 'Chris Renaud',
                'cast' => 'Steve Carell, Kristen Wiig, Will Ferrell',
                'poster_url' => 'https://images.unsplash.com/photo-1594909122845-11baa439b7bf?w=600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=qQlr9-rF32A',
                'duration' => 94,
                'age_rating' => 'P',
                'format' => ['2D Lồng Tiếng', '2D Phụ Đề'],
                'status' => 'NOW_SHOWING',
                'language' => 'Tiếng Anh - Lồng tiếng & Phụ đề',
                'country' => 'Mỹ',
                'cat_names' => ['Hoạt hình', 'Hài hước', 'Phiêu lưu'],
            ],
            [
                'title' => 'Twisters - Lốc Xoáy Tử Thần',
                'description' => 'Một nhóm các nhà nghiên cứu và săn đuổi bão mạo hiểm tính mạng để thử nghiệm hệ thống cảnh báo bão tiên tiến giữa tâm điểm những cơn lốc xoáy tàn khốc.',
                'director' => 'Lee Isaac Chung',
                'cast' => 'Daisy Edgar-Jones, Glen Powell, Anthony Ramos',
                'poster_url' => 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=Jb8D-N4Hk1I',
                'duration' => 122,
                'age_rating' => 'K',
                'format' => ['2D Phụ Đề', '4DX'],
                'status' => 'NOW_SHOWING',
                'language' => 'Tiếng Anh - Phụ đề Tiếng Việt',
                'country' => 'Mỹ',
                'cat_names' => ['Hành động', 'Phiêu lưu', 'Giật gân'],
            ],
            [
                'title' => 'A Quiet Place: Day One - Vùng Đất Câm Lặng: Ngày Một',
                'description' => 'Khám phá ngày đầu tiên thế giới rơi vào câm lặng khi những sinh vật ngoài hành tinh sở hữu thính giác siêu phàm tấn công thành phố New York nhộn nhịp.',
                'director' => 'Michael Sarnoski',
                'cast' => 'Lupita Nyong\'o, Joseph Quinn, Alex Wolff',
                'poster_url' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=YPY7J-flzE8',
                'duration' => 100,
                'age_rating' => 'T16',
                'format' => ['2D Phụ Đề', 'IMAX'],
                'status' => 'NOW_SHOWING',
                'language' => 'Tiếng Anh - Phụ đề Tiếng Việt',
                'country' => 'Mỹ',
                'cat_names' => ['Kinh dị', 'Viễn tưởng', 'Giật gân'],
            ],
            [
                'title' => 'Dune: Part Two - Hành Tinh Cát 2',
                'description' => 'Paul Atreides hợp lực cùng Chani và người Fremen để trả thù những kẻ đã hủy hoại gia đình anh, đồng thời đứng trước lựa chọn định đoạt số phận của cả vũ trụ.',
                'director' => 'Denis Villeneuve',
                'cast' => 'Timothée Chalamet, Zendaya, Rebecca Ferguson',
                'poster_url' => 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=Way9Dexny3w',
                'duration' => 166,
                'age_rating' => 'T16',
                'format' => ['IMAX', '2D Phụ Đề'],
                'status' => 'NOW_SHOWING',
                'language' => 'Tiếng Anh - Phụ đề Tiếng Việt',
                'country' => 'Mỹ',
                'cat_names' => ['Viễn tưởng', 'Hành động', 'Phiêu lưu'],
            ],
            [
                'title' => 'Kung Fu Panda 4',
                'description' => 'Po được chọn trở thành Thủ lĩnh Tinh thần của Thung lũng Bình Yên và phải tìm kiếm, huấn luyện một Chiến binh Rồng mới trước khi đối đầu với mụ Tắc Kè Bông xảo quyệt.',
                'director' => 'Mike Mitchell',
                'cast' => 'Jack Black, Awkwafina, Viola Davis',
                'poster_url' => 'https://images.unsplash.com/photo-1563089145-599997674d42?w=600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=_inKs4eeHiI',
                'duration' => 94,
                'age_rating' => 'P',
                'format' => ['2D Lồng Tiếng', '2D Phụ Đề'],
                'status' => 'NOW_SHOWING',
                'language' => 'Tiếng Anh - Lồng tiếng & Phụ đề',
                'country' => 'Mỹ',
                'cat_names' => ['Hoạt hình', 'Hành động', 'Hài hước'],
            ],
            [
                'title' => 'Transformers One',
                'description' => 'Câu chuyện chưa kể về nguồn gốc mối quan hệ gắn bó giữa Optimus Prime và Megatron khi họ còn là những người anh em trước khi trở thành kẻ thù truyền kiếp trên hành tinh Cybertron.',
                'director' => 'Josh Cooley',
                'cast' => 'Chris Hemsworth, Brian Tyree Henry, Scarlett Johansson',
                'poster_url' => 'https://images.unsplash.com/photo-1485846234645-a62644f84728?w=600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=u2NuUVbfEeo',
                'duration' => 104,
                'age_rating' => 'P',
                'format' => ['2D Phụ Đề', 'IMAX'],
                'status' => 'PRE_ORDER',
                'presale_date' => now()->subDays(2),
                'release_date' => now()->addDays(5),
                'language' => 'Tiếng Anh - Phụ đề Tiếng Việt',
                'country' => 'Mỹ',
                'cat_names' => ['Hoạt hình', 'Hành động', 'Viễn tưởng'],
            ],
            [
                'title' => 'Joker: Folie à Deux',
                'description' => 'Arthur Fleck bị giam giữ tại Bệnh viện Tâm thần Arkham để chờ xét xử. Tại đây, anh gặp gỡ tình yêu định mệnh của đời mình và tìm thấy âm nhạc luôn ẩn sâu bên trong.',
                'director' => 'Todd Phillips',
                'cast' => 'Joaquin Phoenix, Lady Gaga, Brendan Gleeson',
                'poster_url' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=_OKAwz2NiJs',
                'duration' => 138,
                'age_rating' => 'T18',
                'format' => ['IMAX', '2D Phụ Đề'],
                'status' => 'PRE_ORDER',
                'presale_date' => now()->subDays(1),
                'release_date' => now()->addDays(7),
                'language' => 'Tiếng Anh - Phụ đề Tiếng Việt',
                'country' => 'Mỹ',
                'cat_names' => ['Tâm lý', 'Âm nhạc', 'Giật gân'],
            ],
            [
                'title' => 'Moana 2 - Hành Trình Đại Dương 2',
                'description' => 'Moana nhận được lời kêu gọi bất ngờ từ tổ tiên và cùng Maui bắt đầu chuyến hải trình mới vượt qua những vùng biển nguy hiểm chưa từng ai đặt chân tới.',
                'director' => 'David G. Derrick Jr.',
                'cast' => 'Auli\'i Cravalho, Dwayne Johnson, Alan Tudyk',
                'poster_url' => 'https://images.unsplash.com/photo-1574267432553-4b4628081c31?w=600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=hDZ7y8RP5HE',
                'duration' => 100,
                'age_rating' => 'P',
                'format' => ['2D Lồng Tiếng', '2D Phụ Đề'],
                'status' => 'COMING_SOON',
                'release_date' => now()->addDays(20),
                'language' => 'Tiếng Anh - Lồng tiếng & Phụ đề',
                'country' => 'Mỹ',
                'cat_names' => ['Hoạt hình', 'Phiêu lưu', 'Âm nhạc'],
            ],
            [
                'title' => 'Gladiator II - Võ Sĩ Giác Đấu 2',
                'description' => 'Nhiều năm sau cái chết của Maximus, Lucius buộc phải bước vào Đấu trường La Mã để chiến đấu giành lại danh dự và tương lai cho đế chế Roma.',
                'director' => 'Ridley Scott',
                'cast' => 'Paul Mescal, Pedro Pascal, Denzel Washington',
                'poster_url' => 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=4rgYUipGJNo',
                'duration' => 148,
                'age_rating' => 'T18',
                'format' => ['2D Phụ Đề', 'IMAX'],
                'status' => 'COMING_SOON',
                'release_date' => now()->addDays(35),
                'language' => 'Tiếng Anh - Phụ đề Tiếng Việt',
                'country' => 'Mỹ',
                'cat_names' => ['Hành động', 'Phiêu lưu', 'Tâm lý'],
            ],
            [
                'title' => 'Avatar 3: Fire and Ash',
                'description' => 'Gia đình Jake Sully chạm trán với Tộc Tro Tàn - bộ tộc Na\'vi hiếu chiến và tàn bạo trên mặt trăng Pandora với những bí mật chưa từng được hé lộ.',
                'director' => 'James Cameron',
                'cast' => 'Sam Worthington, Zoe Saldana, Sigourney Weaver',
                'poster_url' => 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=d9MyW72ELq0',
                'duration' => 190,
                'age_rating' => 'T13',
                'format' => ['IMAX 3D', '3D Digital'],
                'status' => 'SCHEDULED',
                'release_date' => now()->addDays(60),
                'language' => 'Tiếng Anh - Phụ đề Tiếng Việt',
                'country' => 'Mỹ',
                'cat_names' => ['Viễn tưởng', 'Hành động', 'Phiêu lưu'],
            ],
            [
                'title' => 'Spider-Man: Beyond the Spider-Verse',
                'description' => 'Miles Morales đối đầu với định mệnh của chính mình để bảo vệ những người thân yêu và lập lại trật tự cho toàn bộ Đa vũ trụ Nhện.',
                'director' => 'Joaquim Dos Santos',
                'cast' => 'Shameik Moore, Hailee Steinfeld, Oscar Isaac',
                'poster_url' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=cqGjhVJWtEg',
                'duration' => 140,
                'age_rating' => 'P',
                'format' => ['IMAX', '2D Phụ Đề'],
                'status' => 'COMING_SOON',
                'release_date' => now()->addDays(45),
                'language' => 'Tiếng Anh - Phụ đề Tiếng Việt',
                'country' => 'Mỹ',
                'cat_names' => ['Hoạt hình', 'Hành động', 'Viễn tưởng'],
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

            // Sync categories
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
        // 4. SUẤT CHIẾU MẪU (SHOWTIMES - 24+ SUẤT CHIẾU)
        // =====================================================================
        $bookableMovies = Movie::whereIn('status', ['NOW_SHOWING', 'PRE_ORDER'])->get();
        $times = ['09:30', '13:00', '16:15', '19:30', '21:45'];

        $showtimeCount = 0;
        $createdShowtimes = [];

        foreach ($createdRooms as $rIdx => $room) {
            foreach ($bookableMovies as $mIdx => $movie) {
                // Tạo suất chiếu cho ngày hôm nay và 3 ngày tới
                for ($day = 0; $day <= 3; $day++) {
                    $dateStr = now()->addDays($day)->format('Y-m-d');
                    $timeStr = $times[($rIdx + $mIdx + $day) % count($times)];
                    $startCarbon = Carbon::parse($dateStr . ' ' . $timeStr);
                    $endCarbon   = (clone $startCarbon)->addMinutes($movie->duration + 20);

                    // Tránh trùng suất chiếu trong phòng
                    $exists = Showtime::where('room_id', $room->id)
                        ->where('start_time', $startCarbon->toDateTimeString())
                        ->exists();

                    if (!$exists) {
                        $showtime = Showtime::create([
                            'movie_id' => $movie->id,
                            'room_id' => $room->id,
                            'start_time' => $startCarbon,
                            'end_time' => $endCarbon,
                            'status' => Showtime::STATUS_SCHEDULED,
                            'surcharge' => (str_contains($room->name, 'IMAX') || in_array('IMAX', (array)$movie->format)) ? 20000 : 0,
                        ]);

                        // Ticket Prices
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

                        $createdShowtimes[] = $showtime;
                        $showtimeCount++;
                    }
                }
            }
        }
        $this->command->info('✓ Đã khởi tạo ' . $showtimeCount . ' suất chiếu mẫu.');

        // =====================================================================
        // 5. COMBO BẮP NƯỚC (COMBOS - 12 COMBOS)
        // =====================================================================
        $combosData = [
            [
                'name' => 'Combo Solo 1 Bắp 1 Nước',
                'price' => 79000,
                'description' => '1 Bắp ngọt (Large) + 1 Nước ngọt Coca-Cola (Large). Dành cho 1 người xem phim cực thích.',
                'image' => null,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Combo Couple 1 Bắp 2 Nước',
                'price' => 119000,
                'description' => '1 Bắp lớn (Vị Phô mai/Caramel) + 2 Nước ngọt Coca-Cola (Large). Lựa chọn hoàn hảo cho cặp đôi.',
                'image' => null,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Combo Family IMAX Khổng Lồ',
                'price' => 169000,
                'description' => '2 Bắp lớn + 3 Nước ngọt Lớn + 1 Hộp Snack khoai tây. Trọn vẹn niềm vui cho cả gia đình.',
                'image' => null,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Bắp Rang Bơ Phô Mai (Size L)',
                'price' => 59000,
                'description' => 'Bắp rang bơ lắc bột phô mai béo ngậy, thơm lừng giòn tan trong miệng.',
                'image' => null,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Bắp Rang Bơ Caramel (Size L)',
                'price' => 59000,
                'description' => 'Bắp rang bơ phủ sốt caramel ngọt ngào óng ả chuẩn vị rạp chiếu phim.',
                'image' => null,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Bắp Rang Bơ Truyền Thống (Size L)',
                'price' => 49000,
                'description' => 'Bắp rang bơ vàng ươm mặn ngọt thanh nhẹ truyền thống.',
                'image' => null,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Coca-Cola Tươi Mát Lạnh (Size L)',
                'price' => 35000,
                'description' => 'Nước ngọt có gas Coca-Cola có đá giải khát tức thì.',
                'image' => null,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Trà Đào Cam Sả Tươi (Size L)',
                'price' => 45000,
                'description' => 'Trà đào thơm ngát kết hợp cam tươi và sả thanh mát.',
                'image' => null,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Snack Khoai Tây Chiên Giòn Rụm',
                'price' => 29000,
                'description' => 'Khoai tây chiên lát mỏng giòn tan tẩm gia vị đậm đà.',
                'image' => null,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Combo Marvel Limited Edition',
                'price' => 199000,
                'description' => '1 Xô bắp hình nhân vật Marvel + 1 Ly nước đổi màu phiên bản giới hạn.',
                'image' => null,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Hotdog Xúc Xích Phô Mai Đút Lò',
                'price' => 49000,
                'description' => 'Bánh mì kẹp xúc xích Đức xông khói phủ phô mai nướng nóng hổi.',
                'image' => null,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Combo Trẻ Em Kid Box',
                'price' => 69000,
                'description' => '1 Bắp nhỏ + 1 Sữa chua uống + 1 Đồ chơi hoạt hình ngộ nghĩnh.',
                'image' => null,
                'status' => 'ACTIVE',
            ],
        ];

        $createdCombos = [];
        foreach ($combosData as $cbData) {
            $createdCombos[] = Combo::updateOrCreate(
                ['name' => $cbData['name']],
                $cbData
            );
        }
        $this->command->info('✓ Đã cập nhật ' . count($createdCombos) . ' combo bắp nước.');

        // =====================================================================
        // 6. MÃ GIẢM GIÁ (COUPONS - 11 MÃ KHUYẾN MÃI)
        // =====================================================================
        $couponsData = [
            [
                'code' => 'MOVIEGO50K',
                'type' => 'fixed',
                'value' => 50000,
                'min_order_value' => 150000,
                'max_discount_amount' => 50000,
                'quantity' => 200,
                'used_count' => 25,
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
                'quantity' => 500,
                'used_count' => 80,
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
                'used_count' => 18,
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(20),
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'WELCOME2026',
                'type' => 'fixed',
                'value' => 25000,
                'min_order_value' => 70000,
                'max_discount_amount' => 25000,
                'quantity' => 1000,
                'used_count' => 120,
                'start_date' => now()->subDays(30),
                'end_date' => now()->addDays(90),
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'VIPMEMBER30',
                'type' => 'percent',
                'value' => 30,
                'min_order_value' => 200000,
                'max_discount_amount' => 100000,
                'quantity' => 100,
                'used_count' => 15,
                'start_date' => now()->subDays(10),
                'end_date' => now()->addDays(45),
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'WEEKENDDEAL',
                'type' => 'fixed',
                'value' => 40000,
                'min_order_value' => 180000,
                'max_discount_amount' => 40000,
                'quantity' => 250,
                'used_count' => 42,
                'start_date' => now()->subDays(3),
                'end_date' => now()->addDays(25),
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'COMBOFREE15',
                'type' => 'percent',
                'value' => 15,
                'min_order_value' => 120000,
                'max_discount_amount' => 30000,
                'quantity' => 300,
                'used_count' => 35,
                'start_date' => now()->subDays(7),
                'end_date' => now()->addDays(30),
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'IMAXEXPERIENCE',
                'type' => 'fixed',
                'value' => 50000,
                'min_order_value' => 220000,
                'max_discount_amount' => 50000,
                'quantity' => 100,
                'used_count' => 12,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(40),
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'COUPLELOVE',
                'type' => 'fixed',
                'value' => 35000,
                'min_order_value' => 160000,
                'max_discount_amount' => 35000,
                'quantity' => 200,
                'used_count' => 28,
                'start_date' => now()->subDays(8),
                'end_date' => now()->addDays(35),
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'CINEMAPASS',
                'type' => 'fixed',
                'value' => 20000,
                'min_order_value' => 60000,
                'max_discount_amount' => 20000,
                'quantity' => 500,
                'used_count' => 95,
                'start_date' => now()->subDays(15),
                'end_date' => now()->addDays(60),
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'FLASHSALE50',
                'type' => 'percent',
                'value' => 50,
                'min_order_value' => 300000,
                'max_discount_amount' => 150000,
                'quantity' => 50,
                'used_count' => 10,
                'start_date' => now()->subDays(1),
                'end_date' => now()->addDays(7),
                'status' => 'ACTIVE',
            ],
        ];

        foreach ($couponsData as $cpData) {
            Coupon::updateOrCreate(
                ['code' => $cpData['code']],
                $cpData
            );
        }
        $this->command->info('✓ Đã cập nhật ' . count($couponsData) . ' mã giảm giá khuyến mãi.');

        // =====================================================================
        // 7. ĐƠN ĐẶT VÉ MẪU (BOOKINGS, BOOKED SEATS & BOOKING COMBOS - 12 ĐƠN)
        // =====================================================================
        $customers = User::whereHas('role', fn($q) => $q->where('role_name', 'CUSTOMER'))->get();
        if ($customers->isEmpty()) {
            $customers = User::all();
        }

        $sampleShowtimes = Showtime::with(['room.seats', 'movie'])->take(6)->get();

        $bookingStatuses = [
            Booking::STATUS_PAID, Booking::STATUS_PAID, Booking::STATUS_PAID, Booking::STATUS_PAID,
            Booking::STATUS_PAID, Booking::STATUS_PAID, Booking::STATUS_PAID, Booking::STATUS_PAID,
            Booking::STATUS_PENDING, Booking::STATUS_PENDING, Booking::STATUS_CANCELLED, Booking::STATUS_CANCELLED
        ];
        $createdBookings = [];

        foreach ($bookingStatuses as $bIdx => $status) {
            $customer = $customers[$bIdx % $customers->count()];
            $showtime = $sampleShowtimes[$bIdx % $sampleShowtimes->count()];
            $availableSeats = $showtime->room->seats->where('status', 'AVAILABLE')->values();

            $seat1 = $availableSeats[$bIdx * 2 % $availableSeats->count()];
            $seat2 = $availableSeats[($bIdx * 2 + 1) % $availableSeats->count()];

            $totalPrice = 170000 + ($bIdx * 10000);
            $bookingCode = 'BK' . date('Ymd') . strtoupper(Str::random(6));

            $booking = Booking::create([
                'user_id' => $customer->id,
                'showtime_id' => $showtime->id,
                'total_price' => $totalPrice,
                'status' => $status,
                'payment_method' => ($status === Booking::STATUS_PAID) ? 'VNPAY' : 'COUNTER',
                'booking_time' => now()->subHours(rand(1, 48)),
                'payment_time' => ($status === Booking::STATUS_PAID) ? now()->subHours(rand(1, 47)) : null,
                'cancelled_at' => ($status === Booking::STATUS_CANCELLED) ? now()->subHours(rand(1, 10)) : null,
                'cancellation_reason' => ($status === Booking::STATUS_CANCELLED) ? 'Khách hàng đổi lịch bận đột xuất' : null,
                'booking_code' => $bookingCode,
                'ticket_token' => ($status === Booking::STATUS_PAID) ? (string) Str::uuid() : null,
                'notes' => 'Đơn đặt vé trực tuyến website movieGo',
            ]);

            // Create Booked Seats
            BookedSeat::create([
                'booking_id' => $booking->id,
                'seat_id' => $seat1->id,
                'price_at_booking' => 85000,
                'status' => ($status === Booking::STATUS_PAID) ? 'PAID' : 'RESERVED',
                'qr_code' => 'QR_' . strtoupper(Str::random(10)),
            ]);

            BookedSeat::create([
                'booking_id' => $booking->id,
                'seat_id' => $seat2->id,
                'price_at_booking' => 85000,
                'status' => ($status === Booking::STATUS_PAID) ? 'PAID' : 'RESERVED',
                'qr_code' => 'QR_' . strtoupper(Str::random(10)),
            ]);

            // Create Booking Combos
            if (!empty($createdCombos)) {
                $combo = $createdCombos[$bIdx % count($createdCombos)];
                BookingCombo::create([
                    'booking_id' => $booking->id,
                    'combo_id' => $combo->id,
                    'quantity' => 1,
                    'price' => $combo->price,
                ]);
            }

            $createdBookings[] = $booking;
        }
        $this->command->info('✓ Đã khởi tạo ' . count($createdBookings) . ' đơn đặt vé mẫu (Paid, Pending, Cancelled) kèm vé & combo.');

        // =====================================================================
        // 8. ĐÁNH GIÁ (REVIEWS - >= 10 PHIM, >= 10 RẠP, >= 10 COMBO)
        // =====================================================================
        $movieComments = [
            'Phim hay tuyệt vời! Kỹ xảo hình ảnh và âm thanh sống động đỉnh cao.',
            'Cốt truyện cuốn hút từ đầu đến cuối, rất đáng tiền mua vé IMAX.',
            'Diễn xuất của dàn diễn viên quá nhập vai, đoạn kết làm mình phát khóc.',
            'Phim giải trí tốt, nhiều pha hài hước sảng khoái cho cả gia đình.',
            'Hình ảnh đẹp rực rỡ, âm thanh dội từ mọi hướng trong phòng chiếu!',
            'Tác phẩm điện ảnh xuất sắc, vượt xa kỳ vọng ban đầu.',
            'Kịch bản chặt chẽ, âm nhạc nền nâng tầm cảm xúc từng phân cảnh.',
            'Hiệu ứng 3D chân thực, cảm giác như chính mình đang trong phim.',
            'Rất đáng xem cùng người yêu hoặc bạn bè dịp cuối tuần.',
            'Phim truyền tải thông điệp sâu sắc về tình bạn và gia đình.',
        ];

        foreach ($createdMovies as $idx => $m) {
            $user = $customers[$idx % $customers->count()];
            Review::firstOrCreate(
                ['user_id' => $user->id, 'movie_id' => $m->id],
                [
                    'rating' => rand(4, 5),
                    'comment' => $movieComments[$idx % count($movieComments)],
                    'status' => 'ACTIVE',
                ]
            );
        }
        $this->command->info('✓ Đã khởi tạo ' . count($createdMovies) . ' đánh giá phim mẫu.');

        // Cinema Reviews (10+)
        $cinemaComments = [
            'Rạp sạch sẽ, phòng chiếu IMAX màn hình siêu to và âm thanh cực đã!',
            'Nhân viên phục vụ nhanh nhẹn, hướng dẫn nhiệt tình và chu đáo.',
            'Ghế ngồi êm ái, khoảng cách giữa các hàng ghế rộng rãi duỗi chân thoải mái.',
            'Vị trí thuận tiện ngay trung tâm thương mại, bãi giữ xe rộng rãi.',
            'Máy lạnh mát mẻ, nhà vệ sinh sạch sẽ, trải nghiệm xem phim 10/10.',
            'Hệ thống bán vé online nhanh chóng, quét mã QR vào cửa cực tiện.',
            'Không gian sảnh chờ sang trọng, nhiều góc check-in sống ảo đẹp.',
            'Phòng chiếu cách âm tốt, không bị lẫn tiếng ồn từ bên ngoài.',
            'Chất lượng chiếu sắc nét, máy chiếu laser độ sáng rất cao.',
            'Cụm rạp yêu thích nhất của gia đình mình mỗi dịp cuối tuần.',
        ];

        foreach ($createdCinemas as $cIdx => $cinema) {
            $user = $customers[$cIdx % $customers->count()];
            $booking = $createdBookings[$cIdx % count($createdBookings)];
            CinemaReview::firstOrCreate(
                ['user_id' => $user->id, 'cinema_id' => $cinema->id],
                [
                    'booking_id' => $booking->id,
                    'rating' => rand(4, 5),
                    'comment' => $cinemaComments[$cIdx % count($cinemaComments)],
                    'status' => 'ACTIVE',
                ]
            );
        }
        $this->command->info('✓ Đã khởi tạo ' . count($createdCinemas) . ' đánh giá cụm rạp mẫu.');

        // Combo Reviews (10+)
        $comboComments = [
            'Bắp phô mai lắc đậm vị, giòn rụm ăn hết cả xô mà vẫn thèm!',
            'Bắp caramel ngọt vừa phải, thơm nức mũi, nước ngọt lạnh buốt đã khát.',
            'Combo couple giá hợp lý, bắp nhiều 2 người ăn thoải mái không hết.',
            'Combo Marvel có ly đổi màu cực đẹp, chất lượng hoàn thiện cao.',
            'Hotdog phô mai nướng thơm ngon, vỏ bánh mềm xúc xích giòn sựt.',
            'Bắp rang bơ truyền thống thơm bơ chuẩn vị, không bị ỉu.',
            'Trà đào cam sả thanh mát, giải ngấy cực tốt khi xem phim dài.',
            'Phục vụ bắp nước nhanh chóng, không phải xếp hàng chờ lâu.',
            'Đóng gói sạch sẽ, có khay đựng mang vào rạp rất tiện lợi.',
            'Combo gia đình tiết kiệm, cả nhà ai cũng khen ngon.',
        ];

        foreach ($createdCombos as $cbIdx => $combo) {
            $user = $customers[$cbIdx % $customers->count()];
            $booking = $createdBookings[$cbIdx % count($createdBookings)];
            ComboReview::firstOrCreate(
                ['user_id' => $user->id, 'combo_id' => $combo->id],
                [
                    'booking_id' => $booking->id,
                    'rating' => rand(4, 5),
                    'comment' => $comboComments[$cbIdx % count($comboComments)],
                ]
            );
        }
        $this->command->info('✓ Đã khởi tạo ' . count($createdCombos) . ' đánh giá combo bắp nước mẫu.');

        // =====================================================================
        // 9. CHATBOT CONVERSATIONS & MESSAGES (10 HỘI THOẠI HỖ TRỢ KHÁCH HÀNG)
        // =====================================================================
        $chatSamples = [
            ['q' => 'Hôm nay có những phim gì đang chiếu vậy?', 'a' => 'Chào bạn! Hôm nay movieGo đang chiếu các bom tấn hấp dẫn như Deadpool & Wolverine, Inside Out 2, Despicable Me 4, Twisters và Dune 2. Bạn muốn xem tại cụm rạp nào để mình tra cứu lịch chiếu chi tiết nhé!'],
            ['q' => 'Giá vé xem phim phòng chiếu IMAX là bao nhiêu?', 'a' => 'Dạ giá vé phòng chiếu IMAX tại movieGo dao động từ 110.000đ đến 160.000đ tùy theo loại ghế (Regular, VIP, Sweetbox) và thời điểm suất chiếu. Bạn có thể áp dụng thêm mã giảm giá MOVIEGO50K để nhận ưu đãi nhé!'],
            ['q' => 'Có chương trình khuyến mãi nào cho sinh viên không?', 'a' => 'Có bạn nhé! movieGo có chương trình đồng giá 49k cho Học sinh - Sinh viên từ thứ 2 đến Chủ nhật, và mã giảm giá HSSV20 giảm 20% khi đặt vé online!'],
            ['q' => 'Rạp movieGo Sư Vạn Hạnh ở đâu vậy?', 'a' => 'Dạ rạp movieGo Sư Vạn Hạnh tọa lạc tại tầng 6, số 123 Sư Vạn Hạnh, Phường 12, Quận 10, TP.HCM. Rạp mở cửa từ 08:30 đến 23:30 hàng ngày ạ!'],
            ['q' => 'Làm sao để hủy vé đã đặt online?', 'a' => 'Theo quy định của movieGo, vé đã thanh toán thành công có thể được hỗ trợ đổi/hủy trước giờ chiếu tối thiểu 60 phút thông qua hotline chăm sóc khách hàng 1900 6868 hoặc trực tiếp tại quầy vé.'],
            ['q' => 'Bắp nước ở rạp có những vị gì?', 'a' => 'movieGo có 3 vị bắp đặc trưng: Bắp Phô mai béo ngậy, Bắp Caramel ngọt ngào và Bắp Rang Bơ truyền thống. Ngoài ra còn có Combo Solo, Combo Couple và Combo Family cực kỳ tiết kiệm!'],
            ['q' => 'Trẻ em dưới bao nhiêu tuổi được miễn phí vé?', 'a' => 'Trẻ em có chiều cao dưới 0.9m đi cùng người lớn được miễn phí vé xem phim (ngồi cùng ghế với người lớn đi kèm) đối với các phim có dán nhãn P (mọi lứa tuổi).'],
            ['q' => 'Phim Deadpool & Wolverine có dành cho trẻ em không?', 'a' => 'Dạ phim Deadpool & Wolverine được dán nhãn phân loại T18 (dành cho khán giả từ đủ 18 tuổi trở lên). Khi đến rạp bạn vui lòng mang theo CCCD để nhân viên hỗ trợ soát vé nhé!'],
            ['q' => 'Tôi muốn mua vé xem phim Inside Out 2 suất 19:30 tối nay', 'a' => 'Tuyệt vời! Suất chiếu 19:30 phim Inside Out 2 đang còn nhiều ghế VIP và Standard đẹp. Bạn có thể nhấp vào nút "Mua Vé Ngay" trên trang chủ để chọn ghế và thanh toán nhanh chóng.'],
            ['q' => 'Rạp có hỗ trợ thanh toán qua VNPAY hay thẻ ngân hàng không?', 'a' => 'Dạ có ạ! movieGo hỗ trợ thanh toán đa dạng qua VNPAY, Thẻ ATM nội địa, Thẻ Quốc tế Visa/Mastercard và quét mã QR ngân hàng cực kỳ an toàn và tiện lợi!'],
        ];

        foreach ($chatSamples as $chatIdx => $chat) {
            $user = $customers[$chatIdx % $customers->count()];
            $conv = ChatConversation::create([
                'user_id' => $user->id,
                'session_id' => 'sess_' . Str::random(16),
                'started_at' => now()->subHours(rand(1, 24)),
                'last_message_at' => now()->subMinutes(rand(5, 60)),
            ]);

            ChatMessage::create([
                'conversation_id' => $conv->id,
                'role' => 'user',
                'message' => $chat['q'],
                'intent' => 'general_inquiry',
                'tokens' => 15,
            ]);

            ChatMessage::create([
                'conversation_id' => $conv->id,
                'role' => 'assistant',
                'message' => $chat['a'],
                'intent' => 'faq_response',
                'tokens' => 50,
            ]);
        }
        $this->command->info('✓ Đã khởi tạo ' . count($chatSamples) . ' cuộc hội thoại Chatbot AI mẫu.');

        $this->command->info('🎉 HOÀN TẤT SEED TOÀN BỘ DỮ LIỆU MẪU (MỖI CHỨC NĂNG >= 10 DỮ LIỆU) THÀNH CÔNG!');
    }
}
