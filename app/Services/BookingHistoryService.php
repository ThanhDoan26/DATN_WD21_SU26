<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BookingHistoryService
{
    /**
     * Get paginated bookings for a specific user with status filter.
     *
     * @param int $userId
     * @param string $statusFilter ('paid', 'cancelled', 'all')
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserBookings(int $userId, string $statusFilter = 'paid', int $perPage = 10): LengthAwarePaginator
    {
        $query = Booking::where('user_id', $userId)
            ->with(['showtime.movie', 'showtime.room.cinema', 'bookedSeats.seat']);

        // Loại bỏ các đơn nháp tự động bị thay thế bởi thao tác đặt vé mới
        $excludedReasons = [
            'User initiated a new booking request',
            'Replaced by a new booking request',
        ];

        switch ($statusFilter) {
            case 'paid':
                $query->whereIn('status', [\App\Models\Booking::STATUS_PAID, \App\Models\Booking::STATUS_USED]);
                break;
            case 'cancelled':
                $query->whereIn('status', [\App\Models\Booking::STATUS_CANCELLED, \App\Models\Booking::STATUS_EXPIRED])
                    ->where(function ($q) use ($excludedReasons) {
                        $q->whereNull('cancellation_reason')
                          ->orWhereNotIn('cancellation_reason', $excludedReasons);
                    });
                break;
            case 'all':
            default:
                $query->where(function ($q) use ($excludedReasons) {
                    $q->whereIn('status', [\App\Models\Booking::STATUS_PAID, \App\Models\Booking::STATUS_USED, \App\Models\Booking::STATUS_PENDING, 'processing'])
                      ->orWhere(function ($q2) use ($excludedReasons) {
                          $q2->whereIn('status', [\App\Models\Booking::STATUS_CANCELLED, \App\Models\Booking::STATUS_EXPIRED])
                             ->where(function ($q3) use ($excludedReasons) {
                                 $q3->whereNull('cancellation_reason')
                                    ->orWhereNotIn('cancellation_reason', $excludedReasons);
                             });
                      });
                });
                break;
        }

        return $query->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends(['status' => $statusFilter]);
    }

    /**
     * Get counts for tab badges.
     *
     * @param int $userId
     * @return array
     */
    public function getBookingCounts(int $userId): array
    {
        $excludedReasons = [
            'User initiated a new booking request',
            'Replaced by a new booking request',
        ];

        return [
            'paid' => Booking::where('user_id', $userId)->whereIn('status', [\App\Models\Booking::STATUS_PAID, \App\Models\Booking::STATUS_USED])->count(),
            'cancelled' => Booking::where('user_id', $userId)
                ->whereIn('status', [\App\Models\Booking::STATUS_CANCELLED, \App\Models\Booking::STATUS_EXPIRED])
                ->where(function ($q) use ($excludedReasons) {
                    $q->whereNull('cancellation_reason')
                      ->orWhereNotIn('cancellation_reason', $excludedReasons);
                })
                ->count(),
            'all' => Booking::where('user_id', $userId)
                ->where(function ($q) use ($excludedReasons) {
                    $q->whereIn('status', [\App\Models\Booking::STATUS_PAID, \App\Models\Booking::STATUS_USED, \App\Models\Booking::STATUS_PENDING, 'processing'])
                      ->orWhere(function ($q2) use ($excludedReasons) {
                          $q2->whereIn('status', [\App\Models\Booking::STATUS_CANCELLED, \App\Models\Booking::STATUS_EXPIRED])
                             ->where(function ($q3) use ($excludedReasons) {
                                 $q3->whereNull('cancellation_reason')
                                    ->orWhereNotIn('cancellation_reason', $excludedReasons);
                             });
                      });
                })
                ->count(),
        ];
    }

    /**
     * Get details for a specific booking by its code.
     *
     * @param string $bookingCode
     * @param int $userId
     * @return Booking|null
     */
    public function getBookingDetails(string $bookingCode, int $userId): ?Booking
    {
        $booking = Booking::where('booking_code', $bookingCode)
            ->where('user_id', $userId)
            ->with([
                'showtime.movie',
                'showtime.room.cinema',
                'bookedSeats.seat',
                'combos.comboReviews',
                'coupon'
            ])
            ->first();

        if ($booking && $booking->isPaid() && empty($booking->ticket_token)) {
            $booking->ticket_token = (string) \Illuminate\Support\Str::uuid();
            $booking->saveQuietly();
        }

        return $booking;
    }
}
