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

        Stripe::setApiKey(config('services.stripe.secret_key'));

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
        }

        return redirect()->route('checkout.success', [
            'booking_id' => $booking->id,
        ]);
    }

    public function cancel(Request $request)
    {
        $booking = Booking::findOrFail($request->booking_id);

        // Không hủy ngay, để hệ thống tự hết hạn theo thời gian (10 phút)
        // Người dùng có thể quay lại chọn ghế nếu muốn

        return redirect()->route('checkout')
            ->with('info', 'Bạn đã hủy thanh toán. Ghế vẫn được giữ trong 10 phút. Bạn có thể quay lại để tiếp tục.');
    }
}
