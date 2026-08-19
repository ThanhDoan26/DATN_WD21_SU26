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

        switch ($statusFilter) {
            case 'paid':
                $query->whereIn('status', ['Paid', 'Used']);
                break;
            case 'cancelled':
                $query->whereIn('status', ['Cancelled', 'Expired']);
                break;
            case 'all':
            default:
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
        return [
            'paid' => Booking::where('user_id', $userId)->whereIn('status', ['Paid', 'Used'])->count(),
            'cancelled' => Booking::where('user_id', $userId)->whereIn('status', ['Cancelled', 'Expired'])->count(),
            'all' => Booking::where('user_id', $userId)->count(),
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
        return Booking::where('booking_code', $bookingCode)
            ->where('user_id', $userId)
            ->with([
                'showtime.movie',
                'showtime.room.cinema',
                'bookedSeats.seat',
                'combos.comboReviews' // Eager load combos and their reviews
            ])
            ->first();
    }
}
