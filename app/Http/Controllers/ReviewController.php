<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\CinemaReview;
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
                $q->where('bookings.status', 'used')
                  ->orWhere(function ($q2) {
                      $q2->where('bookings.status', 'paid')
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

        // Handle optional cinema feedback early so AJAX responses can include it.
        $cReview = null;
        $cinemaFallbackUsed = false;
        try {
            $hasCinemaFeedback = $request->boolean('cinema_feedback_enabled') || $request->filled('cinema_comment');
            if ($hasCinemaFeedback) {
                // Find a cinema the user actually visited for this movie (by booking)
                $booking = DB::table('bookings')
                    ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
                    ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
                    ->where('bookings.user_id', $userId)
                    ->where('showtimes.movie_id', $movieId)
                    ->where(function ($q) {
                        $q->where('bookings.status', 'used')
                          ->orWhere(function ($q2) {
                              $q2->where('bookings.status', 'paid')
                                 ->where('showtimes.start_time', '<', now());
                          });
                    })
                    ->select('rooms.cinema_id as cinema_id', 'bookings.id as booking_id')
                    ->first();

                if ($booking && $booking->cinema_id) {
                    $cReview = CinemaReview::updateOrCreate([
                        'user_id' => $userId,
                        'cinema_id' => $booking->cinema_id,
                    ], [
                        'booking_id' => $booking->booking_id,
                        'rating' => (int) $request->input('cinema_rating', 5),
                        'comment' => $request->input('cinema_comment'),
                        'status' => 'HIDDEN',
                    ]);
                } else {
                    // Fallback: try to find any cinema that shows this movie (first available)
                    $cinemaRow = DB::table('showtimes')
                        ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
                        ->join('cinemas', 'rooms.cinema_id', '=', 'cinemas.id')
                        ->where('showtimes.movie_id', $movieId)
                        ->select('cinemas.id as cinema_id')
                        ->first();

                    if ($cinemaRow && $cinemaRow->cinema_id) {
                        $cReview = CinemaReview::updateOrCreate([
                            'user_id' => $userId,
                            'cinema_id' => $cinemaRow->cinema_id,
                        ], [
                            'booking_id' => null,
                            'rating' => (int) $request->input('cinema_rating', 5),
                            'comment' => $request->input('cinema_comment'),
                            'status' => 'HIDDEN',
                        ]);
                        $cinemaFallbackUsed = true;
                    }
                }
            }
        } catch (\Exception $e) {
            // swallow errors — review succeeded; admin can handle issues
            $cReview = null;
        }

        // If the request expects JSON (AJAX), return the created/updated review payload.
        if ($request->wantsJson() || $request->ajax()) {
            // Render review HTML partial for immediate insertion on page
            $reviewHtml = view('movies.partials.review_item', compact('review'))->render();

            // If cinema feedback was created, render its partial as well
                $cinemaReviewHtml = null;
            if (isset($booking) && isset($booking->cinema_id)) {
                $cReview = \App\Models\CinemaReview::where('user_id', $userId)->where('cinema_id', $booking->cinema_id)->first();
                if ($cReview) {
                    $cinemaReviewHtml = view('movies.partials.cinema_review_item', ['cReview' => $cReview])->render();
                    $cinemaNameSlug = \Illuminate\Support\Str::slug(optional($cReview->cinema)->name ?? '');
                }
            }

            $cinemaFeedbackCreated = (bool) ($cReview ?? false);
            $cinemaFeedbackMessage = null;
            if (($request->boolean('cinema_feedback_enabled') || $request->filled('cinema_comment')) && !$cinemaFeedbackCreated) {
                $cinemaFeedbackMessage = 'Không tìm thấy booking hợp lệ để gắn phản hồi rạp; phản hồi rạp chưa được lưu.';
            }

            return response()->json([
                'success' => true,
                'review_html' => $reviewHtml,
                'cinema_review_html' => $cinemaReviewHtml,
                'cinema_name_slug' => $cinemaNameSlug ?? null,
                'cinema_feedback_created' => $cinemaFeedbackCreated,
                'cinema_feedback_message' => $cinemaFeedbackMessage,
            ]);
        }

        // For normal form posts, redirect back to the movie detail so the page reloads
        // and the user's review is visible immediately in the reviews section.
        // Also handle optional cinema feedback: if provided, create/update CinemaReview
        try {
            $hasCinemaFeedback = $request->boolean('cinema_feedback_enabled') || $request->filled('cinema_comment');
            if ($hasCinemaFeedback) {
                // Find a cinema the user actually visited for this movie (by booking)
                $booking = DB::table('bookings')
                    ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
                    ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
                    ->where('bookings.user_id', $userId)
                    ->where('showtimes.movie_id', $movieId)
                    ->where(function ($q) {
                        $q->where('bookings.status', 'used')
                          ->orWhere(function ($q2) {
                              $q2->where('bookings.status', 'paid')
                                 ->where('showtimes.start_time', '<', now());
                          });
                    })
                    ->select('rooms.cinema_id as cinema_id', 'bookings.id as booking_id')
                    ->first();

                if ($booking && $booking->cinema_id) {
                    CinemaReview::updateOrCreate([
                        'user_id' => $userId,
                        'cinema_id' => $booking->cinema_id,
                    ], [
                        'booking_id' => $booking->booking_id,
                        'rating' => (int) $request->input('cinema_rating', 5),
                        'comment' => $request->input('cinema_comment'),
                        'status' => 'HIDDEN',
                    ]);
                } else {
                    // Fallback: try to find any cinema that shows this movie (first available)
                    $cinemaRow = DB::table('showtimes')
                        ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
                        ->join('cinemas', 'rooms.cinema_id', '=', 'cinemas.id')
                        ->where('showtimes.movie_id', $movieId)
                        ->select('cinemas.id as cinema_id')
                        ->first();

                    if ($cinemaRow && $cinemaRow->cinema_id) {
                        CinemaReview::updateOrCreate([
                            'user_id' => $userId,
                            'cinema_id' => $cinemaRow->cinema_id,
                        ], [
                            'booking_id' => null,
                            'rating' => (int) $request->input('cinema_rating', 5),
                            'comment' => $request->input('cinema_comment'),
                            'status' => 'HIDDEN',
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            // swallow errors here — the review itself succeeded; admin can handle missing cinema feedback
        }

        return redirect()->to(route('movies.show', $movieId) . '#reviews-section')
            ->with('success', 'Cảm ơn! Đánh giá của bạn đã được gửi và sẽ hiển thị ngay.');
    }
}

