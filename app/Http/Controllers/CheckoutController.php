<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\TicketPrice;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $bookingService = new BookingService();
        $bookingService->cleanupExpiredPendingBookings();

        $showtime = null;
        $selectedSeats = collect();
        $ticketPrices = collect();
        $seatSummary = [];
        $subtotal = 0;
        $surcharge = 0;
        $total = 0;
        $showtimeId = $request->query('showtime_id');
        $seatIds = $request->query('seat_ids');

        if (is_array($showtimeId)) {
            $showtimeId = $showtimeId[0] ?? null;
        }

        if (is_array($seatIds)) {
            $seatIds = implode(',', array_filter($seatIds, fn($item) => $item !== null && $item !== ''));
        }

        if ($showtimeId && $seatIds) {
            $seatIds = array_filter(array_map('intval', explode(',', $seatIds)));
            $showtime = Showtime::with('room.cinema')->find($showtimeId);

            if (!$showtime) {
                abort(404, 'Suất chiếu không tồn tại.');
            }

            $ticketPrices = TicketPrice::where('showtime_id', $showtimeId)
                ->where('status', 'ACTIVE')
                ->get()
                ->keyBy('seat_type');

            $selectedSeats = Seat::whereIn('id', $seatIds)->get();

            foreach ($selectedSeats as $seat) {
                $priceRow = $ticketPrices[$seat->seat_type] ?? null;
                $seatPrice = $priceRow ? (float) $priceRow->price : 0;
                $seatFinalPrice = $seatPrice + (float) $showtime->surcharge;

                $seatSummary[] = [
                    'id' => $seat->id,
                    'code' => $seat->getSeatCode(),
                    'type' => $seat->seat_type,
                    'base_price' => $seatPrice,
                    'surcharge' => (float) $showtime->surcharge,
                    'final_price' => $seatFinalPrice,
                ];

                $subtotal += $seatPrice;
                $total += $seatFinalPrice;
            }

            $surcharge = (float) $showtime->surcharge;
        }

        return view('checkout', compact(
            'showtime',
            'selectedSeats',
            'ticketPrices',
            'seatSummary',
            'subtotal',
            'surcharge',
            'total',
            'seatIds',
            'showtimeId'
        ));
    }

    public function reserve(Request $request)
    {
        $request->validate([
            'showtime_id' => 'required|exists:showtimes,id',
            'seat_ids' => 'required|string',
            'payment_method' => 'nullable|string|max:100',
        ]);

        $seatIds = array_filter(array_map('intval', explode(',', $request->input('seat_ids'))));

        if (empty($seatIds)) {
            return response()->json(['success' => false, 'message' => 'Vui lòng chọn ít nhất 1 ghế.'], 422);
        }

        try {
            $bookingService = new BookingService();
            $bookingId = $bookingService->createBooking(
                Auth::id(),
                (int) $request->input('showtime_id'),
                $seatIds,
                $request->input('payment_method', 'ONLINE')
            );

            $bookingDetails = $bookingService->getBookingDetails($bookingId);

            return response()->json([
                'success' => true,
                'message' => 'Đã giữ ghế thành công. Vui lòng thanh toán trong 10 phút.',
                'data' => [
                    'booking_id' => $bookingId,
                    'booking_time' => $bookingDetails['booking_time'],
                    'timeout_minutes' => BookingService::PENDING_PAYMENT_TIMEOUT_MINUTES,
                    'booking_code' => $bookingDetails['booking_code'],
                    'total_price' => $bookingDetails['total_price'],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Checkout reserve failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * API kiểm tra và áp dụng mã giảm giá
     */
    public function success(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
        ]);

        $booking = Booking::where('id', $request->query('booking_id'))
            ->where('user_id', Auth::id())
            ->first();

        if (!$booking) {
            abort(404, 'Booking không tồn tại hoặc không thuộc về bạn.');
        }

        $bookingService = new BookingService();
        $bookingDetails = $bookingService->getBookingDetails($booking->id);

        return view('checkout-success', [
            'booking' => $bookingDetails,
        ]);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'order_total' => 'required|numeric|min:0'
        ]);

        $code = trim($request->code);
        $orderTotal = floatval($request->order_total);

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá không tồn tại.'
            ], 404);
        }

        // Gọi hàm kiểm tra điều kiện bên trong model Coupon
        $validation = $coupon->isValid($orderTotal);

        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validation['message']
            ], 400);
        }

        // Tính số tiền giảm
        $discountAmount = $coupon->calculateDiscount($orderTotal);

        return response()->json([
            'success' => true,
            'message' => 'Áp dụng mã giảm giá thành công!',
            'data' => [
                'coupon_id' => $coupon->id,
                'code' => $coupon->code,
                'discount_amount' => $discountAmount,
                'final_total' => max(0, $orderTotal - $discountAmount)
            ]
        ]);
    }
}
