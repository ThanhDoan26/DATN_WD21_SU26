<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Combo;
use App\Models\Coupon;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\TicketPrice;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketConfirmationMail;

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
        $expiresAtMs = null;
        $showtimeId = $request->query('showtime_id');
        $seatIds = $request->query('seat_ids');

        // Normalize showtimeId - handle array or string
        if (is_array($showtimeId)) {
            $showtimeId = $showtimeId[0] ?? null;
        }
        if ($showtimeId !== null) {
            $showtimeId = (int) $showtimeId;
        }

        // Normalize seatIds - handle array or string
        if (is_array($seatIds)) {
            $seatIds = implode(',', array_filter($seatIds, fn($item) => $item !== null && $item !== ''));
        }

        // Convert string to array of integers
        if ($seatIds && is_string($seatIds)) {
            $seatIds = array_filter(array_map('intval', explode(',', $seatIds)));
        } else {
            $seatIds = [];
        }

        // Only proceed if we have both showtime and seat IDs
        if ($showtimeId && !empty($seatIds)) {
            $showtime = Showtime::with('room.cinema')->find($showtimeId);

            if (!$showtime) {
                abort(404, 'Suất chiếu không tồn tại.');
            }

            // Check if showtime is still valid for booking
            if (!in_array($showtime->status, [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])) {
                abort(404, 'Suất chiếu này không còn khả dụng.');
            }

            if ($showtime->start_time <= now()) {
                abort(404, 'Suất chiếu này đã bắt đầu hoặc kết thúc.');
            }

            // Get ticket prices for this showtime
            $ticketPrices = TicketPrice::where('showtime_id', $showtimeId)
                ->where('status', 'ACTIVE')
                ->get()
                ->keyBy('seat_type');

            // Get selected seats
            $selectedSeats = Seat::whereIn('id', $seatIds)->get();

            // Verify all requested seats were found
            if ($selectedSeats->count() !== count($seatIds)) {
                abort(404, 'Một số ghế không tồn tại.');
            }

            // Build seat summary
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

            // Check if there is an existing pending booking for this user, showtime and these seats
            $pendingBooking = Booking::where('user_id', Auth::id())
                ->where('showtime_id', $showtimeId)
                ->where('status', 'Pending')
                ->whereHas('bookedSeats', function ($q) use ($seatIds) {
                    $q->whereIn('seat_id', $seatIds);
                })
                ->orderBy('booking_time', 'desc')
                ->first();

            if ($pendingBooking) {
                $expiresAtMs = ($pendingBooking->booking_time->timestamp + BookingService::PENDING_PAYMENT_TIMEOUT_MINUTES * 60) * 1000;
            }
        }

        $combos = Combo::where('status', 'ACTIVE')->get();
        $coupons = Coupon::where('status', 'ACTIVE')->get();

        return view('checkout', compact(
            'showtime',
            'selectedSeats',
            'ticketPrices',
            'seatSummary',
            'subtotal',
            'surcharge',
            'total',
            'seatIds',
            'showtimeId',
            'combos',
            'coupons',
            'expiresAtMs'
        ));
    }

    public function reserve(Request $request)
    {
        $request->validate([
            'showtime_id' => 'required|exists:showtimes,id',
            'seat_ids' => 'required',
            'combos' => 'nullable|array',
            'payment_method' => 'nullable|string|max:100',
            'coupon_code' => 'nullable|string|max:50',
        ]);

        $seatIdsInput = $request->input('seat_ids');
        if (is_string($seatIdsInput)) {
            $seatIds = array_filter(array_map('intval', explode(',', $seatIdsInput)));
        } elseif (is_array($seatIdsInput)) {
            $seatIds = array_filter(array_map('intval', $seatIdsInput));
        } else {
            $seatIds = [];
        }

        if (empty($seatIds)) {
            return response()->json(['success' => false, 'message' => 'Vui lòng chọn ít nhất 1 ghế.'], 422);
        }

        // Chặn ghế hỏng hoặc đã đặt (phòng trường hợp hack request)
        $invalidSeats = Seat::whereIn('id', $seatIds)
            ->whereIn('status', [Seat::STATUS_BROKEN, Seat::STATUS_BOOKED])
            ->get();

        if ($invalidSeats->isNotEmpty()) {
            $codes = $invalidSeats->map(fn($s) => $s->getSeatCode())->implode(', ');
            return response()->json([
                'success' => false,
                'message' => 'Các ghế sau không khả dụng: ' . $codes
            ], 422);
        }

        try {
            $bookingService = new BookingService();
            $bookingId = $bookingService->createBooking(
                Auth::id(),
                (int) $request->input('showtime_id'),
                $seatIds,
                $request->input('payment_method', 'ONLINE'),
                $request->input('coupon_code'),
                $request->input('combos', [])
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
        $validation = $coupon->isValid($orderTotal, \Illuminate\Support\Facades\Auth::id());

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

    public function cancel(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
        ]);

        $booking = Booking::where('id', $request->booking_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$booking) {
            return back()->with('error', 'Đơn vé không tồn tại hoặc không thuộc về bạn.');
        }

        if ($booking->status !== 'Pending') {
            return back()->with('error', 'Chỉ có thể hủy đơn vé đang chờ thanh toán.');
        }

        try {
            $bookingService = new BookingService();
            $bookingService->cancelBooking($booking->id, 'Người dùng tự hủy đơn');
            
            return redirect()->route('home')->with('success', 'Đã hủy đơn vé và giải phóng ghế thành công.');
        } catch (\Exception $e) {
            Log::error('Cancel booking failed: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi hủy đơn vé.');
        }
    }

    public function mockPayment(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
        ]);

        $booking = Booking::where('id', $request->booking_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$booking) {
            return back()->with('error', 'Đơn vé không tồn tại hoặc không thuộc về bạn.');
        }

        if ($booking->status !== 'Pending') {
            return back()->with('error', 'Đơn vé này không ở trạng thái chờ thanh toán.');
        }

        try {
            $bookingService = new BookingService();
            
            // Đánh dấu thanh toán thành công
            $bookingService->completePayment($booking->id, $booking->payment_method ?? 'MOCK_PAYMENT');
            
            // Lấy thông tin chi tiết để gửi email
            $bookingDetails = $bookingService->getBookingDetails($booking->id);
            $showtime = Showtime::with(['movie', 'room.cinema'])->find($booking->showtime_id);
            
            // Gửi email xác nhận
            if (Auth::user() && Auth::user()->email) {
                try {
                    \Illuminate\Support\Facades\Log::info("CheckoutController: Đang gọi Mail::to()->send() gửi cho " . Auth::user()->email);
                    Mail::to(Auth::user()->email)->send(new TicketConfirmationMail($bookingDetails, $showtime));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("CheckoutController: Lỗi khi gọi Mail::to()->send() cho " . Auth::user()->email . ". Lỗi: " . $e->getMessage(), [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            } else {
                \Illuminate\Support\Facades\Log::warning("CheckoutController: TicketConfirmationMail KHÔNG được gọi do user chưa đăng nhập hoặc không có email.");
            }
            
            return redirect()->route('checkout.success', ['booking_id' => $booking->id])
                             ->with('success', 'Thanh toán thành công. Email xác nhận đã được gửi đến bạn.');
        } catch (\Exception $e) {
            Log::error('Mock payment failed: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xử lý thanh toán: ' . $e->getMessage());
        }
    }
}
