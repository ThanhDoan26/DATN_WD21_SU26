<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;
use App\Services\SeatHoldAbuseService;
use Illuminate\Support\Facades\Cache;

/**
 * ========================================
 * BookingService
 * ========================================
 * Service xử lý logic booking với protection chống race condition
 *
 * 🚨 CRITICAL: Đây là file quan trọng nhất để chống lỗi "2 khách mua 1 ghế"
 *
 * Sử dụng:
 * - DB Transaction
 * - Row-level Locking (SELECT FOR UPDATE)
 * - Retry logic cho Deadlock
 *
 * Được gọi từ controller:
 * $bookingService = new BookingService();
 * $booking = $bookingService->createBooking($userId, $showtimeId, $selectedSeatIds);
 */
class BookingService
{
    /**
     * Giữ tên constant cho backward-compatibility (frontend blade references).
     * Giá trị thực tế lấy từ config('booking.seat_hold.duration_minutes').
     * @see config/booking.php
     */
    public const PENDING_PAYMENT_TIMEOUT_MINUTES = 10;

    /**
     * Lấy thời gian hold từ config — SINGLE SOURCE OF TRUTH.
     * Fallback = PENDING_PAYMENT_TIMEOUT_MINUTES nếu config chưa load.
     */
    public static function getHoldDuration(): int
    {
        return (int) config('booking.seat_hold.duration_minutes', self::PENDING_PAYMENT_TIMEOUT_MINUTES);
    }

    /**
     * Lấy Active Pending Booking của user (Single Source of Truth)
     */
    public function getActivePendingBooking(?int $userId)
    {
        if (!$userId) return null;
        
        return \App\Models\Booking::with(['showtime.movie', 'bookedSeats.seat'])
            ->where('user_id', $userId)
            ->whereIn('status', ['Pending', 'PROCESSING'])
            ->where('booking_time', '>=', now()->subMinutes(self::getHoldDuration()))
            ->first();
    }

    /**
     * Tạo booking với protection chống race condition
     *
     * @param int $userId User ID (nullable cho guest)
     * @param int $showtimeId Suất chiếu
     * @param array $selectedSeatIds Danh sách seat ID được chọn
     * @param string|null $paymentMethod Phương thức thanh toán
     * @return int Booking ID
     * @throws Exception
     */
    public function createBooking(
        ?int $userId,
        int $showtimeId,
        array $selectedSeatIds,
        ?string $paymentMethod = null,
        ?string $couponCode = null,
        array $combos = [],
        array $extraData = []
    ): int {
        // Apply user lock to prevent concurrent booking attempts
        $userLock = $userId ? Cache::lock("user_booking_lock_{$userId}", 10) : null;
        if ($userLock && !$userLock->get()) {
            throw new Exception("Hệ thống đang xử lý giao dịch của bạn. Vui lòng thử lại sau.");
        }

        try {
            if (empty($selectedSeatIds)) {
                throw new Exception('Vui lòng chọn ít nhất 1 ghế');
            }

            // Anti-abuse: Validate max seats per booking (config-driven)
            $selectedSeatCount = count(array_unique(array_values($selectedSeatIds)));
            $maxSeatsPerBooking = (int) config('booking.seat_hold.max_seats_per_booking', 8);
            if ($selectedSeatCount > $maxSeatsPerBooking) {
                throw new Exception("Bạn chỉ có thể chọn tối đa {$maxSeatsPerBooking} ghế cho mỗi đơn hàng.");
            }

            $this->cleanupExpiredPendingBookings();

            $inheritedBookingTime = null;
            $isExtended = false;

            try {
                // 1. Tự động dọn dẹp các booking quá hạn trước khi kiểm tra
                // 2. ACTIVE PENDING BOOKING GUARD (SSOT)
                if ($userId) {
                    $activePendingBooking = $this->getActivePendingBooking($userId);
                    
                    if ($activePendingBooking && $activePendingBooking->showtime_id != $showtimeId) {
                        throw new Exception("Bạn đang có một đơn đặt vé chưa hoàn tất. Vui lòng hoàn thành hoặc hủy đơn hiện tại trước khi đặt vé mới.");
                    }
                }

                // 3. Hủy các booking Pending cũ của chính user này đối với suất chiếu này để giải phóng ghế
                if ($userId) {
                $userPendingBookings = DB::table('bookings')
                    ->where('user_id', $userId)
                    ->where('showtime_id', $showtimeId)
                    ->whereIn('status', ['Pending', 'PROCESSING'])
                    ->select('id', 'booking_time', 'is_extended')
                    ->get();
                
                $userPendingBookingIds = $userPendingBookings->pluck('id')->toArray();

                if (!empty($userPendingBookingIds)) {
                    $oldestBooking = $userPendingBookings->sortBy('booking_time')->first();
                    if ($oldestBooking) {
                        $inheritedBookingTime = \Carbon\Carbon::parse($oldestBooking->booking_time);
                        $isExtended = (bool) $oldestBooking->is_extended;
                    }

                    // Hoàn lại lượt dùng mã giảm giá nếu có
                    $bookingsWithCoupons = DB::table('bookings')
                        ->whereIn('id', $userPendingBookingIds)
                        ->whereNotNull('coupon_id')
                        ->get();

                    foreach ($bookingsWithCoupons as $b) {
                        DB::table('coupons')
                            ->where('id', $b->coupon_id)
                            ->where('used_count', '>', 0)
                            ->decrement('used_count');
                    }

                    // Hủy booking cũ trước
                    DB::table('bookings')
                        ->whereIn('id', $userPendingBookingIds)
                        ->update([
                            'status' => 'Cancelled',
                            'cancellation_reason' => 'User initiated a new booking request',
                            'cancelled_at' => now(),
                            'updated_at' => now(),
                        ]);

                    $oldSeatIds = DB::table('booked_seats')
                        ->whereIn('booking_id', $userPendingBookingIds)
                        ->pluck('seat_id')
                        ->toArray();

                    DB::table('booked_seats')
                        ->whereIn('booking_id', $userPendingBookingIds)
                        ->update([
                            'status' => 'CANCELLED',
                            'updated_at' => now(),
                        ]);

                    // Release Redis locks & broadcast AVAILABLE for seats no longer selected
                    $releasedSeatIds = array_diff($oldSeatIds, $selectedSeatIds);
                    foreach ($releasedSeatIds as $oldSeatId) {
                        try {
                            \Illuminate\Support\Facades\Redis::del("seat_lock:showtime_{$showtimeId}:seat_{$oldSeatId}");
                        } catch (\Throwable $t) {}
                        if ($userId) {
                            \Illuminate\Support\Facades\Cache::put(
                                "cooldown_user_{$userId}_showtime_{$showtimeId}_seat_{$oldSeatId}",
                                true,
                                now()->addMinutes(3)
                            );
                        }
                    }
                    if (!empty($releasedSeatIds)) {
                        try {
                            event(new \App\Events\SeatStatusUpdated($showtimeId, array_values($releasedSeatIds), 'AVAILABLE'));
                        } catch (\Throwable $t) {}
                    }

                    // ── Anti-Abuse: Mark released cho tracking ──
                    try {
                        $abuseServiceCleanup = new SeatHoldAbuseService();
                        foreach ($userPendingBookingIds as $pendingId) {
                            // User chủ động sửa giỏ hàng -> nhả ghế tự nguyện -> RELEASED
                            $abuseServiceCleanup->markReleased($pendingId);
                        }
                    } catch (\Throwable $trackingEx) {
                        \Illuminate\Support\Facades\Log::warning("Failed to record seat release for abuse tracking: " . $trackingEx->getMessage());
                    }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Pre-booking cleanup failed: ' . $e->getMessage());
        }

        if ($inheritedBookingTime) {
            $holdDuration = self::getHoldDuration();
            $expiresAt = $inheritedBookingTime->copy()->addMinutes($holdDuration);
            
            // Silent Buffer: Nếu thời gian còn lại < 2 phút và chưa từng gia hạn
            if (now()->addMinutes(2)->gte($expiresAt) && !$isExtended) {
                $inheritedBookingTime->addMinutes(3);
                $isExtended = true;
                $expiresAt->addMinutes(3); // Update local var just in case
            } elseif (now()->gte($expiresAt)) {
                throw new Exception("Thời gian giữ ghế của bạn đã hết. Vui lòng tải lại trang và chọn lại ghế mới.");
            }
        }

        // 3. Thực hiện validate sau khi đã giải phóng các ghế hết hạn và ghế cũ của chính user
        $seatValidationService = new SeatSelectionValidationService();
        $seatValidationService->validateSelectedSeats($showtimeId, $selectedSeatIds);

        // ── Bước 1: Xin khóa Redis (Anti Race Condition) ──────────────
        $locks = [];
        $sortedSeatIds = $selectedSeatIds;
        sort($sortedSeatIds); // Đảm bảo thứ tự xin khóa đồng nhất, tránh Deadlock

        try {
            // Cố gắng lấy khóa từng ghế (10 phút) trên Redis (nếu Redis khả dụng)
            foreach ($sortedSeatIds as $seatId) {
                $lockKey = "seat_lock:showtime_{$showtimeId}:seat_{$seatId}";
                try {
                    $locked = \Illuminate\Support\Facades\Redis::set(
                        $lockKey, 
                        json_encode(['user_id' => $userId, 'status' => 'Pending']), 
                        'EX', 
                        600, 
                        'NX'
                    );
                    
                    if (!$locked) {
                        // Release previously acquired locks in this batch
                        foreach ($locks as $acquiredKey) {
                            try { \Illuminate\Support\Facades\Redis::del($acquiredKey); } catch (\Throwable $t) {}
                        }
                        throw new Exception("Ghế bạn chọn đang có người khác thao tác. Vui lòng thử lại!");
                    }
                    $locks[] = $lockKey;
                } catch (\Throwable $ex) {
                    if ($ex->getMessage() === "Ghế bạn chọn đang có người khác thao tác. Vui lòng thử lại!") {
                        throw new \Exception($ex->getMessage());
                    }
                    \Illuminate\Support\Facades\Log::warning("Redis connection failed during seat lock, falling back to DB: " . $ex->getMessage());
                    break;
                }
            }

            // ── Bước 2: Kiểm tra Chống Spam (Anti-abuse) ──────────────
            // Chạy TRONG block try (sau khi đã có khóa Redis) nhưng NGOÀI transaction để tránh giữ DB lock quá lâu
            $bookingSource = $extraData['booking_source'] ?? 'online';
            $isStaffBooking = ($bookingSource !== 'online');
            
            if ($userId && !$isStaffBooking) {
                $abuseService = new SeatHoldAbuseService();
                $maxActiveSeats = (int) config('booking.seat_hold.max_active_seats_per_user', 8);
                $currentHeldSeats = $abuseService->countActiveHeldSeats($userId, $showtimeId);
                $newSeatCount = count($selectedSeatIds);

                if (($currentHeldSeats + $newSeatCount) > $maxActiveSeats) {
                    $remaining = max(0, $maxActiveSeats - $currentHeldSeats);
                    throw new Exception(
                        "Bạn đang giữ {$currentHeldSeats} ghế cho suất chiếu này. Chỉ có thể giữ thêm tối đa {$remaining} ghế nữa."
                    );
                }

                // Kiểm tra Cooldown 15 phút (chống giam ghế)
                // Chỉ chặn các hành vi THỰC SỰ lạm dụng (admin hủy, hệ thống phát hiện abuse).
                // Các lý do hủy bình thường PHẢI được loại trừ:
                // - 'User initiated a new booking request': User chọn lại ghế (update giỏ hàng)
                // - 'Payment timeout expired': Booking hết hạn tự nhiên, user quay lại chọn lại
                // - 'User cancelled explicitly': User chủ động hủy rồi muốn đặt lại
                $cooldownMinutes = 15;
                $recentAbusedSeats = DB::table('bookings')
                    ->join('booked_seats', 'bookings.id', '=', 'booked_seats.booking_id')
                    ->where('bookings.user_id', $userId)
                    ->where('bookings.showtime_id', $showtimeId)
                    ->where('bookings.status', 'Cancelled')
                    ->whereNotIn('bookings.cancellation_reason', [
                        'User initiated a new booking request',
                        'Replaced by a new booking request',
                        'Payment timeout expired',
                        'User cancelled explicitly',
                        'Người dùng tự hủy đơn',
                        'User actively released lock (beforeunload/back)',
                    ])
                    ->where('bookings.created_at', '>=', now()->subMinutes($cooldownMinutes))
                    ->whereIn('booked_seats.seat_id', $selectedSeatIds)
                    ->select('booked_seats.seat_id')
                    ->get();

            }

            // ── Bước 3: Cập nhật giữ ghế (Thực thi an toàn) ──────────────
            $bookingId = DB::transaction(function () use ($userId, $showtimeId, $selectedSeatIds, $paymentMethod, $couponCode, $combos, $extraData, $inheritedBookingTime, $isExtended) {

                // ================================================================
                // Step 1: Lock các hàng ghế (chỉ 1 request được giữ lock)
                // ================================================================
                // 🔒 SELECT FOR UPDATE - lock các hàng trong booked_seats
                // Các request khác phải đợi cho đến khi transaction này commit/rollback

                $lockedBookedSeats = DB::table('booked_seats')
                    ->join('bookings', 'booked_seats.booking_id', '=', 'bookings.id')
                    ->where('bookings.showtime_id', $showtimeId)
                    ->whereIn('booked_seats.seat_id', $selectedSeatIds)
                    // Chỉ lock ghế chưa hủy và chưa hết hạn
                    ->where('bookings.status', '!=', 'Cancelled')
                    ->where(function ($q) {
                        $q->whereNotIn('bookings.status', ['Pending', 'PROCESSING'])
                          ->orWhere('bookings.booking_time', '>=', now()->subMinutes(self::getHoldDuration()));
                    })
                    ->lockForUpdate() // 🔒 CRITICAL: SELECT ... FOR UPDATE
                    ->select('booked_seats.seat_id', 'booked_seats.status')
                    ->get();

                // ================================================================
                // Step 2: Kiểm tra xem ghế đã bị đặt hay chưa
                // ================================================================
                if ($lockedBookedSeats->count() > 0) {
                    // Lấy danh sách ghế đã đặt dưới dạng Seat Code (ví dụ: A5, B6)
                    $bookedSeatIds = $lockedBookedSeats->pluck('seat_id')->toArray();
                    $bookedSeatsInfo = DB::table('seats')
                        ->whereIn('id', $bookedSeatIds)
                        ->select('row_name', 'seat_number')
                        ->get();

                    $bookedSeatCodes = [];
                    foreach ($bookedSeatsInfo as $seat) {
                        $bookedSeatCodes[] = $seat->row_name . $seat->seat_number;
                    }

                    throw new Exception(
                        'Một hoặc nhiều ghế đã được đặt bởi khách khác: ' .
                        implode(', ', $bookedSeatCodes) .
                        '. Vui lòng chọn ghế khác!'
                    );
                }


                // ================================================================
                // Step 3: Lấy thông tin ghế + tính giá vé
                // ================================================================
                $selectedSeats = DB::table('seats')
                    ->whereIn('id', $selectedSeatIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($selectedSeats->count() !== count($selectedSeatIds)) {
                    throw new Exception('Một hoặc nhiều ghế không tồn tại');
                }

                // ================================================================
                // Step 4: Lấy thông tin suất chiếu và giá vé từ ticket_prices
                // ================================================================
                $showtime = DB::table('showtimes')
                    ->where('id', $showtimeId)
                    ->lockForUpdate()
                    ->first();

                if (!$showtime) {
                    throw new Exception("Suất chiếu $showtimeId không tồn tại");
                }

                $startTime = \Carbon\Carbon::parse($showtime->start_time);
                $endTime = $showtime->end_time ? \Carbon\Carbon::parse($showtime->end_time) : null;
                $isWalkIn = ($extraData['booking_source'] ?? 'online') !== 'online';

                // Kiểm tra trạng thái và thời gian đặt vé theo quy định
                if ($showtime->status === 'CANCELLED') {
                    throw new Exception("Suất chiếu này đã bị hủy, không thể đặt vé.");
                }

                if ($isWalkIn) {
                    // Tại quầy: Cho phép trong 30 phút đầu kể từ khi bắt đầu chiếu (và chưa kết thúc)
                    if ($endTime && now()->gte($endTime)) {
                        throw new Exception("Suất chiếu này đã kết thúc, không thể xuất vé.");
                    }
                    if (now()->gt($startTime->copy()->addMinutes(30))) {
                        throw new Exception("Suất chiếu đã bắt đầu quá 30 phút, hệ thống đã khóa bán vé.");
                    }
                } else {
                    // Trực tuyến (Online): Khóa trước giờ chiếu 15 phút
                    if ($showtime->status !== 'SCHEDULED') {
                        throw new Exception("Suất chiếu này không còn mở bán trực tuyến.");
                    }
                    if (now()->addMinutes(15)->gte($startTime)) {
                        throw new Exception("Suất chiếu này đã đóng cổng đặt vé trực tuyến (cần đặt trước giờ chiếu tối thiểu 15 phút). Vui lòng mua vé trực tiếp tại quầy hoặc chọn suất chiếu khác.");
                    }
                }

                $ticketPrices = DB::table('ticket_prices')
                    ->where('showtime_id', $showtimeId)
                    ->where('status', 'ACTIVE')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('seat_type');

                $surcharge = isset($showtime->surcharge) ? (float) $showtime->surcharge : 0;

                // ================================================================
                // Step 5: Tính tổng giá
                // ================================================================
                $totalPrice = 0;
                $seatDetails = [];

                foreach ($selectedSeatIds as $seatId) {
                    $seat = $selectedSeats[$seatId] ?? null;
                    if (!$seat) {
                        throw new Exception("Ghế $seatId không tồn tại");
                    }

                    $priceRow = $ticketPrices[$seat->seat_type] ?? null;
                    
                    if (!$priceRow) {
                        // BIZ-002: Tự động bù giá gốc nếu suất chiếu cũ chưa có giá cho loại ghế mới
                        $defaultPrices = [
                            'Regular' => 75000,
                            'VIP' => 90000,
                            'Sweetbox' => 120000,
                        ];
                        
                        if (isset($defaultPrices[$seat->seat_type])) {
                            $price = $defaultPrices[$seat->seat_type];
                            // Tự động insert để cache lại cho các lần booking sau
                            DB::table('ticket_prices')->insert([
                                'showtime_id' => $showtimeId,
                                'seat_type' => $seat->seat_type,
                                'price' => $price,
                                'status' => 'ACTIVE',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        } else {
                            throw new Exception(
                                "Không có giá vé cho loại ghế {$seat->seat_type} trong suất chiếu này. Vui lòng báo quản trị viên cập nhật giá."
                            );
                        }
                    } else {
                        $price = (float) $priceRow->price;
                    }

                    $finalPrice = $price + $surcharge;
                    $totalPrice += $finalPrice;

                    $seatDetails[] = [
                        'seat_id' => $seatId,
                        'seat_row' => $seat->row_name,
                        'seat_number' => $seat->seat_number,
                        'price_at_booking' => $finalPrice,
                    ];
                }

                $comboDetails = [];
                if (!empty($combos)) {
                    $comboIds = array_keys($combos);
                    $dbCombos = DB::table('combos')->whereIn('id', $comboIds)->get()->keyBy('id');
                    foreach ($combos as $comboId => $comboData) {
                        $qty = (int) ($comboData['qty'] ?? 0);
                        if ($qty > 0) {
                            if (!isset($dbCombos[$comboId])) {
                                throw new Exception("Combo không tồn tại");
                            }
                            $comboPrice = (float) $dbCombos[$comboId]->price;
                            $totalPrice += ($comboPrice * $qty);
                            $comboDetails[] = [
                                'combo_id' => $comboId,
                                'quantity' => $qty,
                                'price' => $comboPrice,
                            ];
                        }
                    }
                }

                // ================================================================
                // Step 5.1: Xử lý Mã giảm giá (nếu có)
                // ================================================================
                $couponId = null;
                $discountAmount = 0;

                if (!empty($couponCode)) {
                    $coupon = \App\Models\Coupon::where('code', $couponCode)->lockForUpdate()->first();
                    if (!$coupon) {
                        throw new Exception("Mã giảm giá không hợp lệ hoặc không tồn tại.");
                    }

                    $validation = $coupon->isValid($totalPrice, $userId);
                    if (!$validation['valid']) {
                        throw new Exception($validation['message']);
                    }

                    $discountAmount = $coupon->calculateDiscount($totalPrice);
                    $couponId = $coupon->id;

                    // Tăng lượt sử dụng
                    $coupon->increment('used_count');
                }

                $finalTotalPrice = max(0, $totalPrice - $discountAmount);

                // ================================================================
                // Step 6: Tạo Booking record
                // ================================================================
                $bookingCode = 'BK' . uniqid() . date('Ymd');

                $bookingId = DB::table('bookings')->insertGetId([
                    'user_id' => $userId,
                    'showtime_id' => $showtimeId,
                    'total_price' => $finalTotalPrice,
                    'coupon_id' => $couponId,
                    'discount_amount' => $discountAmount,
                    'status' => 'Pending',
                    'payment_method' => $paymentMethod,
                    'booking_time' => $inheritedBookingTime ?? now(),
                    'is_extended' => $isExtended,
                    'booking_code' => $bookingCode,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'booking_source' => $extraData['booking_source'] ?? 'online',
                    'customer_name' => $extraData['customer_name'] ?? null,
                    'customer_phone' => $extraData['customer_phone'] ?? null,
                    'customer_email' => $extraData['customer_email'] ?? null,
                ]);

                // ================================================================
                // Step 7: Insert booked_seats (Safe vì đã lock từ step 1)
                // ================================================================
                foreach ($seatDetails as $detail) {
                    DB::table('booked_seats')->insert([
                        'booking_id' => $bookingId,
                        'seat_id' => $detail['seat_id'],
                        'price_at_booking' => $detail['price_at_booking'],
                        'status' => 'RESERVED',
                        'qr_code' => $this->generateQRCode($bookingCode, $detail['seat_row'], $detail['seat_number']),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // ================================================================
                // Step 7.1: Insert booking_combos
                // ================================================================
                foreach ($comboDetails as $cd) {
                    DB::table('booking_combos')->insert([
                        'booking_id' => $bookingId,
                        'combo_id' => $cd['combo_id'],
                        'quantity' => $cd['quantity'],
                        'price' => $cd['price'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                return $bookingId;

            }, 5); // Retry tối đa 5 lần nếu xảy ra Deadlock

            // ── Anti-Abuse: Record hold AFTER transaction thành công (TRACKING ONLY) ──
            // Nếu transaction rollback → code này không chạy → đúng behavior.
            if ($userId) {
                try {
                    $holdAbuseService = new SeatHoldAbuseService();
                    $customExpiresAt = $inheritedBookingTime ? $inheritedBookingTime->copy()->addMinutes(self::getHoldDuration()) : null;
                    $holdAbuseService->recordHold(
                        $userId,
                        $showtimeId,
                        $bookingId,
                        count($selectedSeatIds),
                        request()?->ip(),
                        $customExpiresAt
                    );
                } catch (\Throwable $trackingEx) {
                    // Tracking failure KHÔNG được block booking flow
                    \Illuminate\Support\Facades\Log::warning('SeatHold tracking failed: ' . $trackingEx->getMessage());
                }
            }

            try {
                event(new \App\Events\SeatStatusUpdated($showtimeId, $selectedSeatIds, 'Pending'));
            } catch (\Throwable $broadcastEx) {
                \Illuminate\Support\Facades\Log::warning('Broadcasting SeatStatusUpdated failed: ' . $broadcastEx->getMessage());
            }

            return $bookingId;

        } catch (\Throwable $e) {
            // Giải phóng toàn bộ Redis Lock nếu giao dịch thất bại
            if (isset($locks) && is_array($locks)) {
                foreach ($locks as $lockKey) {
                    \Illuminate\Support\Facades\Redis::del($lockKey);
                }
            }
            
            // Xử lý Deadlock Exception (error code 40001 - Serialization failure)
            if ($e instanceof \Illuminate\Database\QueryException && ($e->getCode() === '40001' || $e->getCode() === '1213')) {
                throw new Exception(
                    'Có quá nhiều khách đặt vé cùng lúc. Vui lòng thử lại sau vài giây!',
                    1
                );
            }
            throw $e;
        }
    } finally {
        if (isset($userLock) && $userLock) {
            $userLock->release();
        }
    }
}

    /**
     * Hủy các booking Pending quá hạn thanh toán và giải phóng ghế.
     *
     * @return int
     */
    public function getUserBookedSeatCount(?int $userId, ?int $movieId = null): int
    {
        if (!$userId) {
            return 0;
        }

        $bookingQuery = DB::table('bookings')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->where('bookings.user_id', $userId)
            ->whereIn('bookings.status', ['Paid', 'Used']);

        if ($movieId) {
            $bookingQuery->where('showtimes.movie_id', $movieId);
        }

        $bookingIds = $bookingQuery->pluck('bookings.id')->toArray();

        if (empty($bookingIds)) {
            return 0;
        }

        return DB::table('booked_seats')
            ->whereIn('booking_id', $bookingIds)
            ->count();
    }

    private function getMovieIdFromShowtime(int $showtimeId): ?int
    {
        $showtime = DB::table('showtimes')->where('id', $showtimeId)->first();

        return $showtime ? (int) $showtime->movie_id : null;
    }

    private function cancelUserPendingBookingsForMovie(?int $userId, ?int $movieId): void
    {
        if (!$userId || !$movieId) {
            return;
        }

        $pendingBookingIds = DB::table('bookings')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->where('bookings.user_id', $userId)
            ->where('showtimes.movie_id', $movieId)
            ->where('bookings.status', 'Pending')
            ->pluck('bookings.id')
            ->toArray();

        if (empty($pendingBookingIds)) {
            return;
        }

        $bookingsWithCoupons = DB::table('bookings')
            ->whereIn('id', $pendingBookingIds)
            ->whereNotNull('coupon_id')
            ->get();

        foreach ($bookingsWithCoupons as $b) {
            DB::table('coupons')
                ->where('id', $b->coupon_id)
                ->where('used_count', '>', 0)
                ->decrement('used_count');
        }

        DB::table('bookings')
            ->whereIn('id', $pendingBookingIds)
            ->update([
                'status' => 'Cancelled',
                'cancellation_reason' => 'Replaced by a new booking request',
                'cancelled_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('booked_seats')
            ->whereIn('booking_id', $pendingBookingIds)
            ->update([
                'status' => 'CANCELLED',
                'updated_at' => now(),
            ]);

        DB::table('seat_holds')
            ->whereIn('booking_id', $pendingBookingIds)
            ->where('status', 'active')
            ->update([
                'status' => 'released',
                'released_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function cleanupExpiredPendingBookings(): int
    {
        $expiredBookings = []; // Capture for tracking after transaction

        $result = DB::transaction(function () use (&$expiredBookings) {
            $expiredAt = now()->subMinutes(self::getHoldDuration());

            $expiredBookings = DB::table('bookings')
                ->where(function($query) use ($expiredAt) {
                    $query->where('status', 'Pending')
                          ->where('booking_time', '<', $expiredAt);
                })
                ->orWhere(function($query) use ($expiredAt) {
                    // Dọn dẹp cả PROCESSING nhưng cho thêm 15 phút ân hạn (tránh treo ghế vĩnh viễn)
                    $query->where('status', 'PROCESSING')
                          ->where('booking_time', '<', $expiredAt->copy()->subMinutes(15));
                })
                ->select('id', 'user_id')
                ->get();

            if ($expiredBookings->isEmpty()) {
                return 0;
            }
            
            $expiredBookingIds = $expiredBookings->pluck('id')->toArray();

            // Hoàn lại lượt dùng mã giảm giá
            $bookingsWithCoupons = DB::table('bookings')
                ->whereIn('id', $expiredBookingIds)
                ->whereNotNull('coupon_id')
                ->get();

            foreach ($bookingsWithCoupons as $b) {
                DB::table('coupons')->where('id', $b->coupon_id)->where('used_count', '>', 0)->decrement('used_count');
            }

            DB::table('bookings')
                ->whereIn('id', $expiredBookingIds)
                ->update([
                    'status' => 'Cancelled',
                    'cancellation_reason' => 'Payment timeout expired',
                    'cancelled_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('booked_seats')
                ->whereIn('booking_id', $expiredBookingIds)
                ->update([
                    'status' => 'CANCELLED',
                    'updated_at' => now(),
                ]);

            return count($expiredBookingIds);
        });

        // ── Anti-Abuse & Realtime Broadcast: Mark expired holds and broadcast AVAILABLE ──
        if (!empty($expiredBookings) && count($expiredBookings) > 0) {
            try {
                $abuseService = new SeatHoldAbuseService();
                $userIds = [];
                foreach ($expiredBookings as $booking) {
                    $abuseService->markExpired($booking->id);
                    if ($booking->user_id) {
                        $userIds[] = $booking->user_id;
                    }

                    // Delete Redis locks & broadcast AVAILABLE for expired seats
                    $expiredSeats = DB::table('booked_seats')->where('booking_id', $booking->id)->pluck('seat_id')->toArray();
                    if (!empty($expiredSeats)) {
                        foreach ($expiredSeats as $seatId) {
                            try {
                                \Illuminate\Support\Facades\Redis::del("seat_lock:showtime_{$booking->showtime_id}:seat_{$seatId}");
                            } catch (\Throwable $t) {}
                        }
                        try {
                            event(new \App\Events\SeatStatusUpdated($booking->showtime_id, $expiredSeats, 'AVAILABLE', $booking->user_id ?? null));
                        } catch (\Throwable $t) {}
                    }
                }
                
                // Trigger abuse detection for affected users
                $userIds = array_unique($userIds);
                foreach ($userIds as $userId) {
                    $abuseService->checkAndApplyAbuse($userId);
                }
            } catch (\Throwable $trackingEx) {
                \Illuminate\Support\Facades\Log::warning('Tracking markExpired in cleanup failed: ' . $trackingEx->getMessage());
            }
        }

        return $result;
    }

    /**
     * Thanh toán booking - cập nhật status từ Pending → Paid
     *
     * @param int $bookingId
     * @param string $paymentMethod (VNPay, Momo, Direct Banking, etc.)
     * @param array $additionalData (transaction ID, reference code, etc.)
     * @return bool
     * @throws Exception
     */
    public function completePayment(
        int $bookingId,
        string $paymentMethod,
        array $additionalData = []
    ): bool {
        \Illuminate\Support\Facades\Log::info("BookingService::completePayment [Step: Complete Payment] - Starting for Booking ID: {$bookingId}, Method: {$paymentMethod}");
        $result = DB::transaction(function () use ($bookingId, $paymentMethod, $additionalData) {

            // Kiểm tra booking tồn tại + status = Pending hoặc PROCESSING (Sử dụng Eloquent Model để có thể save/kích hoạt Observer)
            $bookingModel = \App\Models\Booking::where('id', $bookingId)
                ->lockForUpdate()
                ->first();

            if (!$bookingModel) {
                \Illuminate\Support\Facades\Log::warning("BookingService::completePayment - Booking $bookingId không tồn tại");
                throw new Exception("Booking $bookingId không tồn tại");
            }

            if (!in_array($bookingModel->status, ['Pending', 'PROCESSING'])) {
                \Illuminate\Support\Facades\Log::warning("BookingService::completePayment - Booking $bookingId không thể thanh toán. Status: {$bookingModel->status}");
                throw new Exception(
                    "Không thể thanh toán booking này. Status: {$bookingModel->status}. " .
                    "Chỉ có thể thanh toán booking ở trạng thái Pending hoặc PROCESSING."
                );
            }

            // Kiểm tra Redis key để đảm bảo ghế chưa bị nhả do quá hạn (nếu Redis khả dụng)
            try {
                $seatIds = DB::table('booked_seats')->where('booking_id', $bookingId)->pluck('seat_id')->toArray();
                foreach ($seatIds as $seatId) {
                    if (!\Illuminate\Support\Facades\Redis::exists("seat_lock:showtime_{$bookingModel->showtime_id}:seat_{$seatId}")) {
                        throw new Exception("Đơn hàng đã hết hạn giữ chỗ (hoặc ghế đã bị nhả). Vui lòng liên hệ CSKH để được hỗ trợ hoàn tiền.");
                    }
                }
            } catch (\Throwable $ex) {
                if (str_contains($ex->getMessage(), 'Đơn hàng đã hết hạn giữ chỗ')) {
                    throw $ex;
                }
                \Illuminate\Support\Facades\Log::warning("Redis check in completePayment skipped: " . $ex->getMessage());
            }

            // Cập nhật booking status sử dụng Eloquent để kích hoạt BookingObserver
            $bookingModel->status = 'Paid';
            $bookingModel->payment_method = $paymentMethod;
            $bookingModel->payment_time = now();
            $bookingModel->save();

            // Cập nhật status các vé
            DB::table('booked_seats')
                ->where('booking_id', $bookingId)
                ->update([
                    'status' => 'PAID',
                    'updated_at' => now(),
                ]);

            \Illuminate\Support\Facades\Log::info("BookingService::completePayment [Step: Complete Payment] - Successfully completed payment for Booking ID: {$bookingId}");
            return true;

        });

        // ── Anti-Abuse: Mark hold completed SAU transaction thành công ──
        // Payment thành công → hold tracking chuyển active → completed.
        try {
            $abuseService = new SeatHoldAbuseService();
            $abuseService->markCompleted($bookingId);
        } catch (\Throwable $trackingEx) {
            \Illuminate\Support\Facades\Log::warning('Tracking markCompleted in completePayment failed: ' . $trackingEx->getMessage());
        }

        // ── Redis & Broadcast (Reverb Events) ──
        try {
            $booking = \App\Models\Booking::with(['showtime.movie', 'showtime.room.cinema', 'bookedSeats'])->find($bookingId);
            if ($booking) {
                $showtimeId = $booking->showtime_id;
                $seatIds = $booking->bookedSeats->pluck('seat_id')->toArray();
                
                // 1. Release Redis lock keys
                foreach ($seatIds as $seatId) {
                    \Illuminate\Support\Facades\Redis::del("seat_lock:showtime_{$showtimeId}:seat_{$seatId}");
                }

                // 2. Broadcast SeatStatusUpdated (PAID) to Showtime Presence Channel
                event(new \App\Events\SeatStatusUpdated($showtimeId, $seatIds, 'PAID', $booking->user_id));

                // 3. Broadcast PaymentConfirmed to Order Private Channel for instant browser redirect
                $redirectUrl = route('booking.history.show', ['bookingCode' => $booking->booking_code]);
                event(new \App\Events\PaymentConfirmed(
                    $booking->booking_code,
                    $booking->id,
                    $redirectUrl,
                    'PAID',
                    'Thanh toán thành công! Đang chuyển hướng...',
                    $paymentMethod
                ));

                // 4. Calculate Live Analytics & Broadcast LiveRevenueUpdated to Admin/Manager
                $cinemaId = $booking->showtime?->room?->cinema_id ?? 1;
                $totalToday = (float) \App\Models\Booking::whereDate('payment_time', today())
                    ->whereIn('status', ['Paid', 'Used'])
                    ->whereHas('showtime.room', function($q) use ($cinemaId) {
                        $q->where('cinema_id', $cinemaId);
                    })->sum('total_price');

                $bookingsTodayCount = \App\Models\Booking::whereDate('payment_time', today())
                    ->whereIn('status', ['Paid', 'Used'])
                    ->whereHas('showtime.room', function($q) use ($cinemaId) {
                        $q->where('cinema_id', $cinemaId);
                    })->count();

                $totalRoomSeats = (int) ($booking->showtime?->room?->seats()?->count() ?? 100);
                $occupiedSeatsCount = (int) \App\Models\BookedSeat::whereHas('booking', function($q) use ($showtimeId) {
                    $q->where('showtime_id', $showtimeId)->whereIn('status', ['PAID', 'USED']);
                })->count();

                $occupancyRate = $totalRoomSeats > 0 ? ($occupiedSeatsCount / $totalRoomSeats) * 100 : 0.0;

                event(new \App\Events\LiveRevenueUpdated(
                    $cinemaId,
                    (float) $booking->total_price,
                    $totalToday,
                    $bookingsTodayCount,
                    $showtimeId,
                    $booking->showtime?->movie?->title,
                    $occupancyRate
                ));
            }
        } catch (\Throwable $ex) {
            \Illuminate\Support\Facades\Log::warning('Redis/Broadcast in completePayment failed: ' . $ex->getMessage());
        }

        return $result;
    }

    /**
     * Hủy booking - cập nhật status từ Pending/Paid → Cancelled
     *
     * @param int $bookingId
     * @param string $reason Lý do hủy
     * @return bool
     * @throws Exception
     */
    public function cancelBooking(int $bookingId, string $reason = ''): bool {
        // Fetch booking info & seat IDs BEFORE transaction to guarantee availability broadcast
        $bookingInfo = DB::table('bookings')->where('id', $bookingId)->first();
        $showtimeId = $bookingInfo ? $bookingInfo->showtime_id : null;
        $seatIds = $bookingInfo ? DB::table('booked_seats')->where('booking_id', $bookingId)->pluck('seat_id')->toArray() : [];

        $result = DB::transaction(function () use ($bookingId, $reason) {

            $booking = DB::table('bookings')
                ->where('id', $bookingId)
                ->lockForUpdate()
                ->first();

            if (!$booking) {
                throw new Exception("Booking $bookingId không tồn tại");
            }

            if (!in_array($booking->status, ['Pending', 'PROCESSING', 'Paid'])) {
                throw new Exception(
                    "Không thể hủy booking này. Status: {$booking->status}"
                );
            }

            // Hoàn lại lượt dùng mã giảm giá
            if ($booking->coupon_id) {
                DB::table('coupons')->where('id', $booking->coupon_id)->where('used_count', '>', 0)->decrement('used_count');
            }

            // Cập nhật booking status
            DB::table('bookings')
                ->where('id', $bookingId)
                ->update([
                    'status' => 'Cancelled',
                    'cancellation_reason' => $reason,
                    'cancelled_at' => now(),
                    'updated_at' => now(),
                ]);

            // Cập nhật status các vé
            DB::table('booked_seats')
                ->where('booking_id', $bookingId)
                ->update([
                    'status' => 'CANCELLED',
                    'updated_at' => now(),
                ]);

            return true;

        });

        // ── Anti-Abuse: Mark released cho tracking (cancel bình thường) ──
        // Chạy SAU transaction thành công.
        try {
            $abuseService = new SeatHoldAbuseService();
            $abuseService->markReleased($bookingId);
        } catch (\Throwable $trackingEx) {
            \Illuminate\Support\Facades\Log::warning('Tracking markReleased in cancelBooking failed: ' . $trackingEx->getMessage());
        }

        // ── Redis & Broadcast ──
        try {
            if ($showtimeId && !empty($seatIds)) {
                foreach ($seatIds as $seatId) {
                    \Illuminate\Support\Facades\Redis::del("seat_lock:showtime_{$showtimeId}:seat_{$seatId}");
                    if ($bookingInfo && $bookingInfo->user_id) {
                        \Illuminate\Support\Facades\Cache::put(
                            "cooldown_user_{$bookingInfo->user_id}_showtime_{$showtimeId}_seat_{$seatId}",
                            true,
                            now()->addMinutes(3)
                        );
                    }
                }
                event(new \App\Events\SeatStatusUpdated($showtimeId, $seatIds, 'AVAILABLE', $bookingInfo->user_id ?? null));
            }
        } catch (\Throwable $ex) {
            \Illuminate\Support\Facades\Log::warning('Redis/Broadcast in cancelBooking failed: ' . $ex->getMessage());
        }

        return $result;
    }

    /**
     * Lấy danh sách ghế còn trống của suất chiếu
     *
     * @param int $showtimeId
     * @return array Danh sách ghế còn trống
     */
    public function getAvailableSeats(int $showtimeId): array {
        $room = DB::table('showtimes')
            ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
            ->where('showtimes.id', $showtimeId)
            ->select('rooms.id as room_id')
            ->first();

        if (!$room) {
            throw new Exception("Suất chiếu $showtimeId không tồn tại");
        }

        // Lấy toàn bộ ghế của phòng
        $allSeats = DB::table('seats')
            ->where('room_id', $room->room_id)
            ->where('status', 'AVAILABLE')
            ->get();

        // Lấy ghế đã đặt (chưa hủy)
        $bookedSeatIds = DB::table('booked_seats')
            ->join('bookings', 'booked_seats.booking_id', '=', 'bookings.id')
            ->where('bookings.showtime_id', $showtimeId)
            ->where('bookings.status', '!=', 'Cancelled')
            ->pluck('booked_seats.seat_id')
            ->toArray();

        // Filter ghế trống
        return $allSeats
            ->filter(fn($seat) => !in_array($seat->id, $bookedSeatIds))
            ->map(fn($seat) => [
                'id' => $seat->id,
                'row' => $seat->row_name,
                'number' => $seat->seat_number,
                'type' => $seat->seat_type,
            ])
            ->values()
            ->toArray();
    }

    /**
     * Cập nhật thông tin booking Pending (combos, coupon, payment_method, total_price)
     *
     * @param int $bookingId
     * @param string|null $paymentMethod
     * @param string|null $couponCode
     * @param array $combos Format: [combo_id => ['qty' => int]]
     * @return \App\Models\Booking
     * @throws Exception
     */
    public function updatePendingBooking(
        int $bookingId,
        ?string $paymentMethod = null,
        ?string $couponCode = null,
        array $combos = []
    ): \App\Models\Booking {
        return DB::transaction(function () use ($bookingId, $paymentMethod, $couponCode, $combos) {
            $booking = \App\Models\Booking::with('bookedSeats')->where('id', $bookingId)->lockForUpdate()->first();

            if (!$booking) {
                throw new Exception("Booking không tồn tại.");
            }

            if ($booking->status !== 'Pending') {
                throw new Exception("Không thể cập nhật đơn đặt vé không ở trạng thái chờ thanh toán.");
            }

            // 1. Tính tổng tiền ghế từ các ghế đã đặt trong booking
            $seatTotalPrice = 0;
            foreach ($booking->bookedSeats as $seat) {
                $seatTotalPrice += (float) $seat->price_at_booking;
            }

            // 2. Cập nhật Combos
            // Xóa toàn bộ combos cũ của booking này trong bảng booking_combos
            DB::table('booking_combos')->where('booking_id', $bookingId)->delete();

            $comboTotalPrice = 0;
            $comboDetails = [];

            if (!empty($combos)) {
                $comboIds = array_keys($combos);
                $dbCombos = DB::table('combos')
                    ->whereIn('id', $comboIds)
                    ->get()
                    ->keyBy('id');

                foreach ($combos as $comboId => $comboData) {
                    $qty = (int) ($comboData['qty'] ?? 0);
                    if ($qty > 0) {
                        if (!isset($dbCombos[$comboId])) {
                            throw new Exception("Combo không tồn tại.");
                        }

                        $comboPrice = (float) $dbCombos[$comboId]->price;
                        $comboTotalPrice += ($comboPrice * $qty);

                        $comboDetails[] = [
                            'booking_id' => $bookingId,
                            'combo_id' => $comboId,
                            'quantity' => $qty,
                            'price' => $comboPrice,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                if (!empty($comboDetails)) {
                    DB::table('booking_combos')->insert($comboDetails);
                }
            }

            $subtotal = $seatTotalPrice + $comboTotalPrice;

            // 3. Xử lý Mã giảm giá (Coupon)
            $couponId = null;
            $discountAmount = 0;

            if (!empty($couponCode)) {
                $coupon = \App\Models\Coupon::where('code', strtoupper(trim($couponCode)))
                    ->where('status', 'ACTIVE')
                    ->lockForUpdate()
                    ->first();

                if (!$coupon) {
                    throw new Exception("Mã giảm giá không hợp lệ hoặc đã hết hạn.");
                }

                $validation = $coupon->isValid($subtotal, $booking->user_id);
                if (!$validation['valid']) {
                    throw new Exception($validation['message']);
                }

                $discountAmount = $coupon->calculateDiscount($subtotal);
                $couponId = $coupon->id;

                // Nếu đổi mã coupon mới hoặc trước đó chưa dùng mã này
                if ($booking->coupon_id !== $couponId) {
                    if ($booking->coupon_id) {
                        DB::table('coupons')
                            ->where('id', $booking->coupon_id)
                            ->where('used_count', '>', 0)
                            ->decrement('used_count');
                    }
                    $coupon->increment('used_count');
                }
            } else {
                // Hủy mã giảm giá nếu trước đó có áp dụng mà giờ bỏ
                if ($booking->coupon_id) {
                    DB::table('coupons')
                        ->where('id', $booking->coupon_id)
                        ->where('used_count', '>', 0)
                        ->decrement('used_count');
                }
            }

            $finalTotalPrice = max(0, $subtotal - $discountAmount);

            // 4. Lưu thông tin đã cập nhật vào booking
            $booking->total_price = $finalTotalPrice;
            $booking->coupon_id = $couponId;
            $booking->discount_amount = $discountAmount;
            if ($paymentMethod) {
                $booking->payment_method = $paymentMethod;
            }
            $booking->save();

            return $booking;
        });
    }


    /**
     * Checkout khách - lấy thông tin booking + booked_seats
     *
     * @param int $bookingId
     * @return array
     */
    public function getBookingDetails(int $bookingId): array {
        \Illuminate\Support\Facades\Log::info("BookingService::getBookingDetails [Step: Get Booking Details] - Fetching details for Booking ID: {$bookingId}");
        $booking = DB::table('bookings')
            ->where('id', $bookingId)
            ->first();

        if (!$booking) {
            \Illuminate\Support\Facades\Log::warning("BookingService::getBookingDetails - Booking $bookingId không tồn tại");
            throw new Exception("Booking $bookingId không tồn tại");
        }

        $bookedSeats = DB::table('booked_seats')
            ->join('seats', 'booked_seats.seat_id', '=', 'seats.id')
            ->where('booked_seats.booking_id', $bookingId)
            ->select(
                'booked_seats.id',
                'seats.row_name',
                'seats.seat_number',
                'seats.seat_type',
                'booked_seats.price_at_booking',
                'booked_seats.status',
                'booked_seats.qr_code'
            )
            ->get();

        // Fetch combos
        $combos = DB::table('booking_combos')
            ->join('combos', 'booking_combos.combo_id', '=', 'combos.id')
            ->where('booking_combos.booking_id', $bookingId)
            ->select('combos.id', 'combos.name', 'booking_combos.quantity', 'booking_combos.price')
            ->get();

        // Fetch showtime and movie details
        $showtime = DB::table('showtimes')
            ->where('id', $booking->showtime_id)
            ->first();

        $movie = null;
        if ($showtime) {
            $movie = DB::table('movies')
                ->where('id', $showtime->movie_id)
                ->first();
        }

        // Fetch user email if not set on booking level
        $userEmail = '';
        $userName = '';
        if ($booking->user_id) {
            $user = DB::table('users')->where('id', $booking->user_id)->first();
            if ($user) {
                $userEmail = $user->email;
                $userName = $user->name;
            }
        }

        \Illuminate\Support\Facades\Log::info("BookingService::getBookingDetails [Step: Get Booking Details] - Successfully fetched details for Booking ID: {$bookingId}");
        return [
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'status' => $booking->status,
            'total_price' => $booking->total_price,
            'payment_method' => $booking->payment_method,
            'booking_time' => $booking->booking_time,
            'payment_time' => $booking->payment_time,
            'discount_amount' => $booking->discount_amount ?? 0,
            'customer_name' => $booking->customer_name ?? $userName ?? 'Khách hàng',
            'customer_email' => $booking->customer_email ?? $userEmail ?? '',
            'seats' => $bookedSeats,
            'combos' => $combos,
            'showtime' => $showtime,
            'movie' => $movie,
        ];
    }

    /**
     * Tạo QR Code cho vé (simplified - thực tế nên dùng library QR code)
     *
     * @param string $bookingCode
     * @param string $row
     * @param int $seatNumber
     * @return string
     */
    private function generateQRCode(string $bookingCode, string $row, int $seatNumber): string {
        $payload = [
            'booking_code' => $bookingCode,
            'seat' => $row . $seatNumber,
            'timestamp' => now()->timestamp,
        ];

        // Add HMAC checksum
        ksort($payload);
        $dataToSign = json_encode($payload);
        $secretKey = config('ticket.secret_key', env('APP_KEY'));
        $payload['checksum'] = hash_hmac('sha256', $dataToSign, $secretKey);

        return base64_encode(json_encode($payload));
    }
}
