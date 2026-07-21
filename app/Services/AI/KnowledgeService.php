<?php   
namespace App\Services\AI;
use App\Models\Movie;
use App\Models\Cinema;
use App\Models\Showtime;
use App\Models\Booking;
use App\Models\User;

class KnowledgeService
{
    public function getContext(string $intent, ?User $user): string
    {
        switch ($intent) {
            case 'ask_movies':
                $movies = Movie::whereIn('status', ['Showing', 'Coming Soon'])
                    ->select('title', 'director', 'status', 'duration', 'age_rating')
                    ->get();
                if ($movies->isEmpty()) {
                    return "Hiện tại không có phim nào đang chiếu hoặc sắp chiếu.";
                }
                return "Danh sách phim đang chiếu hoặc sắp chiếu: " . json_encode($movies->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_cinemas':
                $cinemas = Cinema::select('name', 'address', 'phone', 'city')->get();
                return "Danh sách hệ thống rạp phim: " . json_encode($cinemas->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_showtimes':
                $showtimes = Showtime::upcoming()
                    ->with(['movie:id,title', 'room:id,name,cinema_id', 'room.cinema:id,name,address'])
                    ->take(10)
                    ->get();
                if ($showtimes->isEmpty()) {
                    return "Hiện tại không có suất chiếu nào sắp diễn ra.";
                }
                
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
                if (!$user) {
                    return "Bạn chưa đăng nhập. Vui lòng yêu cầu người dùng đăng nhập để xem thông tin vé.";
                }
                $bookings = Booking::where('user_id', $user->id)
                    ->with(['showtime', 'showtime.movie:id,title', 'showtime.room:id,name,cinema_id', 'showtime.room.cinema:id,name'])
                    ->latest()
                    ->take(5)
                    ->get();
                if ($bookings->isEmpty()) {
                    return "Bạn chưa đặt bất kỳ vé nào trong hệ thống.";
                }
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

            case 'general':
            default:
                return "Không có dữ liệu đặc biệt nào cần lấy. Hãy trả lời bình thường.";
        }
    }
}