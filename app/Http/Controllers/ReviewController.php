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
            'combos' => 'nullable|array',
            'combos.*.booking_id' => 'required|exists:bookings,id',
            'combos.*.rating' => 'required|integer|min:1|max:5',
            'combos.*.comment' => 'nullable|string|max:1000',
        ]);

        $userId = Auth::id();

        // Kiểm tra xem user có được phép review không (đã thanh toán/sử dụng vé và suất chiếu đã kết thúc)
        $canReview = Booking::where('user_id', $userId)
            ->whereIn('status', ['Paid', 'Used'])
            ->whereHas('showtime', function ($query) use ($movie) {
                $query->where('movie_id', $movie->id)
                    ->where('end_time', '<=', now());
            })->exists();

        if (!$canReview) {
            return back()->with('error', 'Bạn chỉ có thể đánh giá sau khi suất chiếu của bạn đã kết thúc.');
        }

        // Cập nhật hoặc tạo mới review (chỉ cho phép chỉnh sửa trong vòng 5 phút sau khi tạo)
        $review = Review::where('user_id', $userId)->where('movie_id', $movie->id)->first();
        if ($review) {
            if ($review->created_at->addMinutes(5)->isPast()) {
                return back()->with('error', 'Đã quá thời gian chỉnh sửa đánh giá này (chỉ được sửa trong vòng 5 phút sau khi đánh giá).');
            }
            $review->update([
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
                'status' => 'ACTIVE' // Reset status to ACTIVE if they update
            ]);
        } else {
            Review::create([
                'user_id' => $userId,
                'movie_id' => $movie->id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
                'status' => 'ACTIVE'
            ]);
        }

        // Cập nhật các đánh giá Combo nếu có
        if (!empty($validated['combos'])) {
            foreach ($validated['combos'] as $comboId => $comboData) {
                \App\Models\ComboReview::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'combo_id' => $comboId,
                    ],
                    [
                        'booking_id' => $comboData['booking_id'],
                        'rating' => $comboData['rating'],
                        'comment' => $comboData['comment'] ?? null,
                    ]
                );
            }
        }

        return back()->with('success', 'Đánh giá cảu bạn đã gửi tành công!');
    }
}
