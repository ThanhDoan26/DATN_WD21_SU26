<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cinema;
use App\Models\Room;
use App\Models\Movie;
use App\Models\Booking;
use App\Services\Admin\DashboardService;

/**
 * DashboardController
 * ========================================
 * Controller cho trang dashboard admin
 */
class DashboardController extends AdminController
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $topCombos = \App\Models\Combo::withCount('comboReviews as total_reviews')
            ->withAvg('comboReviews as average_rating', 'rating')
            ->having('total_reviews', '>', 0)
            ->orderByDesc('average_rating')
            ->orderByDesc('total_reviews')
            ->take(5)
            ->get();

        $statistics = $this->dashboardService->getStatistics();
        
        $data = [
            'totalActiveUsers' => $statistics['totalActiveUsers'],
            'totalMovies'      => $statistics['totalMovies'],
            'totalCinemas'     => $statistics['totalCinemas'],
            'totalShowtimes'   => $statistics['totalShowtimes'],
            'totalTicketsSold' => $statistics['totalTicketsSold'],
            'topCombos'        => $topCombos,
        ];

        return view('admin.dashboard', $data);
    }
}
