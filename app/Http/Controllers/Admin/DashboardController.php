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
        $reportType = request()->query('report_type', 'month');
        $fromDate = request()->query('from_date');
        $toDate = request()->query('to_date');
        $week = request()->query('week') ? (int) request()->query('week') : null;

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

        $statistics = $this->dashboardService->getStatistics($month, $year, $cinemaId, $reportType, $fromDate, $toDate, $week);
        
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
            'periodRevenue'    => $statistics['periodRevenue'],
            'selectedMonth'    => $statistics['selectedMonth'],
            'selectedYear'     => $statistics['selectedYear'],
            'selectedWeek'     => $statistics['selectedWeek'],
            'selectedReportType' => $statistics['selectedReportType'],
            'fromDate'         => $statistics['fromDate'],
            'toDate'           => $statistics['toDate'],
            'selectedCinemaId' => $cinemaId,
            'cinemas'          => \App\Models\Cinema::all(),
            'topCombos'        => $topCombos,
            'topMovies'        => $statistics['topMovies'],
            'movieStatistics'  => $statistics['movieStatistics'] ?? collect(),
            'detailedBookings' => $statistics['detailedBookings'],
        ];

        if (request()->ajax()) {
            return response()->json([
                'totalActiveUsers' => $data['totalActiveUsers'],
                'totalMovies'      => $data['totalMovies'],
                'totalCinemas'     => $data['totalCinemas'],
                'totalShowtimes'   => $data['totalShowtimes'],
                'totalTicketsSold' => $data['totalTicketsSold'],
                'totalRevenue'     => $data['totalRevenue'],
                'dailyRevenue'     => $data['dailyRevenue'],
                'periodRevenue'    => $data['periodRevenue'],
                'monthlyRevenue'   => $data['monthlyRevenue'],
                'yearlyRevenue'    => $data['yearlyRevenue'],
                'selectedMonth'    => $data['selectedMonth'],
                'selectedYear'     => $data['selectedYear'],
                'selectedWeek'     => $data['selectedWeek'],
                'selectedReportType'=> $data['selectedReportType'],
                'fromDate'         => $data['fromDate'],
                'toDate'           => $data['toDate'],
                'selectedCinemaId' => $data['selectedCinemaId'],
                'cinemaName'       => $cinemaId && $data['cinemas']->firstWhere('id', $cinemaId) ? $data['cinemas']->firstWhere('id', $cinemaId)->name : 'Tất cả cụm rạp',
                'html_revenue_table' => view('admin.partials.revenue_table', $data)->render(),
                'html_top_combos'    => view('admin.partials.top_combos', $data)->render(),
                'html_top_movies'    => view('admin.partials.top_movies', $data)->render(),
                'html_movie_statistics' => view('admin.partials.movie_statistics', $data)->render(),
            ]);
        }

        return view('admin.dashboard', $data);
    }
}
