<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cinema;
use App\Models\Room;
use App\Models\Movie;
use App\Models\Booking;

/**
 * DashboardController
 * ========================================
 * Controller cho trang dashboard admin
 */
class DashboardController extends AdminController
{
    public function index()
    {
        $data = [
            'totalCinemas' => Cinema::count(),
            'totalRooms' => Room::count(),
            'totalMovies' => Movie::count(),
            'totalBookings' => Booking::count(),
        ];

        return view('admin.dashboard', $data);
    }
}
