<?php

namespace App\Http\Controllers\Admin;

use App\Models\Seat;
use App\Models\Room;
use App\Models\Cinema;

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
    public function getBySeatsByRoom($roomId)
    {
        $seats = Seat::where('room_id', $roomId)
            ->orderBy('row_name')
            ->orderBy('seat_number')
            ->get();

        return response()->json($seats);
    }
}
