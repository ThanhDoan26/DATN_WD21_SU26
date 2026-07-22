<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function createSession(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $booking = Booking::findOrFail($request->booking_id);

        // Không tạo lại nếu đã thanh toán
        if ($booking->status == 'Paid') {
            return response()->json([
                'message' => 'Booking này đã được thanh toán.'
            ], 400);
        }

        $secretKey = config('services.stripe.secret_key');
        if (empty($secretKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa cấu hình Stripe Secret Key (STRIPE_SECRET_KEY) trong file .env.'
            ], 500);
        }

        Stripe::setApiKey($secretKey);

        /*
        |--------------------------------------------------------------------------
        | Quy đổi VND -> USD
        |--------------------------------------------------------------------------
        | Stripe US không hỗ trợ VND nên ta quy đổi tạm.
        | Chỉ dùng để TEST.
        */
        $exchangeRate = 26000; // 1 USD ≈ 26.000 VNĐ

        $usd = round($booking->total_price / $exchangeRate, 2);

        $session = Session::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Movie Ticket',
                        'description' => 'Booking: ' . $booking->booking_code,
                    ],
                    // Stripe nhận đơn vị CENT
                    'unit_amount' => (int) round($usd * 100),
                ],
            ]],
            'metadata' => [
                'booking_id' => $booking->id,
            ],
            'success_url' => route('stripe.success', [
                'booking_id' => $booking->id,
            ]),
            'cancel_url' => route('stripe.cancel', [
                'booking_id' => $booking->id,
            ]),
        ]);

        return response()->json([
            'url' => $session->url,
        ]);
    }

    public function success(Request $request)
    {
        $booking = Booking::findOrFail($request->booking_id);

        if ($booking->status != 'Paid') {

            $booking->status = 'Paid';

            $booking->payment_method = 'Stripe';

            $booking->payment_time = now();

            $booking->save();

            // Sync booked_seats status to PAID
            \Illuminate\Support\Facades\DB::table('booked_seats')
                ->where('booking_id', $booking->id)
                ->update([
                    'status' => 'PAID',
                    'updated_at' => now(),
                ]);
        }


        return redirect()->route('checkout.success', [
            'booking_id' => $booking->id,
        ]);
    }

    public function cancel(Request $request)
    {
        $booking = Booking::with('bookedSeats')->findOrFail($request->booking_id);
        $seatIds = $booking->bookedSeats->pluck('seat_id')->implode(',');

        return redirect()->route('checkout', [
            'showtime_id' => $booking->showtime_id,
            'seat_ids' => $seatIds,
        ])->with('info', 'Bạn đã hủy thanh toán. Ghế vẫn sẽ được giữ trong 10 phút. Bạn có thể quay lại để tiếp tục.');
    }
}
