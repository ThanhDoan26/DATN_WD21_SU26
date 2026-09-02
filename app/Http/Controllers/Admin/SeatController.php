<?php

namespace App\Http\Controllers\Admin;

use App\Models\Seat;
use App\Models\Room;
use App\Models\Cinema;
use App\Services\SeatBookingStateService;

/**
 * SeatController
 * ========================================
 * Controller quản lý seats - Read-only + AJAX (Index & getBySeatsByRoom only)
 */
class SeatController extends AdminController
{
    /**
     * Display a listing of seats
     */
    public function index()
    {
        $seats = Seat::with('room')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $cinemas = Cinema::all();
        $rooms = Room::all();

        return view('admin.seats.index', [
            'seats' => $seats,
            'cinemas' => $cinemas,
            'rooms' => $rooms
        ]);
    }

    /**
     * Get seats by room (AJAX)
     */
    public function getBySeatsByRoom(\Illuminate\Http\Request $request, $roomId)
    {
        $seats = Seat::where('room_id', $roomId)
            ->orderBy('row_name')
            ->orderBy('seat_number')
            ->get();

        $seatBookingStateService = app(SeatBookingStateService::class);
        $seats = $seatBookingStateService->enrichSeatsWithBookingState($seats, (int)$roomId);

        $showtimeId = $request->query('showtime_id');
        if ($showtimeId) {
            $bookedSeatIds = \Illuminate\Support\Facades\DB::table('booked_seats')
                ->join('bookings', 'booked_seats.booking_id', '=', 'bookings.id')
                ->where('bookings.showtime_id', $showtimeId)
                ->whereNotIn('bookings.status', [\App\Models\Booking::STATUS_CANCELLED, 'cancelled', 'Cancelled'])
                ->pluck('booked_seats.seat_id')
                ->toArray();

            $seats = $seats->map(function ($seat) use ($bookedSeatIds) {
                $seat->is_booked_in_showtime = in_array($seat->id, $bookedSeatIds);
                return $seat;
            });
        }

        return response()->json($seats);
    }

    /**
     * Show form to edit seat
     */
    public function edit(Seat $seat)
    {
        $seat->load('room.cinema');
        return view('admin.seats.edit', compact('seat'));
    }

    /**
     * Update seat
     */
    public function update(\Illuminate\Http\Request $request, Seat $seat)
    {
        $validated = $request->validate([
            'seat_type' => 'required|in:Regular,VIP,Sweetbox',
            'status' => 'required|in:AVAILABLE,UNAVAILABLE',
        ]);

        $seat->update($validated);

        return redirect()->route('admin.seats.index')
            ->with('success', 'Cập nhật thông tin ghế thành công.');
    }
}
