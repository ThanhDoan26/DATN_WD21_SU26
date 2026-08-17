<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Store a review from a user who has watched the movie.
     */
    public function store(Request $request)
    {
        $request->validate([
            'movie_id' => 'sometimes|exists:movies,id',
            'rating' => 'required|integer|min:1|max:5',
            'body' => 'sometimes|string|min:10',
            'comment' => 'sometimes|string|min:10',
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Bạn cần đăng nhập để đánh giá.'], 403);
        }

        $movieParam = $request->input('movie_id') ?? $request->route('movie');
        if (is_object($movieParam) && isset($movieParam->id)) {
            $movieId = (int) $movieParam->id;
        } else {
            $movieId = (int) $movieParam;
        }

        // Verify user has a booking for this movie and the showtime has passed or booking marked as Used
        $hasWatched = DB::table('bookings')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->where('bookings.user_id', $userId)
            ->where('showtimes.movie_id', $movieId)
            ->where(function ($q) {
                $q->where('bookings.status', 'Used')
                  ->orWhere(function ($q2) {
                      $q2->where('bookings.status', 'Paid')
                         ->where('showtimes.start_time', '<', now());
                  });
            })
            ->exists();

        if (!$hasWatched) {
            return response()->json(['success' => false, 'message' => 'Chỉ có thể đánh giá phim sau khi đã xem.'], 403);
        }

        $body = $request->input('body') ?? $request->input('comment');

        $attributes = [
            'user_id' => $userId,
            'movie_id' => $movieId,
        ];

        $values = [
            'rating' => (int) $request->input('rating'),
            'comment' => $body,
            // When a user creates/edits a review, mark it HIDDEN for moderation
            'status' => 'HIDDEN',
        ];

        $review = Review::updateOrCreate($attributes, $values);

        // If the request expects JSON (AJAX), return the created/updated review payload.
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'data' => $review]);
        }

        // For normal form posts, redirect back to the movie detail so the page reloads
        // and the user's review is visible immediately in the reviews section.
        return redirect()->to(route('movies.show', $movieId) . '#reviews-section')
            ->with('success', 'Cảm ơn! Đánh giá của bạn đã được gửi và sẽ hiển thị ngay.');
    }
}

