<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Manager\DashboardService;

class CinemaManagerDashboardController extends Controller
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
        $reportType = request()->query('report_type', 'month');
        $fromDate = request()->query('from_date');
        $toDate = request()->query('to_date');
        $week = request()->query('week') ? (int) request()->query('week') : null;
        $movieId = request()->query('movie_id') ? (int) request()->query('movie_id') : null;

        $cinemaId = auth()->user()->cinema_id;

        $topCombosQuery = \App\Models\Combo::query();
        $topCombosQuery->withCount(['comboReviews as total_reviews' => function ($query) use ($cinemaId, $movieId) {
            if ($cinemaId) {
                $query->whereHas('booking.showtime.room', function ($q) use ($cinemaId) {
                    $q->where('cinema_id', $cinemaId);
                });
            }
            if ($movieId) {
                $query->whereHas('booking.showtime', function ($q) use ($movieId) {
                    $q->where('movie_id', $movieId);
                });
            }
        }])
        ->withAvg(['comboReviews as average_rating' => function ($query) use ($cinemaId, $movieId) {
            if ($cinemaId) {
                $query->whereHas('booking.showtime.room', function ($q) use ($cinemaId) {
                    $q->where('cinema_id', $cinemaId);
                });
            }
            if ($movieId) {
                $query->whereHas('booking.showtime', function ($q) use ($movieId) {
                    $q->where('movie_id', $movieId);
                });
            }
        }], 'rating');

        $topCombos = $topCombosQuery->having('total_reviews', '>', 0)
            ->orderByDesc('average_rating')
            ->orderByDesc('total_reviews')
            ->take(5)
            ->get();

        $statistics = $this->dashboardService->getStatistics($month, $year, $reportType, $fromDate, $toDate, $week, $movieId);
        
        $data = [
            'totalActiveUsers' => $statistics['totalActiveUsers'],
            'totalMovies'      => $statistics['totalMovies'],
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
            'selectedMovieId'  => $movieId,
            'movies'           => \App\Models\Movie::all(),
            'topCombos'        => $topCombos,
            'topMovies'        => $statistics['topMovies'],
            'movieStatistics'  => $statistics['movieStatistics'] ?? collect(),
            'detailedBookings' => $statistics['detailedBookings'],
            'chartData'        => $statistics['chartData'],
        ];

        if (request()->ajax()) {
            return response()->json([
                'totalActiveUsers' => $data['totalActiveUsers'],
                'totalMovies'      => $data['totalMovies'],
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
                'selectedMovieId'  => $data['selectedMovieId'],
                'movieName'        => $movieId && $data['movies']->firstWhere('id', $movieId) ? $data['movies']->firstWhere('id', $movieId)->title : 'Tất cả phim',
                'chartData'        => $data['chartData'],
                'html_revenue_table' => view('manager.dashboard.partials.revenue_table', $data)->render(),
                'html_top_combos'    => view('manager.dashboard.partials.top_combos', $data)->render(),
                'html_top_movies'    => view('manager.dashboard.partials.top_movies', $data)->render(),
                'html_movie_statistics' => view('manager.dashboard.partials.movie_statistics', $data)->render(),
            ]);
        }

        return view('manager.dashboard.index', $data);
    }
}
