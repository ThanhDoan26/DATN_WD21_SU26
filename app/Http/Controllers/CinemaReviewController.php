<?php

namespace App\Http\Controllers;

use App\Models\CinemaReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CinemaReviewController extends Controller
{
    public function store(Request $request, $cinema)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'sometimes|string|min:5',
        ]);

        $userId = Auth::id();
        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'Bạn cần đăng nhập để gửi phản hồi.'], 403);
        }

        $cinemaId = is_object($cinema) && isset($cinema->id) ? $cinema->id : (int) $cinema;

        // Allow any authenticated user to submit cinema feedback. (Removed booking-only restriction.)

        $attributes = ['user_id' => $userId, 'cinema_id' => $cinemaId];
        $values = [
            'rating' => (int) $request->input('rating'),
            'comment' => $request->input('comment'),
            'status' => 'HIDDEN',
        ];

        $review = CinemaReview::updateOrCreate($attributes, $values);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'data' => $review]);
        }

        return redirect()->back()->with('success', 'Cảm ơn phản hồi của bạn — nó sẽ được hiển thị sau khi duyệt.');
    }
}
