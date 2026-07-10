<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Combo;
use App\Models\ComboReview;
use Illuminate\Http\Request;

class ComboReviewController extends Controller
{
    /**
     * Danh sách tất cả Combo để xem thống kê đánh giá
     */
    public function index(Request $request)
    {
        // Lấy danh sách Combo kèm theo avg rating và total rating
        $combos = Combo::withCount('comboReviews as total_reviews')
            ->withAvg('comboReviews as average_rating', 'rating')
            ->orderByDesc('total_reviews')
            ->paginate(10);

        return view('admin.combo-reviews.index', compact('combos'));
    }

    /**
     * Xem chi tiết đánh giá của 1 Combo
     */
    public function show(Request $request, Combo $combo)
    {
        $query = $combo->comboReviews()->with('user');

        // Lọc theo rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Lọc theo thời gian
        if ($request->filled('date_from')) {
            $query->whereDate('updated_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('updated_at', '<=', $request->date_to);
        }

        // Tìm kiếm theo tên khách hàng
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $reviews = $query->orderByDesc('updated_at')->paginate(15)->withQueryString();

        $stats = [
            'total' => $combo->comboReviews()->count(),
            'avg' => round($combo->comboReviews()->avg('rating') ?? 0, 1),
            'star5' => $combo->comboReviews()->where('rating', 5)->count(),
            'star4' => $combo->comboReviews()->where('rating', 4)->count(),
            'star3' => $combo->comboReviews()->where('rating', 3)->count(),
            'star2' => $combo->comboReviews()->where('rating', 2)->count(),
            'star1' => $combo->comboReviews()->where('rating', 1)->count(),
        ];

        return view('admin.combo-reviews.show', compact('combo', 'reviews', 'stats'));
    }
}
