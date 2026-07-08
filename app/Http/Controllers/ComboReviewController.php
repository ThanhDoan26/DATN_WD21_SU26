<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ComboReview;
use Illuminate\Http\Request;

class ComboReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'combo_id' => 'required|exists:combos,id',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $booking = Booking::where('id', $request->booking_id)
            ->where('user_id', auth()->id())
            ->with('combos') // Ensure combos relationship is loaded
            ->firstOrFail();

        if ($booking->status !== 'Paid') {
            return back()->with('error', 'Bạn chỉ có thể đánh giá combo khi đơn hàng đã thanh toán thành công.');
        }

        // Verify combo is in this booking
        if (!$booking->combos->contains($request->combo_id)) {
            return back()->with('error', 'Bạn không mua combo này trong đơn hàng hiện tại.');
        }

        // Upsert review: update if exists, otherwise create
        ComboReview::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'combo_id' => $request->combo_id,
            ],
            [
                'booking_id' => $booking->id,
                'rating' => $request->rating,
            ]
        );

        return back()->with('success', 'Cảm ơn bạn đã đánh giá combo!');
    }
}
