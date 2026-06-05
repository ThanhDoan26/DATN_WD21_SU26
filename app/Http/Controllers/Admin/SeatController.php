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
