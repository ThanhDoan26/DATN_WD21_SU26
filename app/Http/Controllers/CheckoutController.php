<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Combo;
use App\Models\Coupon;
use App\Models\Movie;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\TicketPrice;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

        $showtime = \App\Models\Showtime::with('movie')->find($showtimeId);
        if (!$showtime || !$showtime->isOnlineBookable()) {
            return redirect()->route('home')->with('error', 'Suất chiếu này đã đóng cổng đặt vé trực tuyến. Vui lòng chọn suất chiếu khác.');
        }

        $takenSeatIds = DB::table('booked_seats')
            ->join('bookings', 'booked_seats.booking_id', '=', 'bookings.id')
            ->where('bookings.showtime_id', $showtimeId)
            ->whereIn('booked_seats.seat_id', $seatIds)
            ->where('booked_seats.status', '!=', 'CANCELLED')
            ->where('bookings.status', '!=', 'Cancelled')
            ->where(function ($q) {
                $q->whereNotIn('bookings.status', ['Pending', 'PROCESSING'])
                    ->orWhere('bookings.booking_time', '>=', now()->subMinutes(config('booking.seat_hold.duration_minutes', 10)));
            })
            ->where(function ($q) use ($userId) {
                $q->whereNull('bookings.user_id')
                    ->orWhere('bookings.user_id', '!=', $userId);
            })
            ->distinct()
            ->pluck('booked_seats.seat_id')
            ->toArray();

        if (!empty($takenSeatIds)) {
            $seatCodes = Seat::whereIn('id', $takenSeatIds)
                ->get()
                ->map(fn($seat) => $seat->getSeatCode())
                ->implode(', ');

            return back()->with('error', 'Ghế ' . $seatCodes . ' đã được khách chọn và đã có người đặt/giữ. Vui lòng chọn ghế khác.');
        }

        // 1. Rate Limit: 3 lần thay đổi / 5 phút (Áp dụng cả User và Khách vãng lai qua IP)
        $clientKey = $userId ? "user_{$userId}" : "ip_" . md5($request->ip());
        $rateLimitKey = "rate_limit_{$clientKey}_showtime_{$showtimeId}";
        $blockKey = "block_{$clientKey}_showtime_{$showtimeId}";

        if (\Illuminate\Support\Facades\Cache::has($blockKey)) {
            return back()->with('error', 'Bạn đã thao tác chọn/hủy ghế quá nhiều lần. Vui lòng chờ 5 phút trước khi thử lại.');
        }

        // 2. Seat Cooldown
        foreach ($seatIds as $seatId) {
            if (\Illuminate\Support\Facades\Cache::has("cooldown_{$clientKey}_showtime_{$showtimeId}_seat_{$seatId}")) {
                return back()->with('error', 'Bạn vừa hủy ghế này gần đây. Vui lòng chọn ghế khác hoặc chờ 3 phút.');
            }
        }

        try {
            // Nếu user đang có đơn Pending cho cùng suất chiếu và cùng ghế, không tạo booking mới nữa
            // => tránh case click liên tục "Tiếp tục" vẫn vào được checkout dù đơn đang chờ thanh toán.
            if ($userId) {
                $existingPending = Booking::where('user_id', $userId)
                    ->where('showtime_id', $showtimeId)
                    ->whereIn('status', [\App\Models\Booking::STATUS_PENDING, 'processing'])
                    ->orderBy('booking_time', 'desc')
                    ->first();

                if ($existingPending) {
                    $existingSeatIds = $existingPending->bookedSeats()->pluck('seat_id')->sort()->values()->all();
                    $requestedSeatIds = collect($seatIds)->unique()->sort()->values()->all();

                    if ($existingSeatIds === $requestedSeatIds) {
                        return redirect()->route('checkout', ['showtime_id' => $showtimeId])
                            ->with('info', 'Bạn đang có đơn vé chờ thanh toán cho suất chiếu này. Vui lòng tiếp tục thanh toán.');
                    }
                }
            }

            // Lấy lại danh sách Combo đã chọn từ request (sessionStorage) hoặc từ đơn giữ ghế cũ của suất chiếu này (nếu có)
            $existingCombos = [];

            $combosInput = $request->input('combos');
            if (!empty($combosInput)) {
                if (is_string($combosInput)) {
                    $decoded = json_decode($combosInput, true);
                    if (is_array($decoded)) {
                        $existingCombos = $decoded;
                    }
                } elseif (is_array($combosInput)) {
                    $existingCombos = $combosInput;
                }
            }

            if (empty($existingCombos) && $userId) {
                $existingPending = Booking::where('user_id', $userId)
                    ->where('showtime_id', $showtimeId)
                    ->whereIn('status', [\App\Models\Booking::STATUS_PENDING, 'processing'])
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
            $attempts = \Illuminate\Support\Facades\Cache::get($rateLimitKey, 0) + 1;
            \Illuminate\Support\Facades\Cache::put($rateLimitKey, $attempts, now()->addMinutes(5));
            if ($attempts >= 3) {
                \Illuminate\Support\Facades\Cache::put($blockKey, true, now()->addMinutes(5));
                \Illuminate\Support\Facades\Cache::forget($rateLimitKey); // clear count
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
            ->whereIn('status', [\App\Models\Booking::STATUS_PENDING, 'pending', 'Pending', 'processing', 'PROCESSING'])
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
        $coupons = Coupon::validForCheckout(\Illuminate\Support\Facades\Auth::id(), $pendingBookingId)->orderByAvailabilityAndExpiration()->get();

        $pendingBookingCode = $pendingBooking->booking_code;

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
            'pendingBookingId',
            'pendingBooking',
            'pendingBookingCode'
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

        $showtimeId = (int) $request->input('showtime_id');
        $showtime = Showtime::with('movie')->find($showtimeId);

        if (!$showtime) {
            return response()->json(['success' => false, 'message' => 'Suất chiếu không tồn tại.'], 404);
        }

        // 1. Kiểm tra ghế đã có người khác chọn/đặt chưa (Chống trùng ghế giữa 2 người dùng)
        $takenSeatIds = DB::table('booked_seats')
            ->join('bookings', 'booked_seats.booking_id', '=', 'bookings.id')
            ->where('bookings.showtime_id', $showtimeId)
            ->whereIn('booked_seats.seat_id', $seatIds)
            ->where('booked_seats.status', '!=', 'CANCELLED')
            ->where('bookings.status', '!=', 'Cancelled')
            ->where(function ($q) {
                $q->whereNotIn('bookings.status', ['Pending', 'PROCESSING'])
                    ->orWhere('bookings.booking_time', '>=', now()->subMinutes(config('booking.seat_hold.duration_minutes', 10)));
            })
            ->where(function ($q) {
                $q->whereNull('bookings.user_id')
                    ->orWhere('bookings.user_id', '!=', Auth::id());
            })
            ->distinct()
            ->pluck('booked_seats.seat_id')
            ->toArray();

        if (!empty($takenSeatIds)) {
            $seatCodes = Seat::whereIn('id', $takenSeatIds)->get()->map(fn($seat) => $seat->getSeatCode())->implode(', ');
            return response()->json([
                'success' => false,
                'message' => 'Ghế ' . $seatCodes . ' đã được khách chọn và đã có người đặt/giữ. Vui lòng chọn ghế khác.'
            ], 409);
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

            if (!$showtime->isOnlineBookable()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Suất chiếu này đã đóng cổng đặt vé trực tuyến (cần đặt trước giờ chiếu tối thiểu 15 phút). Vui lòng mua vé trực tiếp tại quầy hoặc chọn suất chiếu khác.'
                ], 422);
            }

            // 2. Chặn ghế hỏng, ghế đã đặt hoặc ghế không thuộc phòng chiếu này (Bảo mật & Tránh hack request)
            $invalidSeats = Seat::whereIn('id', $seatIds)
                ->where(function ($q) use ($showtime) {
                    $q->where('room_id', '!=', $showtime->room_id)
                      ->orWhereIn('status', [Seat::STATUS_BROKEN, Seat::STATUS_BOOKED]);
                })
                ->get();

            if ($invalidSeats->isNotEmpty()) {
                $codes = $invalidSeats->map(fn($s) => $s->getSeatCode())->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => 'Một hoặc nhiều ghế được chọn không thuộc phòng chiếu của suất chiếu này hoặc không khả dụng: ' . $codes
                ], 422);
            }

            $userId = Auth::id();
            $existingBooking = null;

            if ($userId) {
                $existingBooking = Booking::where('user_id', $userId)
                    ->where('showtime_id', $showtimeId)
                    ->whereIn('status', [\App\Models\Booking::STATUS_PENDING, 'pending', 'Pending', 'processing', 'PROCESSING'])
                    ->orderBy('booking_time', 'desc')
                    ->first();
            }

            $existingSeatIds = $existingBooking ? $existingBooking->bookedSeats()->pluck('seat_id')->map(fn($id) => (int)$id)->toArray() : [];
            sort($existingSeatIds);
            $checkSeatIds = array_values($seatIds);
            sort($checkSeatIds);

            $extraData = [
                'booking_source' => 'online',
                'customer_name' => $request->input('customer_name'),
                'customer_phone' => $request->input('customer_phone'),
                'customer_email' => $request->input('customer_email'),
            ];

            if ($existingBooking && $existingSeatIds === $checkSeatIds) {
                // Kiểm tra thời gian giữ ghế còn hiệu lực không
                $holdDuration = BookingService::getHoldDuration();
                $expiresAt = \Carbon\Carbon::parse($existingBooking->booking_time)->addMinutes($holdDuration);
                if (now()->gt($expiresAt)) {
                    throw new \Exception("Thời gian giữ ghế của bạn đã hết. Vui lòng chọn lại ghế.");
                }

                // Cập nhật booking hiện tại (không tạo booking mới để tránh trùng lặp/hủy đơn cũ)
                $updatedBooking = $bookingService->updatePendingBooking(
                    $existingBooking->id,
                    $request->input('payment_method', 'ONLINE'),
                    $request->input('coupon_code'),
                    $request->input('combos', []),
                    $extraData
                );

                $bookingId = $updatedBooking->id;
            } else {
                // Nếu chưa có hoặc ghế đã thay đổi, tạo booking mới
                $bookingId = $bookingService->createBooking(
                    $userId,
                    $showtimeId,
                    $seatIds,
                    $request->input('payment_method', 'ONLINE'),
                    $request->input('coupon_code'),
                    $request->input('combos', []),
                    $extraData
                );
            }

            // Chuyển sang trạng thái processing để ngăn cronjob dọn dẹp
            Booking::where('id', $bookingId)->update(['status' => 'processing']);

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

            $code = ($e instanceof \App\Exceptions\MovieScheduledException || $e->getMessage() === 'Movie is currently scheduled and not yet open for ticket sales.') ? 422 : 400;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $code);
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
        $code = trim($request->input('code') ?? $request->input('coupon_code') ?? '');
        $orderTotal = floatval($request->input('order_total') ?? $request->input('subtotal') ?? 0);

        if (empty($code)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp mã giảm giá.'
            ], 422);
        }

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá không tồn tại.'
            ], 404);
        }

        $bookingId = $request->input('booking_id') ?? $request->input('pending_booking_id');

        // Gọi hàm kiểm tra điều kiện bên trong model Coupon
        $validation = $coupon->isValid($orderTotal, \Illuminate\Support\Facades\Auth::id(), $bookingId);

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
            'discount_amount' => $discountAmount,
            'final_total' => max(0, $orderTotal - $discountAmount),
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

    public function mockPayment(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
        ]);

        $booking = Booking::where('id', $request->booking_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$booking) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Đơn vé không tồn tại hoặc không thuộc về bạn.'], 404);
            }
            return back()->with('error', 'Đơn vé không tồn tại hoặc không thuộc về bạn.');
        }

        if (!in_array($booking->status, ['Pending', 'PROCESSING'])) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Đơn vé này không ở trạng thái chờ thanh toán.'], 400);
            }
            return back()->with('error', 'Đơn vé này không ở trạng thái chờ thanh toán.');
        }

        // Kiểm tra thời gian giữ ghế 10 phút
        $expiresAt = \Carbon\Carbon::parse($booking->booking_time)->addMinutes(BookingService::getHoldDuration());
        if (now()->gt($expiresAt)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Thời gian giữ ghế của bạn đã hết.'], 400);
            }
            return back()->with('error', 'Thời gian giữ ghế của bạn đã hết.');
        }

        try {
            $bookingService = new BookingService();
            
            // Đánh dấu thanh toán thành công (BookingObserver sẽ tự động kích hoạt gửi TicketConfirmationMail bất đồng bộ qua Queue)
            $bookingService->completePayment($booking->id, $booking->payment_method ?? 'MOCK_PAYMENT');
            
            return redirect()->route('booking.history.show', ['bookingCode' => $booking->booking_code])
                             ->with('success', 'Thanh toán thành công. Vé của bạn đã được xuất và email xác nhận sẽ được gửi đến bạn.');
        } catch (\Exception $e) {
            Log::error('Mock payment failed: ' . $e->getMessage());
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra khi xử lý thanh toán: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Có lỗi xảy ra khi xử lý thanh toán: ' . $e->getMessage());
        }
    }

    public function releaseLock(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
        ]);

        $booking = Booking::where('id', $request->booking_id)
            ->where('user_id', Auth::id())
            ->where('status', \App\Models\Booking::STATUS_PENDING)
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
        
        return response()->json(['success' => false, 'message' => 'Đơn đặt vé không tồn tại hoặc không ở trạng thái chờ thanh toán.'], 404);
    }
}
