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
        $topCombos = \App\Models\Combo::withCount('comboReviews as total_reviews')
            ->withAvg('comboReviews as average_rating', 'rating')
            ->having('total_reviews', '>', 0)
            ->orderByDesc('average_rating')
            ->orderByDesc('total_reviews')
            ->take(5)
            ->get();

        $data = [
            'totalCinemas' => Cinema::count(),
            'totalRooms' => Room::count(),
            'totalMovies' => Movie::count(),
            'totalBookings' => Booking::count(),
            'topCombos' => $topCombos,
        ];

        return view('admin.dashboard', $data);
    }
}
