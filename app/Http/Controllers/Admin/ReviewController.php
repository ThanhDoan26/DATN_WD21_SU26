<?php

namespace App\Http\Controllers\Admin;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends AdminController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Review::with(['user', 'movie']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('movie', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->all());

        return view('admin.reviews.index', compact('reviews'));
    }

    /**
     * Toggle status (ACTIVE/HIDDEN)
     */
    public function toggleStatus(Review $review)
    {
        $review->status = $review->status === 'ACTIVE' ? 'HIDDEN' : 'ACTIVE';
        $review->save();

        return back()->with('success', 'Trạng thái đánh giá đã được cập nhật.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('success', 'Đánh giá đã được xóa thành công.');
    }
}
