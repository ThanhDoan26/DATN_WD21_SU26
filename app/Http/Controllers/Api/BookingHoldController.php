<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Showtime;
use App\Services\BookingService;
use App\Services\SeatHoldAbuseService;
use App\Events\SeatStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class BookingHoldController extends Controller
{
    /**
     * API giải phóng ghế tức thì khi người dùng đóng tab / tắt trình duyệt (navigator.sendBeacon)
     * Endpoint: POST /api/v1/bookings/release-hold-seats
     */
    public function releaseHoldSeats(Request $request)
    {
        $bookingId = $request->input('booking_id');
        $showtimeId = $request->input('showtime_id');
        $seatIdsInput = $request->input('seat_ids');

        $seatIds = [];
        if (!empty($seatIdsInput)) {
            $seatIds = is_array($seatIdsInput) ? $seatIdsInput : array_filter(explode(',', (string) $seatIdsInput));
            $seatIds = array_map('intval', $seatIds);
        }

        $bookingService = new BookingService();
        $cancelledCount = 0;

        // 1. Xử lý giải phóng theo booking_id
        if (!empty($bookingId)) {
            $booking = Booking::with('bookedSeats')->find($bookingId);
            if ($booking && in_array(strtolower($booking->status), [Booking::STATUS_PENDING, 'processing'])) {
                try {
                    $bookingService->cancelBooking($booking->id, 'User closed browser/tab (sendBeacon)');
                    $cancelledCount++;
                    Log::info("BookingHoldController: Released booking ID {$booking->id} via beacon");
                } catch (\Throwable $e) {
                    Log::warning("BookingHoldController: Failed to cancel booking ID {$booking->id}: " . $e->getMessage());
                }
            }
        }

        // 2. Xử lý giải phóng theo showtime_id và danh sách ghế nếu chưa có booking hoặc kèm theo
        if (!empty($showtimeId) && !empty($seatIds)) {
            // Tìm các booking Pending chứa các ghế này
            $pendingBookings = DB::table('bookings')
                ->join('booked_seats', 'bookings.id', '=', 'booked_seats.booking_id')
                ->where('bookings.showtime_id', $showtimeId)
                ->whereIn(DB::raw('LOWER(bookings.status)'), [Booking::STATUS_PENDING, 'processing'])
                ->whereIn('booked_seats.seat_id', $seatIds)
                ->select('bookings.id')
                ->distinct()
                ->get();

            foreach ($pendingBookings as $pb) {
                try {
                    $bookingService->cancelBooking($pb->id, 'User closed browser/tab (sendBeacon)');
                    $cancelledCount++;
                } catch (\Throwable $e) {
                    Log::warning("BookingHoldController: Failed to cancel pending booking {$pb->id}: " . $e->getMessage());
                }
            }

            // Xóa khóa Redis cho từng ghế
            foreach ($seatIds as $seatId) {
                try {
                    Redis::del("seat_lock:showtime_{$showtimeId}:seat_{$seatId}");
                } catch (\Throwable $t) {}
            }

            // Broadcast sự kiện ghế đã trống (AVAILABLE) qua WebSocket / Reverb
            try {
                event(new SeatStatusUpdated($showtimeId, $seatIds, 'AVAILABLE'));
            } catch (\Throwable $t) {
                Log::warning("BookingHoldController: Failed to broadcast SeatStatusUpdated: " . $t->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Released hold seats successfully',
            'cancelled_bookings_count' => $cancelledCount,
        ]);
    }
}
