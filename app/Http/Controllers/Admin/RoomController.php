<?php

namespace App\Http\Controllers\Admin;

use App\Models\Room;

/**
 * RoomController
 * ========================================
 * Controller quản lý rooms - Read-only (Index only)
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
}
