<?php

namespace App\Services;

use App\Models\Movie;
use App\Models\Showtime;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class MovieDetailService
{
    /**
     * Lấy chi tiết phim cùng các phim liên quan và lịch chiếu theo rạp.
     * 
     * @param int $id
     * @return array
     */
    public function getMovieDetail(int $id): array
    {
        // 1. Fetch movie with categories to avoid N+1
        $movie = Movie::with('categories')->findOrFail($id);

        // 2. Lấy danh sách suất chiếu của phim (chỉ lấy suất SCHEDULED và còn hạn)
        // Group by Cinema then by Date
        $showtimes = Showtime::where('movie_id', $id)
            ->where('status', Showtime::STATUS_SCHEDULED)
            ->where('start_time', '>=', now())
            ->with(['room.cinema'])
            ->orderBy('start_time', 'asc')
            ->get();

        $showtimesByCinema = $showtimes->groupBy(function ($showtime) {
            return $showtime->room->cinema->name ?? 'Khác';
        })->map(function ($cinemaGroup) {
            return $cinemaGroup->groupBy(function ($showtime) {
                return $showtime->start_time->format('Y-m-d');
            });
        });

        // 3. Lấy ra các phim liên quan (cùng category, ưu tiên Now Showing và Coming Soon)
        $categoryIds = $movie->categories->pluck('id');
        $relatedMovies = collect();

        if ($categoryIds->isNotEmpty()) {
            $relatedMovies = Movie::whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            })
            ->where('id', '!=', $id)
            ->whereIn('status', ['NOW_SHOWING', 'COMING_SOON'])
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->with('categories')
            ->get();
        }

        // 4. Load reviews and check review permission
        // By default only show ACTIVE reviews. If the current user has submitted
        // a review (pending approval), include it so the author can see their own review.
        $reviewsQuery = $movie->reviews()->with('user')->orderBy('created_at', 'desc');

        if (auth()->check()) {
            $userId = auth()->id();
            $reviews = $reviewsQuery->where(function($q) use ($userId) {
                $q->where('status', 'ACTIVE')
                  ->orWhere('user_id', $userId);
            })->get();
        } else {
            $reviews = $reviewsQuery->where('status', 'ACTIVE')->get();
        }
        
        $canReview = false;
        $userReview = null;
        $canEditReview = false;
        $purchasedCombos = collect();
        
        if (auth()->check()) {
            $userId = auth()->id();
            
            // Check if user has reviewed
            $userReview = $movie->reviews()->where('user_id', $userId)->first();
            if ($userReview) {
                $canEditReview = $userReview->created_at->addMinutes(5)->isFuture();
            }
            
            // Check if they can review (booking paid/used and showtime ended)
            $canReview = \App\Models\Booking::where('user_id', $userId)
                ->whereIn('status', ['Paid', 'Used'])
                ->whereHas('showtime', function ($query) use ($id) {
                    $query->where('movie_id', $id)
                        ->where('end_time', '<=', now());
                })->exists();

            // Fetch combos purchased during this movie's bookings
            $bookingsWithCombos = \App\Models\Booking::where('user_id', $userId)
                ->whereIn('status', ['Paid', 'Used'])
                ->whereHas('showtime', function ($query) use ($id) {
                    $query->where('movie_id', $id);
                })
                ->with(['combos', 'combos.comboReviews' => function($q) use ($userId) {
                    $q->where('user_id', $userId);
                }])
                ->get();
                
            foreach ($bookingsWithCombos as $booking) {
                foreach ($booking->combos as $combo) {
                    if (!$purchasedCombos->has($combo->id)) {
                        $combo->booking_id_for_review = $booking->id;
                        $purchasedCombos->put($combo->id, $combo);
                    }
                }
            }
        }

        // Fetch all combo reviews for this movie to display alongside movie reviews
        $comboReviews = \App\Models\ComboReview::with('combo')
            ->whereHas('booking.showtime', function($q) use ($id) {
                $q->where('movie_id', $id);
            })
            ->get()
            ->groupBy('user_id');

        // 5. Logging phục vụ debug
        Log::info('Truy cập trang chi tiết phim', [
            'movie_id' => $movie->id,
            'showtime_count' => $showtimes->count(),
            'cinema_count' => $showtimesByCinema->count(),
        ]);

        return [
            'movie' => $movie,
            'showtimesByCinema' => $showtimesByCinema,
            'relatedMovies' => $relatedMovies,
            'reviews' => $reviews,
            'canReview' => $canReview,
            'userReview' => $userReview,
            'canEditReview' => $canEditReview,
            'purchasedCombos' => $purchasedCombos,
            'comboReviews' => $comboReviews,
        ];
    }
}
