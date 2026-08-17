<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CinemaReview;
use Illuminate\Http\Request;

class CinemaReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = CinemaReview::with(['user', 'cinema'])->orderByDesc('created_at');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $reviews = $query->paginate(20)->withQueryString();

        return view('admin.cinema-reviews.index', compact('reviews'));
    }

    public function show($id)
    {
        $review = CinemaReview::with(['user', 'cinema', 'booking'])->findOrFail($id);
        return view('admin.cinema-reviews.show', compact('review'));
    }

    public function toggleStatus($id)
    {
        $review = CinemaReview::findOrFail($id);
        $review->status = ($review->status === 'ACTIVE') ? 'HIDDEN' : 'ACTIVE';
        $review->save();

        return redirect()->back()->with('success', 'Trạng thái phản hồi đã được cập nhật.');
    }

    public function destroy($id)
    {
        $review = CinemaReview::findOrFail($id);
        $review->delete();

        return redirect()->back()->with('success', 'Đã xóa phản hồi.');
    }
}
