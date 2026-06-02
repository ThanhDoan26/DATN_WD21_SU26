<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;

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
        ?string $paymentMethod = null
    ): int {
        if (empty($selectedSeatIds)) {
            throw new Exception('Vui lòng chọn ít nhất 1 ghế');
        }

        try {
            return DB::transaction(function () use ($userId, $showtimeId, $selectedSeatIds, $paymentMethod) {

                // ================================================================
                // Step 1: Lock các hàng ghế (chỉ 1 request được giữ lock)
                // ================================================================
                // 🔒 SELECT FOR UPDATE - lock các hàng trong booked_seats
                // Các request khác phải đợi cho đến khi transaction này commit/rollback

                $lockedBookedSeats = DB::table('booked_seats')
                    ->join('bookings', 'booked_seats.booking_id', '=', 'bookings.id')
                    ->where('bookings.showtime_id', $showtimeId)
                    ->whereIn('booked_seats.seat_id', $selectedSeatIds)
                    // Chỉ lock ghế chưa hủy
                    ->where('bookings.status', '!=', 'Cancelled')
                    ->lockForUpdate() // 🔒 CRITICAL: SELECT ... FOR UPDATE
                    ->select('booked_seats.seat_id', 'booked_seats.status')
                    ->get();

                // ================================================================
                // Step 2: Kiểm tra xem ghế đã bị đặt hay chưa
                // ================================================================
                if ($lockedBookedSeats->count() > 0) {
                    // Lấy danh sách ghế đã đặt
                    $bookedSeatIds = $lockedBookedSeats->pluck('seat_id')->toArray();
                    throw new Exception(
                        'Một hoặc nhiều ghế đã được đặt bởi khách khác: ' .
                        implode(', ', $bookedSeatIds) .
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
                // Step 4: Lấy giá vé từ ticket_prices
                // ================================================================
                $ticketPrices = DB::table('ticket_prices')
                    ->where('showtime_id', $showtimeId)
                    ->where('status', 'ACTIVE')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('seat_type');

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
                        throw new Exception(
                            "Không có giá vé cho loại ghế {$seat->seat_type} trong suất chiếu này"
                        );
                    }

                    $price = (float) $priceRow->price;
                    $totalPrice += $price;

                    $seatDetails[] = [
                        'seat_id' => $seatId,
                        'seat_row' => $seat->row_name,
                        'seat_number' => $seat->seat_number,
                        'price_at_booking' => $price,
                    ];
                }

                // ================================================================
                // Step 6: Tạo Booking record
                // ================================================================
                $bookingCode = 'BK' . uniqid() . date('Ymd');

                $bookingId = DB::table('bookings')->insertGetId([
                    'user_id' => $userId,
                    'showtime_id' => $showtimeId,
                    'total_price' => $totalPrice,
                    'status' => 'Pending',
                    'payment_method' => $paymentMethod,
                    'booking_time' => now(),
                    'booking_code' => $bookingCode,
                    'created_at' => now(),
                    'updated_at' => now(),
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

                return $bookingId;

            }, 5); // Retry tối đa 5 lần nếu xảy ra Deadlock

        } catch (\Illuminate\Database\QueryException $e) {
            // Xử lý Deadlock Exception (error code 40001 - Serialization failure)
            if ($e->getCode() === '40001' || $e->getCode() === '1213') {
                throw new Exception(
                    'Có quá nhiều khách đặt vé cùng lúc. Vui lòng thử lại sau vài giây!',
                    1
                );
            }
            throw $e;
        }
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
        return DB::transaction(function () use ($bookingId, $paymentMethod, $additionalData) {

            // Kiểm tra booking tồn tại + status = Pending
            $booking = DB::table('bookings')
                ->where('id', $bookingId)
                ->lockForUpdate()
                ->first();

            if (!$booking) {
                throw new Exception("Booking $bookingId không tồn tại");
            }

            if ($booking->status !== 'Pending') {
                throw new Exception(
                    "Không thể thanh toán booking này. Status: {$booking->status}. " .
                    "Chỉ có thể thanh toán booking ở trạng thái Pending."
                );
            }

            // Cập nhật booking status
            DB::table('bookings')
                ->where('id', $bookingId)
                ->update([
                    'status' => 'Paid',
                    'payment_method' => $paymentMethod,
                    'payment_time' => now(),
                    'updated_at' => now(),
                ]);

            // Cập nhật status các vé
            DB::table('booked_seats')
                ->where('booking_id', $bookingId)
                ->update([
                    'status' => 'PAID',
                    'updated_at' => now(),
                ]);

            return true;

        });
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
        return DB::transaction(function () use ($bookingId, $reason) {

            $booking = DB::table('bookings')
                ->where('id', $bookingId)
                ->lockForUpdate()
                ->first();

            if (!$booking) {
                throw new Exception("Booking $bookingId không tồn tại");
            }

            if (!in_array($booking->status, ['Pending', 'Paid'])) {
                throw new Exception(
                    "Không thể hủy booking này. Status: {$booking->status}"
                );
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
     * Checkout khách - lấy thông tin booking + booked_seats
     *
     * @param int $bookingId
     * @return array
     */
    public function getBookingDetails(int $bookingId): array {
        $booking = DB::table('bookings')
            ->where('id', $bookingId)
            ->first();

        if (!$booking) {
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

        return [
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'status' => $booking->status,
            'total_price' => $booking->total_price,
            'payment_method' => $booking->payment_method,
            'booking_time' => $booking->booking_time,
            'payment_time' => $booking->payment_time,
            'seats' => $bookedSeats,
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
        // Simplified QR code - thực tế nên dùng endroid/qr-code hoặc simplesoftware/simple-qr-code
        return base64_encode(json_encode([
            'booking_code' => $bookingCode,
            'seat' => $row . $seatNumber,
            'timestamp' => now()->timestamp,
        ]));
    }
}
