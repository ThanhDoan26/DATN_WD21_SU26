<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Review;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $userId = Auth::id();

        // Kiểm tra xem user có được phép review không
        $canReview = Booking::where('user_id', $userId)
            ->whereIn('status', ['Paid', 'Used'])
            ->whereHas('showtime', function ($query) use ($movie) {
                $query->where('movie_id', $movie->id);
            })->exists();

        if (!$canReview) {
            return back()->with('error', 'Bạn chỉ có thể đánh giá sau khi đã mua vé bộ phim này.');
        }

        // Cập nhật hoặc tạo mới review
        Review::updateOrCreate(
            ['user_id' => $userId, 'movie_id' => $movie->id],
            [
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
                'status' => 'ACTIVE' // Reset status to ACTIVE if they update
            ]
        );

        return back()->with('success', 'Đánh giá của bạn đã được gửi thành công!');
    }
}
