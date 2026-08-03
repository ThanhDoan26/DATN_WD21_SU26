<?php   
namespace App\Services\AI;

use App\Models\Movie;
use App\Models\Cinema;
use App\Models\Showtime;
use App\Models\Booking;
use App\Models\User;
use App\Models\Coupon;
use App\Models\Combo;
use App\Models\Post;
use App\Models\Review;

class KnowledgeService
{
    public function getContext(string $intent, ?User $user, string $message = '', array $history = []): string
    {
        switch ($intent) {
            case 'ask_movies':
                $movies = Movie::whereIn('status', ['Showing', 'Coming Soon'])
                    ->with('categories:name')
                    ->get();
                if ($movies->isEmpty()) return "Hiện tại không có phim nào đang chiếu hoặc sắp chiếu.";
                $data = $movies->map(function ($m) {
                    return [
                        'ten_phim' => $m->title,
                        'dao_dien' => $m->director,
                        'the_loai' => $m->categories->pluck('name')->implode(', '),
                        'thoi_luong' => $m->duration . ' phút',
                        'gioi_han_tuoi' => $m->age_rating,
                        'trang_thai' => $m->status
                    ];
                });
                return "Danh sách phim đang chiếu/sắp chiếu: " . json_encode($data->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_movie_information':
            case 'ask_movie_status':
            case 'ask_movie_compare':
                $movies = Movie::whereIn('status', ['Showing', 'Coming Soon'])
                    ->with('categories:name')
                    ->get();
                if ($movies->isEmpty()) return "Không có thông tin phim.";
                $data = $movies->map(function ($m) {
                    return [
                        'ten_phim' => $m->title,
                        'dao_dien' => $m->director,
                        'dien_vien' => $m->cast,
                        'quoc_gia' => $m->country,
                        'ngon_ngu' => $m->language,
                        'the_loai' => $m->categories->pluck('name')->implode(', '),
                        'thoi_luong' => $m->duration . ' phút',
                        'gioi_han_tuoi' => $m->age_rating,
                        'dinh_dang' => $m->format,
                        'mo_ta' => $m->description,
                        'trang_thai' => $m->status,
                        'poster' => $m->poster_url,
                        'trailer' => $m->trailer_url
                    ];
                });
                return "Thông tin chi tiết các phim: " . json_encode($data->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_movie_review':
                $movies = Movie::where('status', 'Showing')
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->get(['id', 'title']);
                return "Đánh giá các phim đang chiếu: " . json_encode($movies->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_movie_recommendation':
                $movies = Movie::whereIn('status', ['Showing', 'Coming Soon'])
                    ->with('categories:name')
                    ->get(['id', 'title', 'age_rating', 'status']);
                $data = $movies->map(function ($m) {
                    return [
                        'ten_phim' => $m->title,
                        'the_loai' => $m->categories->pluck('name')->implode(', '),
                        'gioi_han_tuoi' => $m->age_rating,
                        'trang_thai' => $m->status
                    ];
                });
                return "Gợi ý phim: " . json_encode($data->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_movie_post':
                $posts = Post::where('status', 'published')->latest()->take(3)->get(['title', 'summary']);
                return "Các bài viết/review mới nhất: " . json_encode($posts->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_cinemas':
                $cinemas = Cinema::select('name', 'address', 'phone', 'city')->get();
                return "Danh sách hệ thống rạp phim: " . json_encode($cinemas->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_showtimes':
                $showtimes = Showtime::upcoming()
                    ->with(['movie:id,title', 'room:id,name,cinema_id', 'room.cinema:id,name,address'])
                    ->take(10)
                    ->get();
                if ($showtimes->isEmpty()) return "Hiện tại không có suất chiếu nào sắp diễn ra.";
                $data = $showtimes->map(function ($s) {
                    return [
                        'phim' => $s->movie->title ?? '',
                        'rap' => $s->room->cinema->name ?? '',
                        'dia_chi' => $s->room->cinema->address ?? '',
                        'phong' => $s->room->name ?? '',
                        'thoi_gian_bat_dau' => optional($s->start_time)->format('d/m/Y H:i'),
                        'tinh_trang' => $s->status
                    ];
                });
                return "Lịch chiếu 10 suất chiếu sắp tới: " . json_encode($data->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_my_tickets':
            case 'ask_booking_status':
                if (!$user) return "Bạn chưa đăng nhập. Vui lòng đăng nhập để xem thông tin vé.";
                $bookings = Booking::where('user_id', $user->id)
                    ->with(['showtime', 'showtime.movie:id,title', 'showtime.room:id,name,cinema_id', 'showtime.room.cinema:id,name'])
                    ->latest()
                    ->take(5)
                    ->get();
                if ($bookings->isEmpty()) return "Bạn chưa đặt bất kỳ vé nào trong hệ thống.";
                $data = $bookings->map(function ($b) {
                    $seatsInfo = collect($b->getSeatsInfo())->pluck('code')->implode(', ');
                    return [
                        'ma_ve' => $b->booking_code,
                        'phim' => $b->showtime->movie->title ?? '',
                        'rap' => $b->showtime->room->cinema->name ?? '',
                        'phong' => $b->showtime->room->name ?? '',
                        'ghe_ngoi' => $seatsInfo ?: 'Chưa chọn ghế',
                        'thoi_gian_chieu' => optional($b->showtime->start_time)->format('d/m/Y H:i'),
                        'trang_thai_ve' => $b->status,
                        'tong_tien' => $b->total_price,
                        'thoi_gian_dat_ve' => optional($b->booking_time)->format('d/m/Y H:i')
                    ];
                });
                return "Thông tin 5 vé gần nhất của bạn: " . json_encode($data->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_coupon':
                $coupons = Coupon::where('status', 'ACTIVE')->get(['code', 'value', 'type', 'min_order_value', 'max_discount_amount', 'end_date']);
                return "Mã giảm giá hiện có: " . json_encode($coupons->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_combo':
                $combos = Combo::where('status', 'ACTIVE')->get(['name', 'price', 'description']);
                return "Thông tin combo bắp nước: " . json_encode($combos->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_user_profile':
                if (!$user) return "Người dùng chưa đăng nhập.";
                $profile = [
                    'ten' => $user->name,
                    'email' => $user->email,
                    'so_dien_thoai' => $user->phone,
                    'diem_tich_luy' => $user->loyalty_points
                ];
                return "Thông tin người dùng: " . json_encode($profile, JSON_UNESCAPED_UNICODE);

            case 'ask_booking_history':
                if (!$user) return "Người dùng chưa đăng nhập.";
                $bookings = Booking::where('user_id', $user->id)
                    ->with('showtime.movie:id,title')
                    ->latest()
                    ->take(10)
                    ->get(['id', 'booking_code', 'showtime_id', 'total_price', 'status', 'booking_time']);
                return "Lịch sử 10 lần mua vé gần nhất: " . json_encode($bookings->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_review_history':
                if (!$user) return "Người dùng chưa đăng nhập.";
                $reviews = Review::where('user_id', $user->id)
                    ->with('movie:id,title')
                    ->latest()
                    ->take(5)
                    ->get(['movie_id', 'rating', 'comment', 'created_at']);
                return "Lịch sử đánh giá phim của bạn: " . json_encode($reviews->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_conversation_summary':
                return "Lịch sử cuộc trò chuyện từ trước đến giờ: " . json_encode($history, JSON_UNESCAPED_UNICODE);

            case 'ask_booking_guide':
                return "Hướng dẫn đặt vé: Bước 1: Chọn phim và suất chiếu. Bước 2: Chọn ghế ngồi. Bước 3: Chọn combo (nếu có). Bước 4: Nhập mã giảm giá và thanh toán. Vé của bạn sẽ có trong mục Vé Của Tôi.";

            case 'ask_payment':
                return "Các cổng thanh toán hiện tại hệ thống hỗ trợ: Stripe và MOCK_PAYMENT.";

            case 'ask_payment_error':
                return "Quy định thanh toán lỗi: Nếu bị trừ tiền mà chưa có vé, hệ thống sẽ tự động hoàn tiền hoặc bạn có thể liên hệ tổng đài để được hỗ trợ.";

            case 'ask_ticket_price':
                return "Giá vé: Giá vé được quy định theo từng loại suất chiếu và từng loại ghế (Thường, VIP, Đôi). Vui lòng chọn phim cụ thể để biết giá chi tiết.";

            case 'ask_policy':
                return "Chính sách: Vé đã mua không thể hoàn hoặc đổi. Vui lòng kiểm tra kỹ thông tin trước khi thanh toán.";

            case 'ask_website':
                return "Thông tin website: Bạn có thể đặt vé phim, xem lịch chiếu, đánh giá phim, mua combo bắp nước, xem bài viết giới thiệu phim trên trang web của chúng tôi.";

            // 'ask_seat_hold' bị bỏ qua vì không có Schema/Migration hỗ trợ trong DB.

            case 'general':
            default:
                return "Không có dữ liệu đặc biệt nào cần lấy. Hãy trả lời bình thường.";
        }
    }
}