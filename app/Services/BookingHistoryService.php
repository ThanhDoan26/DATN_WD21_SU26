<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BookingHistoryService
{
    /**
     * Get paginated bookings for a specific user.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserBookings(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Booking::where('user_id', $userId)
            ->with(['showtime.movie', 'showtime.room.cinema'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
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
                'bookedSeats.seat'
            ])
            ->first();
    }
}
