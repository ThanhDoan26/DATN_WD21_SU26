<?php

namespace App\Services;

use App\Models\Movie;
use App\Models\Showtime;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class MovieSearchService
{
    /**
     * Search movies based on provided filters
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function search(array $filters): LengthAwarePaginator
    {
        Log::info('Movie Search Context:', $filters);

        $query = Movie::query();

        // 1. Lọc theo từ khóa (LIKE, không phân biệt hoa thường)
        $query->when($filters['keyword'] ?? null, function ($q, $keyword) {
            $q->where('title', 'like', '%' . $keyword . '%');
        });

        // 2. Lọc theo trạng thái (Đang chiếu / Sắp chiếu)
        $query->when($filters['status'] ?? null, function ($q, $status) {
            $q->where('status', $status);
        });

        // 3. Lọc theo rạp chiếu
        $query->when($filters['cinema_id'] ?? null, function ($q, $cinemaId) {
            // Chỉ hiển thị phim có lịch chiếu (showtime) tại các phòng (room) thuộc rạp ($cinemaId) này 
            $q->whereHas('showtimes.room', function ($qRoom) use ($cinemaId) {
                $qRoom->where('cinema_id', $cinemaId);
            });
        });

        // 4. Lọc theo thể loại
        $query->when($filters['genre_id'] ?? null, function ($q, $genreId) {
            $q->whereHas('categories', function ($qCategory) use ($genreId) {
                $qCategory->where('categories.id', $genreId);
            });
        });

        // 5. Lọc theo vị trí / thành phố (nếu có chọn hoặc từ session)
        $location = $filters['city'] ?? session('user_location');
        $hasLocation = !empty($location) && strtoupper($location) !== 'ALL';
        if ($hasLocation) {
            $query->whereHas('showtimes.room.cinema', function ($qCinema) use ($location) {
                $qCinema->where('city', 'like', '%' . trim($location) . '%');
            });
        }

        // Tối ưu N+1: eager load categories và showtimes 
        // Đối với showtimes, chỉ lấy các lịch chiếu hợp lệ trong tương lai để hiển thị
        $query->with(['categories', 'showtimes' => function ($q) use ($hasLocation, $location) {
            $q->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
              ->where('start_time', '>=', now());
            if ($hasLocation) {
                $q->whereHas('room.cinema', function ($qCinema) use ($location) {
                    $qCinema->where('city', 'like', '%' . trim($location) . '%');
                });
            }
            $q->orderBy('start_time');
        }]);

        // Trả về phân trang, ưu tiên mới nhất
        $paginator = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        Log::info('Movie Search Result Count: ' . $paginator->total());

        return $paginator;
    }
}
