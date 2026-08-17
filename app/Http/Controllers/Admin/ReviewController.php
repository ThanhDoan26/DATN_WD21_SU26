<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = Review::with(['user', 'movie'])->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.reviews.index', compact('reviews'));
    }

    public function approve($id)
    {
        $review = Review::findOrFail($id);
        $review->status = 'ACTIVE';
        $review->save();

        return redirect()->back()->with('success', 'Đã phê duyệt đánh giá.');
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return redirect()->back()->with('success', 'Đã xóa đánh giá.');
    }

    public function toggleStatus($id)
    {
        $review = Review::findOrFail($id);
        $review->status = ($review->status === 'ACTIVE') ? 'HIDDEN' : 'ACTIVE';
        $review->save();

        return redirect()->back()->with('success', 'Trạng thái đánh giá đã được cập nhật.');
    }
}

