<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\Movie;
use App\Models\Cinema;
use App\Models\Showtime;
use App\Models\BookedSeat;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class DashboardService
{
    /**
     * Lấy các thống kê tổng quan cho Admin Dashboard
     *
     * @return array
     */
    public function getStatistics(
        int $month = null,
        int $year = null,
        int $cinemaId = null,
        string $reportType = 'month',
        string $fromDate = null,
        string $toDate = null,
        int $week = null,
        int $movieId = null
    ): array
    {
        // 1. Tổng số người dùng (đang hoạt động)
        $totalActiveUsers = User::where('status', 'ACTIVE')->count();

        // 2. Tổng số phim (chưa bị xóa mềm, count tự động bỏ qua trashed)
        $totalMovies = Movie::count();

        // 3. Tổng số rạp (chưa bị xóa mềm)
        $totalCinemas = Cinema::count();

        // 4. Tổng số suất chiếu (chưa bị xóa mềm)
        $totalShowtimesQuery = Showtime::query();
        if ($cinemaId) {
            $totalShowtimesQuery->whereHas('room', function ($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            });
        }
        if ($movieId) {
            $totalShowtimesQuery->where('movie_id', $movieId);
        }
        $totalShowtimes = $totalShowtimesQuery->count();

        $paidStatuses = ['Paid', 'Used'];

        // 5. Tổng số vé đã bán (thuộc các booking đã thanh toán hoặc đã sử dụng)
        $totalTicketsSold = BookedSeat::whereHas('booking', function ($query) use ($paidStatuses, $cinemaId, $movieId) {
            $query->whereIn('status', $paidStatuses);
            if ($cinemaId) {
                $query->whereHas('showtime.room', function ($q) use ($cinemaId) {
                    $q->where('cinema_id', $cinemaId);
                });
            }
            if ($movieId) {
                $query->whereHas('showtime', function ($q) use ($movieId) {
                    $q->where('movie_id', $movieId);
                });
            }
        })->count();

        // 6. Doanh thu
        $today = Carbon::today();

        $selectedYear = $year ?? $today->year;
        $selectedMonth = $month ?? $today->month;
        $selectedWeek = $week ?? $today->weekOfYear;
        $selectedReportType = $reportType ?? 'month';

        $allTimeRevenueQuery = Booking::whereIn('status', $paidStatuses);
        if ($cinemaId) {
            $allTimeRevenueQuery->whereHas('showtime.room', function ($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            });
        }
        if ($movieId) {
            $allTimeRevenueQuery->whereHas('showtime', function ($q) use ($movieId) {
                $q->where('movie_id', $movieId);
            });
        }
        $allTimeRevenue = $allTimeRevenueQuery->sum('total_price');

        $dailyRevenueQuery = Booking::whereIn('status', $paidStatuses)
            ->whereDate(DB::raw('COALESCE(payment_time, booking_time, created_at)'), $today);
        if ($cinemaId) {
            $dailyRevenueQuery->whereHas('showtime.room', function ($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            });
        }
        if ($movieId) {
            $dailyRevenueQuery->whereHas('showtime', function ($q) use ($movieId) {
                $q->where('movie_id', $movieId);
            });
        }
        $dailyRevenue = $dailyRevenueQuery->sum('total_price');

        $monthlyRevenueQuery = Booking::whereIn('status', $paidStatuses)
            ->whereYear(DB::raw('COALESCE(payment_time, booking_time, created_at)'), $selectedYear)
            ->whereMonth(DB::raw('COALESCE(payment_time, booking_time, created_at)'), $selectedMonth);
        if ($cinemaId) {
            $monthlyRevenueQuery->whereHas('showtime.room', function ($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            });
        }
        if ($movieId) {
            $monthlyRevenueQuery->whereHas('showtime', function ($q) use ($movieId) {
                $q->where('movie_id', $movieId);
            });
        }
        $monthlyRevenue = $monthlyRevenueQuery->sum('total_price');

        $yearlyRevenueQuery = Booking::whereIn('status', $paidStatuses)
            ->whereYear(DB::raw('COALESCE(payment_time, booking_time, created_at)'), $selectedYear);
        if ($cinemaId) {
            $yearlyRevenueQuery->whereHas('showtime.room', function ($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            });
        }
        if ($movieId) {
            $yearlyRevenueQuery->whereHas('showtime', function ($q) use ($movieId) {
                $q->where('movie_id', $movieId);
            });
        }
        $yearlyRevenue = $yearlyRevenueQuery->sum('total_price');

        // 7. Tính toán doanh thu kỳ chọn (periodRevenue) và lọc Booking chi tiết
        $periodRevenueQuery = Booking::whereIn('status', $paidStatuses);
        if ($cinemaId) {
            $periodRevenueQuery->whereHas('showtime.room', function ($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            });
        }
        if ($movieId) {
            $periodRevenueQuery->whereHas('showtime', function ($q) use ($movieId) {
                $q->where('movie_id', $movieId);
            });
        }

        $bookingsQuery = Booking::with(['user', 'showtime.movie', 'showtime.room.cinema', 'bookedSeats'])
            ->whereIn('status', $paidStatuses);
        if ($cinemaId) {
            $bookingsQuery->whereHas('showtime.room', function ($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            });
        }
        if ($movieId) {
            $bookingsQuery->whereHas('showtime', function ($q) use ($movieId) {
                $q->where('movie_id', $movieId);
            });
        }

        $dateRange = null;

        // Áp dụng điều kiện thời gian cho cả kỳ chọn và bookings chi tiết
        if ($selectedReportType === 'date') {
            $fDate = $fromDate ?? Carbon::now()->startOfMonth()->toDateString();
            $tDate = $toDate ?? Carbon::now()->toDateString();
            $dateRange = [Carbon::parse($fDate)->startOfDay(), Carbon::parse($tDate)->endOfDay()];
            
            $periodRevenueQuery->whereBetween(DB::raw('COALESCE(payment_time, booking_time, created_at)'), $dateRange);
            $bookingsQuery->whereBetween(DB::raw('COALESCE(payment_time, booking_time, created_at)'), $dateRange);
        } elseif ($selectedReportType === 'week') {
            $startOfWeek = Carbon::now()->setISODate($selectedYear, $selectedWeek)->startOfWeek();
            $endOfWeek = Carbon::now()->setISODate($selectedYear, $selectedWeek)->endOfWeek();
            $dateRange = [$startOfWeek, $endOfWeek];

            $periodRevenueQuery->whereBetween(DB::raw('COALESCE(payment_time, booking_time, created_at)'), $dateRange);
            $bookingsQuery->whereBetween(DB::raw('COALESCE(payment_time, booking_time, created_at)'), $dateRange);
        } elseif ($selectedReportType === 'month') {
            $periodRevenueQuery->whereYear(DB::raw('COALESCE(payment_time, booking_time, created_at)'), $selectedYear)->whereMonth(DB::raw('COALESCE(payment_time, booking_time, created_at)'), $selectedMonth);
            $bookingsQuery->whereYear(DB::raw('COALESCE(payment_time, booking_time, created_at)'), $selectedYear)->whereMonth(DB::raw('COALESCE(payment_time, booking_time, created_at)'), $selectedMonth);
        } elseif ($selectedReportType === 'year') {
            $periodRevenueQuery->whereYear(DB::raw('COALESCE(payment_time, booking_time, created_at)'), $selectedYear);
            $bookingsQuery->whereYear(DB::raw('COALESCE(payment_time, booking_time, created_at)'), $selectedYear);
        }

        $periodRevenue = $periodRevenueQuery->sum('total_price');
        $detailedBookings = $bookingsQuery->orderBy(DB::raw('COALESCE(payment_time, booking_time, created_at)'), 'desc')->get();

        // 8. Top phim bán chạy (Top Movies)
        $topMoviesQuery = DB::table('movies')
            ->join('showtimes', 'movies.id', '=', 'showtimes.movie_id')
            ->join('bookings', 'showtimes.id', '=', 'bookings.showtime_id')
            ->join('booked_seats', 'bookings.id', '=', 'booked_seats.booking_id')
            ->whereIn('bookings.status', $paidStatuses)
            ->whereNull('movies.deleted_at');

        if ($cinemaId) {
            $topMoviesQuery->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
                           ->where('rooms.cinema_id', $cinemaId);
        }
        if ($movieId) {
            $topMoviesQuery->where('movies.id', $movieId);
        }

        // Áp dụng điều kiện thời gian
        if ($selectedReportType === 'date') {
            $topMoviesQuery->whereBetween(DB::raw('COALESCE(bookings.payment_time, bookings.booking_time, bookings.created_at)'), $dateRange);
        } elseif ($selectedReportType === 'week') {
            $topMoviesQuery->whereBetween(DB::raw('COALESCE(bookings.payment_time, bookings.booking_time, bookings.created_at)'), $dateRange);
        } elseif ($selectedReportType === 'month') {
            $topMoviesQuery->whereYear(DB::raw('COALESCE(bookings.payment_time, bookings.booking_time, bookings.created_at)'), $selectedYear)->whereMonth(DB::raw('COALESCE(bookings.payment_time, bookings.booking_time, bookings.created_at)'), $selectedMonth);
        } elseif ($selectedReportType === 'year') {
            $topMoviesQuery->whereYear(DB::raw('COALESCE(bookings.payment_time, bookings.booking_time, bookings.created_at)'), $selectedYear);
        }

        $topMovies = $topMoviesQuery->select(
                'movies.id',
                'movies.title',
                'movies.poster_url',
                DB::raw('COUNT(booked_seats.id) as total_tickets'),
                DB::raw('SUM(booked_seats.price_at_booking) as total_revenue')
            )
            ->groupBy('movies.id', 'movies.title', 'movies.poster_url')
            ->orderByDesc('total_tickets')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get();

        $movieStatisticsQuery = DB::table('movies')
            ->join('showtimes', 'movies.id', '=', 'showtimes.movie_id')
            ->join('bookings', 'showtimes.id', '=', 'bookings.showtime_id')
            ->join('booked_seats', 'bookings.id', '=', 'booked_seats.booking_id')
            ->whereIn('bookings.status', $paidStatuses)
            ->whereNull('movies.deleted_at');

        if ($cinemaId) {
            $movieStatisticsQuery->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
                ->where('rooms.cinema_id', $cinemaId);
        }
        if ($movieId) {
            $movieStatisticsQuery->where('movies.id', $movieId);
        }

        if ($selectedReportType === 'date') {
            $movieStatisticsQuery->whereBetween(DB::raw('COALESCE(bookings.payment_time, bookings.booking_time, bookings.created_at)'), $dateRange);
        } elseif ($selectedReportType === 'week') {
            $movieStatisticsQuery->whereBetween(DB::raw('COALESCE(bookings.payment_time, bookings.booking_time, bookings.created_at)'), $dateRange);
        } elseif ($selectedReportType === 'month') {
            $movieStatisticsQuery->whereYear(DB::raw('COALESCE(bookings.payment_time, bookings.booking_time, bookings.created_at)'), $selectedYear)->whereMonth(DB::raw('COALESCE(bookings.payment_time, bookings.booking_time, bookings.created_at)'), $selectedMonth);
        } elseif ($selectedReportType === 'year') {
            $movieStatisticsQuery->whereYear(DB::raw('COALESCE(bookings.payment_time, bookings.booking_time, bookings.created_at)'), $selectedYear);
        }

        $movieStatistics = $movieStatisticsQuery->select(
                'movies.id',
                'movies.title',
                'movies.poster_url',
                DB::raw('COUNT(booked_seats.id) as total_tickets'),
                DB::raw('SUM(booked_seats.price_at_booking) as total_revenue'),
                DB::raw('COUNT(DISTINCT showtimes.id) as total_showtimes')
            )
            ->groupBy('movies.id', 'movies.title', 'movies.poster_url')
            ->orderByDesc('total_tickets')
            ->orderByDesc('total_revenue')
            ->take(15)
            ->get();

        // 9. Dữ liệu biểu đồ (Chart Data)
        $chartBaseQuery = Booking::whereIn('status', $paidStatuses);
        if ($cinemaId) {
            $chartBaseQuery->whereHas('showtime.room', function ($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            });
        }
        if ($movieId) {
            $chartBaseQuery->whereHas('showtime', function ($q) use ($movieId) {
                $q->where('movie_id', $movieId);
            });
        }

        $chartData = [
            '7days' => ['labels' => [], 'revenue' => [], 'tickets' => []],
            '30days' => ['labels' => [], 'revenue' => [], 'tickets' => []],
            '12months' => ['labels' => [], 'revenue' => [], 'tickets' => []],
        ];

        // 7 Days
        $startDate7 = Carbon::today()->subDays(6)->startOfDay();
        $endDate7 = Carbon::today()->endOfDay();
        $bookings7Days = (clone $chartBaseQuery)->whereBetween('payment_time', [$startDate7, $endDate7])
            ->select('payment_time', 'total_price', 'id')
            ->withCount('bookedSeats')->get();
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartData['7days']['labels'][] = $date->format('d/m');
            $dayBookings = $bookings7Days->filter(function($b) use ($date) {
                return Carbon::parse($b->payment_time)->format('Y-m-d') === $date->format('Y-m-d');
            });
            $chartData['7days']['revenue'][] = $dayBookings->sum('total_price');
            $chartData['7days']['tickets'][] = $dayBookings->sum('booked_seats_count');
        }

        // 30 Days (4 Weeks)
        $startDate30 = Carbon::today()->subDays(27)->startOfDay();
        $endDate30 = Carbon::today()->endOfDay();
        $bookings30Days = (clone $chartBaseQuery)->whereBetween('payment_time', [$startDate30, $endDate30])
            ->select('payment_time', 'total_price', 'id')
            ->withCount('bookedSeats')->get();
        
        for ($i = 3; $i >= 0; $i--) {
            $startWeek = Carbon::today()->subDays(($i * 7) + 6)->startOfDay();
            $endWeek = Carbon::today()->subDays($i * 7)->endOfDay();
            $chartData['30days']['labels'][] = $startWeek->format('d/m') . ' - ' . $endWeek->format('d/m');
            $weekBookings = $bookings30Days->filter(function($b) use ($startWeek, $endWeek) {
                return Carbon::parse($b->payment_time)->between($startWeek, $endWeek);
            });
            $chartData['30days']['revenue'][] = $weekBookings->sum('total_price');
            $chartData['30days']['tickets'][] = $weekBookings->sum('booked_seats_count');
        }

        // 12 Months
        $bookings12Months = (clone $chartBaseQuery)->whereYear('payment_time', $selectedYear)
            ->select('payment_time', 'total_price', 'id')
            ->withCount('bookedSeats')->get();
            
        for ($m = 1; $m <= 12; $m++) {
            $chartData['12months']['labels'][] = 'Thg ' . $m;
            $monthBookings = $bookings12Months->filter(function($b) use ($m) {
                return Carbon::parse($b->payment_time)->month === $m;
            });
            $chartData['12months']['revenue'][] = $monthBookings->sum('total_price');
            $chartData['12months']['tickets'][] = $monthBookings->sum('booked_seats_count');
        }

        return [
            'totalActiveUsers' => $totalActiveUsers,
            'totalMovies'      => $totalMovies,
            'totalCinemas'     => $totalCinemas,
            'totalShowtimes'   => $totalShowtimes,
            'totalTicketsSold' => $totalTicketsSold,
            'totalRevenue'     => $allTimeRevenue,
            'dailyRevenue'     => $dailyRevenue,
            'monthlyRevenue'   => $monthlyRevenue,
            'yearlyRevenue'    => $yearlyRevenue,
            'periodRevenue'    => $periodRevenue,
            'selectedMonth'    => $selectedMonth,
            'selectedYear'     => $selectedYear,
            'selectedWeek'     => $selectedWeek,
            'selectedReportType' => $selectedReportType,
            'fromDate'         => $fromDate ?? Carbon::now()->startOfMonth()->toDateString(),
            'toDate'           => $toDate ?? Carbon::now()->toDateString(),
            'selectedCinemaId' => $cinemaId,
            'detailedBookings' => $detailedBookings,
            'topMovies'        => $topMovies,
            'movieStatistics'  => $movieStatistics,
            'chartData'        => $chartData,
        ];
    }
}
