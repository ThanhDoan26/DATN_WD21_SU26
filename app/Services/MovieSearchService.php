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

        // Tối ưu N+1: eager load categories và showtimes 
        // Đối với showtimes, chỉ lấy các lịch chiếu hợp lệ để hiển thị
        $query->with(['categories', 'showtimes' => function ($q) {
            $q->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
              ->orderBy('start_time');
        }]);

        // Trả về phân trang, ưu tiên mới nhất
        $paginator = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        Log::info('Movie Search Result Count: ' . $paginator->total());

        return $paginator;
    }
}
