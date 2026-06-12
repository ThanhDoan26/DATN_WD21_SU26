<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class SeatMapService
{
    public function generateSeatMapData(Booking $booking): array
    {
        $showtime = $booking->showtime;
        $room = $showtime->room;

        // Get all seats in the room, grouped by row
        $allSeats = DB::table('seats')
            ->where('room_id', $room->id)
            ->orderBy('row_name')
            ->orderBy('seat_number')
            ->get()
            ->groupBy('row_name');

        // Get all seat IDs booked in current booking
        $bookedByCurrentBooking = $booking->bookedSeats()
            ->pluck('seat_id')
            ->toArray();

        // Get all seat IDs booked in other bookings for this showtime
        $bookedByOthers = DB::table('booked_seats')
            ->join('bookings', 'booked_seats.booking_id', '=', 'bookings.id')
            ->where('bookings.showtime_id', $showtime->id)
            ->where('bookings.id', '!=', $booking->id)
            ->where('bookings.status', '!=', 'Cancelled')
            ->pluck('booked_seats.seat_id')
            ->toArray();

        // Count total seats
        $totalSeats = DB::table('seats')
            ->where('room_id', $room->id)
            ->count();

        return [
            'all_seats_grouped' => $allSeats,
            'booked_by_current_booking' => $bookedByCurrentBooking,
            'booked_by_others' => $bookedByOthers,
            'room_total_seats' => $totalSeats,
            'booked_count' => count($bookedByCurrentBooking),
            'room_name' => $room->name,
        ];
    }
}
