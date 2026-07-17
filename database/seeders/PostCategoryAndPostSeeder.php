<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
        // 1. Tạo các danh mục mẫu bắt buộc
        $categoriesData = [
            ['name' => 'Tin điện ảnh', 'description' => 'Tin tức nóng hổi về làng điện ảnh trong nước và quốc tế.'],
            ['name' => 'Khuyến mãi', 'description' => 'Chương trình ưu đãi, giảm giá vé, combo bắp nước siêu hấp dẫn.'],
            ['name' => 'Sự kiện', 'description' => 'Các sự kiện công chiếu phim, giao lưu diễn viên, mini-game tại rạp.'],
            ['name' => 'Thông báo', 'description' => 'Các thông báo bảo trì, lịch hoạt động Tết, tuyển dụng từ cụm rạp.'],
            ['name' => 'Phim mới', 'description' => 'Giới thiệu các bom tấn sắp chiếu và đánh giá chi tiết phim mới.'],
            ['name' => 'Hoạt động rạp', 'description' => 'Thông tin nâng cấp phòng chiếu, khai trương chi nhánh mới.'],
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
        ];

        // 4. Download hoặc sinh ảnh
        $images = [];
        foreach ($unsplashImages as $key => $url) {
            $images[$key] = $this->downloadOrGenerateImage($url, $key . '.jpg');
        }

        // 5. Danh sách các bài viết mẫu
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
                </ul>
                <blockquote>Lưu ý: Chương trình không áp dụng đồng thời với các chương trình khuyến mãi khác và không áp dụng vào ngày Lễ, Tết. Hãy mang theo thẻ HSSV của bạn đến rạp gần nhất hoặc đặt vé online thông qua ứng dụng movieGo để nhận ưu đãi ngay hôm nay nhé!</blockquote>',
                'seo_title' => 'Đồng Giá Vé 49K Học Sinh Sinh Viên Tại movieGo',
                'seo_description' => 'Khuyến mãi đặc biệt tại movieGo đồng giá vé 49k cho toàn bộ học sinh sinh viên đặt vé 2D từ 20/07. Xem ngay thể lệ chi tiết tại đây!',
                'seo_keywords' => 've dong gia 49k, khuyen mai moviego, dat ve gia re, hoc sinh sinh vien cgv'
            ],
            [
                'title' => 'Bom Tấn Mới Nhất Của Vũ Trụ Điện Ảnh Marvel Chính Thức Mở Vé Bán Sớm',
                'category_index' => 4, // Phim mới
                'is_featured' => true,
                'image' => $images['thumb_marvel'],
                'banner' => $images['banner_marvel'],
                'summary' => 'Siêu phẩm siêu anh hùng được mong đợi nhất năm 2026 đã chính thức mở cổng bán vé sớm trước 1 tuần. Hãy nhanh tay đặt ngay những chiếc ghế đẹp nhất!',
                'content' => '<p>Vũ trụ Điện ảnh Marvel một lần nữa chuẩn bị làm bùng nổ các phòng vé toàn cầu với phần phim tiếp theo đầy kịch tính. Theo thông báo từ nhà phát hành, bộ phim sẽ chính thức ra rạp vào tuần tới, và hệ thống rạp <strong>movieGo</strong> đã mở bán những suất chiếu đặc biệt sớm nhất từ hôm nay.</p>
                <h3>Tại sao bạn không nên bỏ lỡ suất chiếu đặc biệt?</h3>
                <ol>
                    <li>Trở thành những khán giả đầu tiên khám phá những bí mật động trời của đa vũ trụ.</li>
                    <li>Tránh được nguy cơ bị "spoil" nội dung trên các trang mạng xã hội.</li>
                    <li>Nhận ngay quà tặng là quà lưu niệm Marvel Limited Edition khi mua vé kèm combo bắp nước đặc biệt.</li>
                </ol>
                <p>Phòng chiếu IMAX và phòng chiếu Premium của chúng tôi đã chuẩn bị sẵn sàng để mang tới cho bạn những trải nghiệm âm thanh và hình ảnh choáng ngợp nhất. Đặt vé ngay trên website movieGo!</p>',
                'seo_title' => 'Đặt Vé Sớm Bom Tấn Marvel 2026 Nhận Quà Hot | movieGo',
                'seo_description' => 'movieGo mở bán vé sớm bom tấn siêu anh hùng Marvel mới nhất. Đặt vé ngay hôm nay để có chỗ ngồi VIP đẹp nhất và nhận quà tặng giới hạn.',
                'seo_keywords' => 've som marvel, bom tan marvel 2026, dat ve phim marvel, rap chieu phim moviego'
            ],
            [
                'title' => 'Ra Mắt Phòng Chiếu IMAX Thế Hệ Mới Trải Nghiệm Hoàn Toàn Khác Biệt',
                'category_index' => 5, // Hoạt động rạp
                'is_featured' => true,
                'image' => $images['thumb_imax'],
                'banner' => $images['banner_imax'],
                'summary' => 'movieGo hân hạnh trình làng phòng chiếu công nghệ IMAX tân tiến hàng đầu với màn hình cong cực đại cùng hệ thống âm thanh vòm sống động.',
                'content' => '<p>Với mục tiêu không ngừng nâng cao trải nghiệm điện ảnh cho khách hàng, movieGo chính thức công bố ra mắt phòng chiếu <strong>IMAX Laser</strong> thế hệ mới. Đây là công nghệ chiếu phim hiện đại hàng đầu thế giới hiện nay, mang đến độ sắc nét vượt trội và hệ thống âm thanh rung chuyển từng tế bào.</p>
                <h3>Điểm vượt trội của phòng chiếu IMAX Laser:</h3>
                <ul>
                    <li><strong>Độ sáng đỉnh cao:</strong> Công nghệ trình chiếu bằng Laser cung cấp hình ảnh sáng hơn tới 60% so với máy chiếu thường.</li>
                    <li><strong>Độ tương phản chân thực:</strong> Chi tiết vùng sáng và tối được tách biệt hoàn hảo, mang lại chiều sâu tuyệt đối cho hình ảnh.</li>
                    <li><strong>Màn hình khổng lồ:</strong> Thiết kế màn hình cong phủ kín toàn bộ tầm nhìn của khán giả.</li>
                </ul>
                <p>Hãy đến cụm rạp movieGo Sư Vạn Hạnh cuối tuần này để trải nghiệm cảm giác như chính mình đang sống trong thế giới của những thước phim hành động chân thực nhất.</p>',
                'seo_title' => 'Khai Trương Phòng Chiếu IMAX Laser Siêu Khủng Tại movieGo',
                'seo_description' => 'Trải nghiệm phòng chiếu IMAX Laser thế hệ mới tại cụm rạp movieGo. Màn hình cong cực đại, âm thanh vòm sống động choáng ngợp.',
                'seo_keywords' => 'phong chieu imax, imax laser moviego, rap phim chat luong cao, cong nghe chieu phim'
            ],
            [
                'title' => 'Hé Lộ Hậu Trường Kịch Tính Của Bộ Phim Hành Động Đắt Đỏ Nhất Năm 2026',
                'category_index' => 0, // Tin điện ảnh
                'is_featured' => false,
                'image' => $images['thumb_action'],
                'banner' => $images['banner_action'],
                'summary' => 'Để hoàn thành các cảnh quay rượt đuổi nghẹt thở và cháy nổ hoành tráng, đội ngũ sản xuất đã phải huy động hơn 500 diễn viên quần chúng cùng trang thiết bị tối tân.',
                'content' => '<p>Những hình ảnh hậu trường đầu tiên của bộ phim hành động bom tấn vừa được hé lộ đã khiến cộng đồng yêu điện ảnh đứng ngồi không yên. Với kinh phí sản xuất ước tính lên tới 250 triệu USD, tác phẩm hứa hẹn sẽ mang đến những pha hành động thực chiến mãn nhãn nhất mà không lạm dụng kỹ xảo CGI.</p>
                <p>Đạo diễn chia sẻ: <em>"Chúng tôi muốn người xem cảm nhận được sự nguy hiểm chân thực qua từng mét phim. Mọi vụ nổ, mọi cú va đập xe hơi đều được thực hiện trực tiếp bởi các diễn viên đóng thế chuyên nghiệp."</em></p>
                <p>Phim dự kiến sẽ cập bến hệ thống rạp movieGo vào đầu tháng sau. Lịch chiếu chi tiết sẽ được cập nhật liên tục trên trang chủ của chúng tôi.</p>',
                'seo_title' => 'Hậu Trường Bom Tấn Hành Động 250 Triệu Đô | movieGo',
                'seo_description' => 'Xem ngay hậu trường cảnh quay cháy nổ thực chiến nghẹt thở của siêu phẩm hành động đắt giá nhất năm nay trước ngày công chiếu.',
                'seo_keywords' => 'hau truong phim hanh dong, bom tan 2026, lich chieu phim, tin dien anh hot'
            ],
            [
                'title' => 'Thông Báo Bảo Trì Định Kỳ Hệ Thống Máy Chủ Đặt Vé Trực Tuyến Ngày 20/07',
                'category_index' => 3, // Thông báo
                'is_featured' => false,
                'image' => $images['thumb_notify'],
                'banner' => $images['banner_notify'],
                'summary' => 'Hệ thống website và ứng dụng di động đặt vé của movieGo sẽ tạm dừng hoạt động vào lúc 01:00 đến 04:00 ngày 20/07/2026 để tiến hành nâng cấp định kỳ.',
                'content' => '<p>Kính gửi quý khách hàng của movieGo,</p>
                <p>Để nâng cao chất lượng phục vụ, tăng cường độ bảo mật thông tin và nâng cấp các tính năng đặt vé online nhanh chóng hơn, bộ phận kỹ thuật của movieGo sẽ tiến hành bảo trì hệ thống máy chủ định kỳ theo thời gian cụ thể như sau:</p>
                <blockquote>
                    <strong>Thời gian bảo trì:</strong> Từ 01:00 đến 04:00 ngày thứ Hai, 20/07/2026.<br>
                    <strong>Ảnh hưởng:</strong> Quý khách sẽ không thể thực hiện các giao dịch mua vé, tra cứu lịch chiếu trực tuyến trên Website và App movieGo trong khoảng thời gian này.
                </blockquote>
                <p>Sau 04:00 ngày 20/07, toàn bộ dịch vụ sẽ hoạt động bình thường trở lại. Mọi thắc mắc hoặc cần hỗ trợ khẩn cấp vui lòng liên hệ hotline: 1900 xxxx. Xin chân thành cảm ơn sự thông cảm của quý khách!</p>',
                'seo_title' => 'Thông Báo Bảo Trì Hệ Thống Đặt Vé Online movieGo',
                'seo_description' => 'Lịch bảo trì và nâng cấp máy chủ đặt vé trực tuyến website movieGo ngày 20/07/2026. Quý khách vui lòng lưu ý thời gian gián đoạn.',
                'seo_keywords' => 'bao tri he thong, dat ve moviego, thong bao tu rap, hotline ho tro moviego'
            ],
            [
                'title' => 'movieGo Vinh Dự Nhận Giải Thưởng Cụm Rạp Chiếu Phim Được Yêu Thích Nhất',
                'category_index' => 2, // Sự kiện
                'is_featured' => false,
                'image' => $images['thumb_award'],
                'banner' => $images['banner_award'],
                'summary' => 'Vượt qua nhiều đề cử nặng ký, movieGo đã được bình chọn là thương hiệu rạp chiếu phim có dịch vụ chăm sóc khách hàng và trải nghiệm phòng chiếu tốt nhất năm.',
                'content' => '<p>Tối qua, tại lễ trao giải Doanh nghiệp Dịch vụ Tiêu biểu, movieGo đã xuất sắc nhận cúp vàng danh giá cho hạng mục <strong>"Cụm rạp chiếu phim được yêu thích nhất năm"</strong> do người tiêu dùng bình chọn.</p>
                <p>Đây là thành quả xứng đáng sau một năm nỗ lực không ngừng nghỉ của toàn thể đội ngũ nhân viên rạp, quản lý rạp và admin kỹ thuật trong việc nâng cấp chất lượng âm thanh hình ảnh, đa dạng hóa combo bắp nước và duy trì thái độ phục vụ tận tâm chuyên nghiệp.</p>
                <p>Để tri ân sự đồng hành của quý khán giả, movieGo sẽ triển khai hàng loạt mini-game tặng vé miễn phí và tặng điểm tích lũy thành viên trong suốt tuần này. Hãy cùng đến rạp chúc mừng và nhận quà nhé!</p>',
                'seo_title' => 'movieGo Nhận Giải Cụm Rạp Được Yêu Thích Nhất | Sự Kiện',
                'seo_description' => 'movieGo xuất sắc giành giải thưởng cụm rạp chiếu phim được yêu thích nhất. Đón xem chuỗi sự kiện tri ân khách hàng nhận vé miễn phí.',
                'seo_keywords' => 'giai thuong rap chieu phim, su kien tri an, tang ve mien phi, thanh vien moviego'
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
                    'timeout' => 5, // 5s timeout
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
                ]
            ]);
            $data = @file_get_contents($url, false, $ctx);
            if ($data && strlen($data) > 1000) {
                Storage::disk('public')->put($path, $data);
                return $path;
            }
        } catch (\Exception $e) {
            // Không log lỗi để tránh gián đoạn seeder
        }

        // Tạo ảnh placeholder bằng GD
        if (extension_loaded('gd')) {
            $im = imagecreatetruecolor(800, 450);
            $bg = imagecolorallocate($im, 15, 23, 42); // slate-900
            imagefill($im, 0, 0, $bg);
            
            // Vẽ đường chéo trang trí
            $lineColor = imagecolorallocate($im, 30, 41, 59); // slate-800
            imagesetthickness($im, 3);
            imageline($im, 0, 0, 800, 450, $lineColor);
            imageline($im, 800, 0, 0, 450, $lineColor);

            // Chữ text
            $textColor = imagecolorallocate($im, 229, 9, 20); // primary (#e50914)
            imagestring($im, 5, 280, 215, "movieGo Cinema News Block", $textColor);

            ob_start();
            imagejpeg($im, null, 90);
            $imgData = ob_get_clean();
            imagedestroy($im);

            Storage::disk('public')->put($path, $imgData);
            return $path;
        }

        // Dự phòng cuối
        Storage::disk('public')->put($path, 'dummy binary data');
        return $path;
    }
}
