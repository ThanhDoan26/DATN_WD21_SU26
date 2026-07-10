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
        $month = request()->query('month') ? (int) request()->query('month') : null;
        $year = request()->query('year') ? (int) request()->query('year') : null;
        $cinemaId = request()->query('cinema_id') ? (int) request()->query('cinema_id') : null;

        $topCombosQuery = \App\Models\Combo::query();
        $topCombosQuery->withCount(['comboReviews as total_reviews' => function ($query) use ($cinemaId) {
            if ($cinemaId) {
                $query->whereHas('booking.showtime.room', function ($q) use ($cinemaId) {
                    $q->where('cinema_id', $cinemaId);
                });
            }
        }])
        ->withAvg(['comboReviews as average_rating' => function ($query) use ($cinemaId) {
            if ($cinemaId) {
                $query->whereHas('booking.showtime.room', function ($q) use ($cinemaId) {
                    $q->where('cinema_id', $cinemaId);
                });
            }
        }], 'rating');

        $topCombos = $topCombosQuery->having('total_reviews', '>', 0)
            ->orderByDesc('average_rating')
            ->orderByDesc('total_reviews')
            ->take(5)
            ->get();

        $statistics = $this->dashboardService->getStatistics($month, $year, $cinemaId);
        
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
            'selectedCinemaId' => $cinemaId,
            'cinemas'          => \App\Models\Cinema::all(),
            'topCombos'        => $topCombos,
            'detailedBookings' => $statistics['detailedBookings'],
        ];

        return view('admin.dashboard', $data);
    }
}
