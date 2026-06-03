<?php

namespace App\Http\Controllers\Admin;

use App\Models\Room;
use App\Models\Cinema;
use Illuminate\Http\Request;

/**
 * RoomController
 * ========================================
 * Controller quản lý rooms
 */
class RoomController extends AdminController
{
    /**
     * Display a listing of rooms
     */
    public function index()
    {
        $rooms = Room::with('cinema')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.rooms.index', ['rooms' => $rooms]);
    }

    /**
     * Show the form for creating a new room
     */
    public function create()
    {
        $cinemas = Cinema::where('status', 'ACTIVE')->get();
        return view('admin.rooms.create', compact('cinemas'));
    }

    /**
     * Store a newly created room in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cinema_id' => 'required|exists:cinemas,id',
            'name' => 'required|string|max:255',
            'format' => 'required|string|max:100',
            'total_seats' => 'nullable|integer|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE,MAINTENANCE,CLOSED',
        ]);

        Room::create($validated);

        return redirect()->route('admin.rooms.index')
                         ->with('success', 'Thêm phòng chiếu thành công!');
    }
}
