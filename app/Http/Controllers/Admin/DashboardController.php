<?php

namespace App\Http\Controllers\Admin;

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

        $month = request()->query('month') ? (int) request()->query('month') : null;
        $year = request()->query('year') ? (int) request()->query('year') : null;

        $statistics = $this->dashboardService->getStatistics($month, $year);
        
        $data = [
            'totalActiveUsers' => $statistics['totalActiveUsers'],
            'totalMovies'      => $statistics['totalMovies'],
            'totalCinemas'     => $statistics['totalCinemas'],
            'totalShowtimes'   => $statistics['totalShowtimes'],
            'totalTicketsSold' => $statistics['totalTicketsSold'],
            'totalRevenue'     => $statistics['totalRevenue'],
            'dailyRevenue'     => $statistics['dailyRevenue'],
            'monthlyRevenue'   => $statistics['monthlyRevenue'],
            'yearlyRevenue'    => $statistics['yearlyRevenue'],
            'selectedMonth'    => $statistics['selectedMonth'],
            'selectedYear'     => $statistics['selectedYear'],
            'topCombos'        => $topCombos,
        ];

        return view('admin.dashboard', $data);
    }
}
