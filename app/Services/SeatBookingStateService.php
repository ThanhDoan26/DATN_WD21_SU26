<?php

namespace App\Services;

use App\Models\Seat;
use App\Models\Showtime;
use App\Services\BookingService;
use Illuminate\Support\Facades\DB;

/**
 * SeatBookingStateService
 * =========================================================================
 * Service chuyên trách đồng bộ và xác định trạng thái Booking/Hold của ghế
 * đối với hệ thống Quản lý Ghế (Seat Management) của Admin & Manager.
 *
 * Business Rules:
 * 1. Active Hold (< 10 phút): Ghế đang giữ chỗ -> HELD -> KHÔNG thể Lock.
 * 2. Hold hết hạn (> 10 phút) & không có Booking: Ghế tự do -> AVAILABLE -> CÓ THỂ Lock.
 * 3. Booking thành công (Paid/Used): Ghế đã đặt -> BOOKED -> KHÔNG thể Lock.
 * 4. Phạm vi bảo vệ: Áp dụng trên toàn bộ suất chiếu hợp lệ (active showtimes) của phòng.
 * =========================================================================
 */
class SeatBookingStateService
{
    /**
     * Lấy danh sách ID các suất chiếu đang hoạt động hoặc sắp chiếu của phòng.
     *
     * @param int $roomId
     * @return array<int>
     */
    public function getActiveShowtimeIdsForRoom(int $roomId): array
    {
        return Showtime::where('room_id', $roomId)
            ->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
            ->where(function ($q) {
                $q->where('end_time', '>', now())
                  ->orWhere(function ($sub) {
                      $sub->whereNull('end_time')->where('start_time', '>', now()->subHours(3));
                  });
            })
            ->pluck('id')
            ->toArray();
    }

    /**
     * Lấy tổng hợp danh sách seat_id đang HELD hoặc BOOKED cho phòng chiếu.
     *
     * @param int $roomId
     * @return array{held_seat_ids: array<int>, booked_seat_ids: array<int>}
     */
    public function getSeatBookingSummaryForRoom(int $roomId): array
    {
        $activeShowtimeIds = $this->getActiveShowtimeIdsForRoom($roomId);

        if (empty($activeShowtimeIds)) {
            return [
                'held_seat_ids' => [],
                'booked_seat_ids' => [],
            ];
        }

        $holdDuration = BookingService::getHoldDuration();
        $holdCutoff = now()->subMinutes($holdDuration);

        // 1. Active holds: Booking status is Pending/PROCESSING, created within hold window, BookedSeat is RESERVED
        $heldSeatIds = DB::table('booked_seats')
            ->join('bookings', 'booked_seats.booking_id', '=', 'bookings.id')
            ->whereIn('bookings.showtime_id', $activeShowtimeIds)
            ->whereIn('bookings.status', ['Pending', 'PROCESSING'])
            ->where('bookings.booking_time', '>=', $holdCutoff)
            ->where('booked_seats.status', 'RESERVED')
            ->pluck('booked_seats.seat_id')
            ->unique()
            ->values()
            ->toArray();

        // 2. Booked seats: Booking status is Paid/Used, BookedSeat status is PAID/USED
        $bookedSeatIds = DB::table('booked_seats')
            ->join('bookings', 'booked_seats.booking_id', '=', 'bookings.id')
            ->whereIn('bookings.showtime_id', $activeShowtimeIds)
            ->whereIn('bookings.status', ['Paid', 'Used'])
            ->whereIn('booked_seats.status', ['PAID', 'USED'])
            ->pluck('booked_seats.seat_id')
            ->unique()
            ->values()
            ->toArray();

        return [
            'held_seat_ids' => $heldSeatIds,
            'booked_seat_ids' => $bookedSeatIds,
        ];
    }

    /**
     * Kiểm tra một ghế vật lý có thể toggle (khóa/mở khóa) được hay không.
     *
     * @param Seat $seat
     * @return array{allowed: bool, code: string, status: string, message: string}
     */
    public function checkSeatLockable(Seat $seat): array
    {
        $summary = $this->getSeatBookingSummaryForRoom($seat->room_id);

        $isHeld = in_array($seat->id, $summary['held_seat_ids']);
        $isBooked = in_array($seat->id, $summary['booked_seat_ids']);

        if ($isHeld) {
            return [
                'allowed' => false,
                'code' => 'SEAT_HAS_ACTIVE_HOLD',
                'status' => 'HELD',
                'message' => 'Không thể thay đổi trạng thái ghế đang được khách hàng giữ chỗ (đang trong thời gian thanh toán).',
            ];
        }

        if ($isBooked) {
            return [
                'allowed' => false,
                'code' => 'SEAT_ALREADY_BOOKED',
                'status' => 'BOOKED',
                'message' => 'Không thể thay đổi trạng thái ghế đã có khách đặt vé trong suất chiếu hợp lệ.',
            ];
        }

        return [
            'allowed' => true,
            'code' => 'OK',
            'status' => $seat->status,
            'message' => 'Ghế có thể thay đổi trạng thái.',
        ];
    }

    /**
     * Bổ sung thông tin trạng thái nghiệp vụ (is_held, is_booked, can_toggle, business_status)
     * vào danh sách ghế.
     *
     * @param iterable $seats
     * @param int $roomId
     * @return iterable
     */
    public function enrichSeatsWithBookingState(iterable $seats, int $roomId): iterable
    {
        $summary = $this->getSeatBookingSummaryForRoom($roomId);
        $heldSet = array_flip($summary['held_seat_ids']);
        $bookedSet = array_flip($summary['booked_seat_ids']);

        foreach ($seats as $seat) {
            $isHeld = isset($heldSet[$seat->id]);
            $isBooked = isset($bookedSet[$seat->id]);

            $seat->is_held = $isHeld;
            $seat->is_booked = $isBooked;
            $seat->can_toggle = !$isHeld && !$isBooked;
            $seat->business_status = $isBooked ? 'BOOKED' : ($isHeld ? 'HELD' : $seat->status);
        }

        return $seats;
    }
}
