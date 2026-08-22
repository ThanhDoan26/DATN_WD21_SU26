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
    public function init(Request $request)
    {
        $request->validate([
            'showtime_id' => 'required|exists:showtimes,id',
            'seat_ids' => 'required',
        ]);

        $showtimeId = $request->showtime_id;
        $seatIdsInput = $request->seat_ids;
        $userId = \Illuminate\Support\Facades\Auth::id();

        if (is_string($seatIdsInput)) {
            $seatIds = array_filter(array_map('intval', explode(',', $seatIdsInput)));
        } elseif (is_array($seatIdsInput)) {
            $seatIds = array_filter(array_map('intval', $seatIdsInput));
        } else {
            $seatIds = [];
        }

        if (empty($seatIds)) {
            return back()->with('error', 'Vui lòng chọn ghế.');
        }

        // 1. Rate Limit: 3 lần thay đổi / 5 phút
        if ($userId) {
            $rateLimitKey = "rate_limit_user_{$userId}_showtime_{$showtimeId}";
            $blockKey = "block_user_{$userId}_showtime_{$showtimeId}";

            if (\Illuminate\Support\Facades\Cache::has($blockKey)) {
                return back()->with('error', 'Bạn đã thao tác chọn/hủy ghế quá nhiều lần. Vui lòng chờ 5 phút trước khi thử lại.');
            }

            // 2. Seat Cooldown
            foreach ($seatIds as $seatId) {
                if (\Illuminate\Support\Facades\Cache::has("cooldown_user_{$userId}_showtime_{$showtimeId}_seat_{$seatId}")) {
                    return back()->with('error', 'Bạn vừa hủy ghế này gần đây. Vui lòng chọn ghế khác hoặc chờ 3 phút.');
                }
            }
        }

        try {
            // Lấy lại danh sách Combo đã chọn từ đơn giữ ghế cũ của suất chiếu này (nếu có)
            $existingCombos = [];
            if ($userId) {
                $existingPending = Booking::where('user_id', $userId)
                    ->where('showtime_id', $showtimeId)
                    ->whereIn('status', ['Pending', 'PROCESSING'])
                    ->first();

                if ($existingPending) {
                    $existingCombosRaw = \Illuminate\Support\Facades\DB::table('booking_combos')
                        ->where('booking_id', $existingPending->id)
                        ->get();
                    foreach ($existingCombosRaw as $ec) {
                        $existingCombos[$ec->combo_id] = ['qty' => $ec->quantity];
                    }
                }
            }

            $bookingService = new BookingService();
            // This will lock seats and create a new Pending booking with preserved combos
            $bookingId = $bookingService->createBooking(
                $userId,
                $showtimeId,
                $seatIds,
                'ONLINE',
                null,
                $existingCombos
            );

            // Tăng Rate Limit
            if ($userId) {
                $attempts = \Illuminate\Support\Facades\Cache::get($rateLimitKey, 0) + 1;
                \Illuminate\Support\Facades\Cache::put($rateLimitKey, $attempts, now()->addMinutes(5));
                if ($attempts >= 3) {
                    \Illuminate\Support\Facades\Cache::put($blockKey, true, now()->addMinutes(5));
                    \Illuminate\Support\Facades\Cache::forget($rateLimitKey); // clear count
                }
            }

            return redirect()->route('checkout', ['showtime_id' => $showtimeId]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

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

        if (!$showtimeId) {
            abort(404, 'Suất chiếu không hợp lệ.');
        }

        $pendingBooking = Booking::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->where('showtime_id', $showtimeId)
            ->whereIn('status', ['Pending', 'PROCESSING'])
            ->orderBy('booking_time', 'desc')
            ->first();

        if (!$pendingBooking) {
            return redirect()->route('booking.select-seats', ['showtime' => $showtimeId])
                ->with('error', 'Đã hết thời gian giữ ghế hoặc bạn chưa chọn ghế. Vui lòng chọn lại.');
        }

        $pendingBookingId = $pendingBooking->id;
        $expiresAtMs = ($pendingBooking->booking_time->timestamp + BookingService::getHoldDuration() * 60) * 1000;
        
        $seatIds = $pendingBooking->bookedSeats()->pluck('seat_id')->toArray();

        // Only proceed if we have both showtime and seat IDs
        if ($showtimeId && !empty($seatIds)) {
            $showtime = Showtime::with('room.cinema')->find($showtimeId);

            // Check if showtime is still valid for online booking (cut off 15 mins before showtime)
            if (!$showtime->isOnlineBookable()) {
                return redirect()->route('home')->with('error', 'Suất chiếu này đã đóng cổng đặt vé trực tuyến (cần đặt trước giờ chiếu tối thiểu 15 phút). Vui lòng mua vé trực tiếp tại quầy.');
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
        }

        $savedCombos = \Illuminate\Support\Facades\DB::table('booking_combos')
            ->where('booking_id', $pendingBookingId)
            ->pluck('quantity', 'combo_id')
            ->toArray();

        $combos = Combo::where('status', 'ACTIVE')->get();
        $coupons = Coupon::validForCheckout()->get();

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
            'savedCombos',
            'coupons',
            'expiresAtMs',
            'pendingBookingId'
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

        $seatCount = count(array_unique($seatIds));
        $maxSeatsPerBooking = (int) config('booking.seat_hold.max_seats_per_booking', 8);
        if ($seatCount > $maxSeatsPerBooking) {
            return response()->json(['success' => false, 'message' => "Bạn chỉ được đặt tối đa {$maxSeatsPerBooking} ghế cho mỗi đơn hàng."], 422);
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
            $showtimeId = (int) $request->input('showtime_id');
            $showtime = Showtime::find($showtimeId);

            if (!$showtime || !$showtime->isOnlineBookable()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Suất chiếu này đã đóng cổng đặt vé trực tuyến (cần đặt trước giờ chiếu tối thiểu 15 phút). Vui lòng mua vé trực tiếp tại quầy hoặc chọn suất chiếu khác.'
                ], 422);
            }

            $bookingId = $bookingService->createBooking(
                Auth::id(),
                $showtimeId,
                $seatIds,
                $request->input('payment_method', 'ONLINE'),
                $request->input('coupon_code'),
                $request->input('combos', [])
            );

            // Chuyển sang trạng thái PROCESSING để ngăn cronjob dọn dẹp
            Booking::where('id', $bookingId)->update(['status' => 'PROCESSING']);

            $bookingDetails = $bookingService->getBookingDetails($bookingId);
            $holdDurationMs = BookingService::getHoldDuration() * 60 * 1000;
            $expiresAtMs = $bookingDetails['booking_time'] 
                ? (\Carbon\Carbon::parse($bookingDetails['booking_time'])->timestamp * 1000 + $holdDurationMs)
                : (now()->timestamp * 1000 + $holdDurationMs);

            return response()->json([
                'success' => true,
                'message' => 'Đã giữ ghế thành công. Vui lòng thanh toán trong 10 phút.',
                'data' => [
                    'booking_id' => $bookingId,
                    'booking_time' => $bookingDetails['booking_time'],
                    'timeout_minutes' => BookingService::getHoldDuration(),
                    'expires_at_ms' => $expiresAtMs,
                    'booking_code' => $bookingDetails['booking_code'],
                    'total_price' => $bookingDetails['total_price'],
                ],
            ]);
        } catch (\Throwable $e) {
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
            $showtimeId = $booking->showtime_id;
            $bookingService = new BookingService();
            $bookingService->cancelBooking($booking->id, 'Người dùng tự hủy đơn');
            
            return redirect()->route('booking.select-seats', ['showtime' => $showtimeId])
                ->with('success', 'Đã hủy đơn vé và giải phóng ghế thành công. Vui lòng chọn lại ghế.');
        } catch (\Exception $e) {
            Log::error('Cancel booking failed: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi hủy đơn vé.');
        }
    }

    public function releaseLock(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
        ]);

        $booking = Booking::where('id', $request->booking_id)
            ->where('user_id', Auth::id())
            ->where('status', 'Pending')
            ->first();

        if ($booking) {
            try {
                $bookingService = new BookingService();
                $bookingService->cancelBooking($booking->id, 'User actively released lock (beforeunload/back)');
                return response()->json(['success' => true]);
            } catch (\Exception $e) {
                Log::error('Release lock failed: ' . $e->getMessage());
                return response()->json(['success' => false], 500);
            }
        }
        
        return response()->json(['success' => false, 'message' => 'Not found or not pending'], 404);
    }
}
