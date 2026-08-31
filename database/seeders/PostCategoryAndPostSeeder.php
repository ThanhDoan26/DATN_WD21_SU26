<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\PostCategory;
use App\Models\Post;
use App\Models\User;

class PostCategoryAndPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Tạo ít nhất 10 danh mục bài viết mẫu
        $categoriesData = [
            ['name' => 'Tin điện ảnh', 'description' => 'Tin tức nóng hổi về làng điện ảnh trong nước và quốc tế.'],
            ['name' => 'Khuyến mãi', 'description' => 'Chương trình ưu đãi, giảm giá vé, combo bắp nước siêu hấp dẫn.'],
            ['name' => 'Sự kiện', 'description' => 'Các sự kiện công chiếu phim, giao lưu diễn viên, mini-game tại rạp.'],
            ['name' => 'Thông báo', 'description' => 'Các thông báo bảo trì, lịch hoạt động Tết, tuyển dụng từ cụm rạp.'],
            ['name' => 'Phim mới', 'description' => 'Giới thiệu các bom tấn sắp chiếu và đánh giá chi tiết phim mới.'],
            ['name' => 'Hoạt động rạp', 'description' => 'Thông tin nâng cấp phòng chiếu, khai trương chi nhánh mới.'],
            ['name' => 'Góc hậu trường', 'description' => 'Khám phá bí mật hậu trường, kỹ xảo điện ảnh đằng sau các siêu phẩm.'],
            ['name' => 'Review phim', 'description' => 'Đánh giá chuyên sâu, cảm nhận khách quan về các tác phẩm điện ảnh.'],
            ['name' => 'Công nghệ chiếu phim', 'description' => 'Tìm hiểu công nghệ IMAX Laser, Dolby Atmos, 4DX, ScreenX.'],
            ['name' => 'Bảng xếp hạng phòng vé', 'description' => 'Cập nhật doanh thu phòng vé Việt Nam và thế giới hàng tuần.'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[] = PostCategory::firstOrCreate(
                ['name' => $cat['name']],
                [
                    'slug' => Str::slug($cat['name']),
                    'description' => $cat['description']
                ]
            );
        }

        // 2. Lấy tác giả mặc định (ADMIN)
        $admin = User::whereHas('role', function ($q) {
            $q->where('role_name', 'ADMIN');
        })->first();

        $authorId = $admin ? $admin->id : 1;

        // 3. Khai báo danh sách ảnh mẫu chất lượng cao từ Unsplash
        $unsplashImages = [
            'thumb_promo'   => 'https://images.unsplash.com/photo-1513106580091-1d82408b8cd6?w=800&auto=format&fit=crop&q=80',
            'banner_promo'  => 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?w=1200&auto=format&fit=crop&q=80',
            'thumb_marvel'  => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=800&auto=format&fit=crop&q=80',
            'banner_marvel' => 'https://images.unsplash.com/photo-1478720568477-152d9b164e26?w=1200&auto=format&fit=crop&q=80',
            'thumb_imax'    => 'https://images.unsplash.com/photo-1440404653325-ab127d49abc1?w=800&auto=format&fit=crop&q=80',
            'banner_imax'   => 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=1200&auto=format&fit=crop&q=80',
            'thumb_action'  => 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=800&auto=format&fit=crop&q=80',
            'banner_action' => 'https://images.unsplash.com/photo-1485846234645-a62644f84728?w=1200&auto=format&fit=crop&q=80',
            'thumb_notify'  => 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=800&auto=format&fit=crop&q=80',
            'banner_notify' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=1200&auto=format&fit=crop&q=80',
            'thumb_award'   => 'https://images.unsplash.com/photo-1535498730771-e735b998cd64?w=800&auto=format&fit=crop&q=80',
            'banner_award'  => 'https://images.unsplash.com/photo-1518173946687-a4c8a383392e?w=1200&auto=format&fit=crop&q=80',
            'thumb_review'  => 'https://images.unsplash.com/photo-1478720568477-152d9b164e26?w=800&auto=format&fit=crop&q=80',
            'banner_review' => 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?w=1200&auto=format&fit=crop&q=80',
            'thumb_tech'    => 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=800&auto=format&fit=crop&q=80',
            'banner_tech'   => 'https://images.unsplash.com/photo-1440404653325-ab127d49abc1?w=1200&auto=format&fit=crop&q=80',
        ];

        // 4. Download hoặc sinh ảnh
        $images = [];
        foreach ($unsplashImages as $key => $url) {
            $images[$key] = $this->downloadOrGenerateImage($url, $key . '.jpg');
        }

        // 5. Danh sách ít nhất 12 bài viết mẫu
        $postsData = [
            [
                'title' => 'Đại Tiệc Điện Ảnh: Tuần Lễ Đồng Giá 49K Cho Học Sinh Sinh Viên',
                'category_index' => 1, // Khuyến mãi
                'is_featured' => true,
                'image' => $images['thumb_promo'],
                'banner' => $images['banner_promo'],
                'summary' => 'movieGo tung ưu đãi khủng đồng giá vé chỉ 49,000đ dành riêng cho các mọt phim học sinh, sinh viên trên toàn quốc từ ngày 20/07 đến hết tháng.',
                'content' => '<p>Nhằm đồng hành cùng các bạn học sinh, sinh viên sau những giờ học tập thi cử căng thẳng, hệ thống rạp chiếu phim movieGo chính thức khởi động chương trình ưu đãi lớn nhất mùa hè: <strong>ĐỒNG GIÁ VÉ 49,000 VNĐ</strong>.</p>
                <h3>Chi tiết chương trình:</h3>
                <ul>
                    <li><strong>Đối tượng áp dụng:</strong> Học sinh, sinh viên sở hữu thẻ học sinh/sinh viên còn hiệu lực.</li>
                    <li><strong>Thời gian:</strong> Áp dụng tất cả các ngày trong tuần (bao gồm cả thứ 7 và Chủ Nhật) từ ngày 20/07/2026.</li>
                    <li><strong>Loại ghế áp dụng:</strong> Ghế Standard và Ghế VIP cho tất cả các suất chiếu phim 2D.</li>
                </ul>',
                'seo_title' => 'Đồng Giá Vé 49K Học Sinh Sinh Viên Tại movieGo',
                'seo_description' => 'Khuyến mãi đặc biệt tại movieGo đồng giá vé 49k cho toàn bộ học sinh sinh viên đặt vé 2D từ 20/07.',
                'seo_keywords' => 've dong gia 49k, khuyen mai moviego, dat ve gia re, hoc sinh sinh vien'
            ],
            [
                'title' => 'Bom Tấn Mới Nhất Của Vũ Trụ Điện Ảnh Marvel Chính Thức Mở Vé Bán Sớm',
                'category_index' => 4, // Phim mới
                'is_featured' => true,
                'image' => $images['thumb_marvel'],
                'banner' => $images['banner_marvel'],
                'summary' => 'Siêu phẩm siêu anh hùng được mong đợi nhất năm 2026 đã chính thức mở cổng bán vé sớm trước 1 tuần. Hãy nhanh tay đặt ngay những chiếc ghế đẹp nhất!',
                'content' => '<p>Vũ trụ Điện ảnh Marvel một lần nữa chuẩn bị làm bùng nổ các phòng vé toàn cầu với phần phim tiếp theo đầy kịch tính. Hãy đặt vé ngay trên website movieGo để không bỏ lỡ trải nghiệm IMAX tuyệt đỉnh.</p>',
                'seo_title' => 'Đặt Vé Sớm Bom Tấn Marvel 2026 Nhận Quà Hot | movieGo',
                'seo_description' => 'movieGo mở bán vé sớm bom tấn siêu anh hùng Marvel mới nhất.',
                'seo_keywords' => 've som marvel, bom tan marvel 2026, dat ve phim marvel'
            ],
            [
                'title' => 'Ra Mắt Phòng Chiếu IMAX Thế Hệ Mới Trải Nghiệm Hoàn Toàn Khác Biệt',
                'category_index' => 5, // Hoạt động rạp
                'is_featured' => true,
                'image' => $images['thumb_imax'],
                'banner' => $images['banner_imax'],
                'summary' => 'movieGo hân hạnh trình làng phòng chiếu công nghệ IMAX tân tiến hàng đầu với màn hình cong cực đại cùng hệ thống âm thanh vòm sống động.',
                'content' => '<p>Với mục tiêu không ngừng nâng cao trải nghiệm điện ảnh cho khách hàng, movieGo chính thức công bố ra mắt phòng chiếu IMAX Laser thế hệ mới với hình ảnh rực rỡ và âm thanh bùng nổ.</p>',
                'seo_title' => 'Khai Trương Phòng Chiếu IMAX Laser Siêu Khủng Tại movieGo',
                'seo_description' => 'Trải nghiệm phòng chiếu IMAX Laser thế hệ mới tại cụm rạp movieGo.',
                'seo_keywords' => 'phong chieu imax, imax laser moviego, rap phim chat luong cao'
            ],
            [
                'title' => 'Hé Lộ Hậu Trường Kịch Tính Của Bộ Phim Hành Động Đắt Đỏ Nhất Năm 2026',
                'category_index' => 6, // Góc hậu trường
                'is_featured' => false,
                'image' => $images['thumb_action'],
                'banner' => $images['banner_action'],
                'summary' => 'Để hoàn thành các cảnh quay rượt đuổi nghẹt thở và cháy nổ hoành tráng, đội ngũ sản xuất đã phải huy động hơn 500 diễn viên quần chúng cùng trang thiết bị tối tân.',
                'content' => '<p>Những hình ảnh hậu trường đầu tiên của bộ phim hành động bom tấn vừa được hé lộ với những pha hành động nghẹt thở và cháy nổ thực chiến mãn nhãn.</p>',
                'seo_title' => 'Hậu Trường Bom Tấn Hành Động 250 Triệu Đô | movieGo',
                'seo_description' => 'Xem ngay hậu trường cảnh quay cháy nổ thực chiến nghẹt thở của siêu phẩm hành động.',
                'seo_keywords' => 'hau truong phim hanh dong, bom tan 2026, lich chieu phim'
            ],
            [
                'title' => 'Thông Báo Bảo Trì Định Kỳ Hệ Thống Máy Chủ Đặt Vé Trực Tuyến Ngày 20/07',
                'category_index' => 3, // Thông báo
                'is_featured' => false,
                'image' => $images['thumb_notify'],
                'banner' => $images['banner_notify'],
                'summary' => 'Hệ thống website và ứng dụng di động đặt vé của movieGo sẽ tạm dừng hoạt động vào lúc 01:00 đến 04:00 ngày 20/07/2026 để tiến hành nâng cấp định kỳ.',
                'content' => '<p>Để nâng cao chất lượng phục vụ, tăng cường độ bảo mật thông tin, movieGo bảo trì hệ thống định kỳ từ 01:00 đến 04:00 ngày 20/07/2026.</p>',
                'seo_title' => 'Thông Báo Bảo Trì Hệ Thống Đặt Vé Online movieGo',
                'seo_description' => 'Lịch bảo trì và nâng cấp máy chủ đặt vé trực tuyến website movieGo ngày 20/07/2026.',
                'seo_keywords' => 'bao tri he thong, dat ve moviego, thong bao tu rap'
            ],
            [
                'title' => 'movieGo Vinh Dự Nhận Giải Thưởng Cụm Rạp Chiếu Phim Được Yêu Thích Nhất',
                'category_index' => 2, // Sự kiện
                'is_featured' => false,
                'image' => $images['thumb_award'],
                'banner' => $images['banner_award'],
                'summary' => 'Vượt qua nhiều đề cử nặng ký, movieGo đã được bình chọn là thương hiệu rạp chiếu phim có dịch vụ chăm sóc khách hàng và trải nghiệm phòng chiếu tốt nhất năm.',
                'content' => '<p>Tối qua, tại lễ trao giải Doanh nghiệp Dịch vụ Tiêu biểu, movieGo đã xuất sắc nhận cúp vàng danh giá cho hạng mục Cụm rạp chiếu phim được yêu thích nhất năm.</p>',
                'seo_title' => 'movieGo Nhận Giải Cụm Rạp Được Yêu Thích Nhất | Sự Kiện',
                'seo_description' => 'movieGo xuất sắc giành giải thưởng cụm rạp chiếu phim được yêu thích nhất.',
                'seo_keywords' => 'giai thuong rap chieu phim, su kien tri an, tang ve mien phi'
            ],
            [
                'title' => 'Đánh Giá Chi Tiết Inside Out 2: Chạm Tới Cảm Xúc Tuổi Dậy Thì',
                'category_index' => 7, // Review phim
                'is_featured' => true,
                'image' => $images['thumb_review'],
                'banner' => $images['banner_review'],
                'summary' => 'Inside Out 2 đã mang lại một hành trình trưởng thành đầy sâu lắng và nhân văn qua lăng kính những cảm xúc mới của Riley.',
                'content' => '<p>Inside Out 2 là một kiệt tác hoạt hình tiếp theo của Pixar khi khai thác thành công sự xáo trộn tâm lý ở lứa tuổi dậy thì với sự xuất hiện của Lo Âu, Ghen Tị và Xấu Hổ.</p>',
                'seo_title' => 'Review Phim Inside Out 2 Chi Tiết | movieGo',
                'seo_description' => 'Đánh giá chi tiết bộ phim hoạt hình ăn khách Inside Out 2.',
                'seo_keywords' => 'review inside out 2, danh gia phim hoat hinh, cam xuc riley'
            ],
            [
                'title' => 'Tìm Hiểu Công Nghệ Âm Thanh Vòm Dolby Atmos Trong Rạp Chiếu Phim',
                'category_index' => 8, // Công nghệ chiếu phim
                'is_featured' => false,
                'image' => $images['thumb_tech'],
                'banner' => $images['banner_tech'],
                'summary' => 'Dolby Atmos mang đến âm thanh 3D sống động từ mọi hướng, giúp khán giả đắm chìm hoàn toàn vào từng khung hình của bộ phim.',
                'content' => '<p>Dolby Atmos là công nghệ âm thanh đa chiều hiện đại, giải phóng các luồng âm thanh khỏi các kênh cố định và di chuyển tự do trong không gian 3 chiều.</p>',
                'seo_title' => 'Công Nghệ Âm Thanh Dolby Atmos Tại movieGo',
                'seo_description' => 'Khám phá sự kỳ diệu của công nghệ âm thanh vòm Dolby Atmos.',
                'seo_keywords' => 'dolby atmos, am thanh vom, rap chieu phim cong nghe cao'
            ],
            [
                'title' => 'Bảng Xếp Hạng Doanh Thu Phòng Vé Tuần Qua: Deadpool & Wolverine Độc Chiếm Ngôi Vương',
                'category_index' => 9, // Bảng xếp hạng phòng vé
                'is_featured' => false,
                'image' => $images['thumb_marvel'],
                'banner' => $images['banner_marvel'],
                'summary' => 'Bộ đôi siêu anh hùng Marvel đã lập kỷ lục doanh thu phòng vé tuần đầu công chiếu với hơn 200 tỷ đồng tại thị trường Việt Nam.',
                'content' => '<p>Doanh thu phòng vé tuần qua chứng kiến sự bứt phá mạnh mẽ của Deadpool & Wolverine khi chiếm tới 75% tổng lượng vé bán ra trên toàn quốc.</p>',
                'seo_title' => 'Bảng Xếp Hạng Phòng Vé Tuần Này | movieGo Box Office',
                'seo_description' => 'Cập nhật bảng xếp hạng doanh thu phòng vé phim chiếu rạp mới nhất.',
                'seo_keywords' => 'doanh thu phong ve, bang xep hang phim, box office vietnam'
            ],
            [
                'title' => 'Khai Trương Cụm Rạp movieGo Vincom Hải Phòng Với Hàng Ngàn Quà Tặng',
                'category_index' => 5, // Hoạt động rạp
                'is_featured' => false,
                'image' => $images['thumb_imax'],
                'banner' => $images['banner_imax'],
                'summary' => 'Cụm rạp movieGo chính thức có mặt tại thành phố Cảng Hải Phòng với 6 phòng chiếu hiện đại và chương trình xem phim miễn phí ngày khai trương.',
                'content' => '<p>Ngày 25/08, movieGo Vincom Hải Phòng chính thức mở cửa đón khách với ưu đãi mua 1 vé tặng 1 vé cùng combo bắp nước hấp dẫn.</p>',
                'seo_title' => 'Khai Trương movieGo Vincom Hải Phòng | Ưu Đãi Cực Khủng',
                'seo_description' => 'Mừng khai trương cụm rạp movieGo Vincom Hải Phòng với hàng ngàn ưu đãi.',
                'seo_keywords' => 'moviego hai phong, khai truong rap phim, qua tang mo cua'
            ],
            [
                'title' => 'Top 5 Bộ Phim Kinh Dị Đáng Xem Nhất Mùa Halloween 2026',
                'category_index' => 0, // Tin điện ảnh
                'is_featured' => false,
                'image' => $images['thumb_action'],
                'banner' => $images['banner_action'],
                'summary' => 'Điểm danh những tác phẩm kinh dị rùng rợn và hồi hộp nhất chuẩn bị đổ bộ các rạp chiếu phim trong mùa lễ hội Halloween sắp tới.',
                'content' => '<p>Mùa Halloween năm nay hứa hẹn sẽ mang đến những trải nghiệm thót tim cho các tín đồ mê phim kinh dị với loạt siêu phẩm đáng sợ.</p>',
                'seo_title' => 'Top 5 Phim Kinh Dị Hay Nhất Halloween 2026 | movieGo',
                'seo_description' => 'Danh sách 5 phim kinh dị rùng rợn nhất không thể bỏ qua tại rạp.',
                'seo_keywords' => 'phim kinh di halloween, phim ma hay, top phim kinh di 2026'
            ],
            [
                'title' => 'Ưu Đãi Đặt Vé Nhóm: Đi Càng Đông - Giá Càng Rẻ Cùng movieGo',
                'category_index' => 1, // Khuyến mãi
                'is_featured' => false,
                'image' => $images['thumb_promo'],
                'banner' => $images['banner_promo'],
                'summary' => 'Nhận ngay giảm giá 20% tổng hóa đơn vé và tặng kèm 1 bắp khổng lồ khi đặt vé nhóm từ 4 người trở lên tại tất cả các cụm rạp movieGo.',
                'content' => '<p>Xem phim cùng hội bạn thân nay càng thêm vui với gói ưu đãi vé nhóm Group Deal siêu tiết kiệm áp dụng cho tất cả suất chiếu trong tuần.</p>',
                'seo_title' => 'Ưu Đãi Đặt Vé Nhóm Group Deal Tiết Kiệm | movieGo',
                'seo_description' => 'Chương trình giảm giá đặt vé nhóm hấp dẫn từ movieGo.',
                'seo_keywords' => 've nhom moviego, group deal, giam gia xem phim'
            ],
        ];

        // 6. Insert bài viết mẫu vào DB
        foreach ($postsData as $index => $post) {
            $category = $categories[$post['category_index']];
            Post::firstOrCreate(
                ['title' => $post['title']],
                [
                    'slug' => Str::slug($post['title']),
                    'image' => $post['image'],
                    'banner' => $post['banner'],
                    'summary' => $post['summary'],
                    'content' => $post['content'],
                    'post_category_id' => $category->id,
                    'author_id' => $authorId,
                    'status' => 'Published',
                    'is_featured' => $post['is_featured'],
                    'views' => rand(50, 500),
                    'published_at' => now()->subDays(rand(1, 10))->subHours(rand(1, 12)),
                    'seo_title' => $post['seo_title'],
                    'seo_description' => $post['seo_description'],
                    'seo_keywords' => $post['seo_keywords'],
                ]
            );
        }

        if ($this->command) {
            $this->command->info('✅ Đã tạo ' . count($categories) . ' danh mục tin tức và ' . count($postsData) . ' bài viết mẫu!');
        }
    }

    /**
     * Tải xuống hoặc tạo ảnh giả lập có màu
     */
    private function downloadOrGenerateImage(string $url, string $filename): string
    {
        Storage::disk('public')->makeDirectory('posts');
        $path = 'posts/' . $filename;

        // Thử tải xuống
        try {
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
                ]
            ]);
            $data = @file_get_contents($url, false, $ctx);
            if ($data && strlen($data) > 1000) {
                Storage::disk('public')->put($path, $data);
                return $path;
            }
        } catch (\Exception $e) {
            // Fallback to generator
        }

        // Tạo ảnh placeholder bằng GD
        if (extension_loaded('gd')) {
            $im = imagecreatetruecolor(800, 450);
            $bg = imagecolorallocate($im, 15, 23, 42);
            imagefill($im, 0, 0, $bg);
            
            $lineColor = imagecolorallocate($im, 30, 41, 59);
            imagesetthickness($im, 3);
            imageline($im, 0, 0, 800, 450, $lineColor);
            imageline($im, 800, 0, 0, 450, $lineColor);

            $textColor = imagecolorallocate($im, 229, 9, 20);
            imagestring($im, 5, 280, 215, "movieGo Cinema News Block", $textColor);

            ob_start();
            imagejpeg($im, null, 90);
            $imgData = ob_get_clean();
            imagedestroy($im);

            Storage::disk('public')->put($path, $imgData);
            return $path;
        }

        Storage::disk('public')->put($path, 'dummy binary data');
        return $path;
    }
}
