<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\Movie;
use App\Models\Cinema;
use App\Models\Showtime;
use App\Models\BookedSeat;
use App\Models\Booking;
use Carbon\Carbon;

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
        int $week = null
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
        $totalShowtimes = $totalShowtimesQuery->count();

        $paidStatuses = ['Paid', 'Used'];

        // 5. Tổng số vé đã bán (thuộc các booking đã thanh toán hoặc đã sử dụng)
        $totalTicketsSold = BookedSeat::whereHas('booking', function ($query) use ($paidStatuses, $cinemaId) {
            $query->whereIn('status', $paidStatuses);
            if ($cinemaId) {
                $query->whereHas('showtime.room', function ($q) use ($cinemaId) {
                    $q->where('cinema_id', $cinemaId);
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
        $allTimeRevenue = $allTimeRevenueQuery->sum('total_price');

        $dailyRevenueQuery = Booking::whereIn('status', $paidStatuses)
            ->whereDate('payment_time', $today);
        if ($cinemaId) {
            $dailyRevenueQuery->whereHas('showtime.room', function ($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            });
        }
        $dailyRevenue = $dailyRevenueQuery->sum('total_price');

        $monthlyRevenueQuery = Booking::whereIn('status', $paidStatuses)
            ->whereYear('payment_time', $selectedYear)
            ->whereMonth('payment_time', $selectedMonth);
        if ($cinemaId) {
            $monthlyRevenueQuery->whereHas('showtime.room', function ($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            });
        }
        $monthlyRevenue = $monthlyRevenueQuery->sum('total_price');

        $yearlyRevenueQuery = Booking::whereIn('status', $paidStatuses)
            ->whereYear('payment_time', $selectedYear);
        if ($cinemaId) {
            $yearlyRevenueQuery->whereHas('showtime.room', function ($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
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

        $bookingsQuery = Booking::with(['user', 'showtime.movie', 'showtime.room.cinema', 'bookedSeats'])
            ->whereIn('status', $paidStatuses);
        if ($cinemaId) {
            $bookingsQuery->whereHas('showtime.room', function ($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            });
        }

        // Áp dụng điều kiện thời gian cho cả kỳ chọn và bookings chi tiết
        if ($selectedReportType === 'date') {
            $fDate = $fromDate ?? Carbon::now()->startOfMonth()->toDateString();
            $tDate = $toDate ?? Carbon::now()->toDateString();
            $dateRange = [Carbon::parse($fDate)->startOfDay(), Carbon::parse($tDate)->endOfDay()];
            
            $periodRevenueQuery->whereBetween('payment_time', $dateRange);
            $bookingsQuery->whereBetween('payment_time', $dateRange);
        } elseif ($selectedReportType === 'week') {
            $startOfWeek = Carbon::now()->setISODate($selectedYear, $selectedWeek)->startOfWeek();
            $endOfWeek = Carbon::now()->setISODate($selectedYear, $selectedWeek)->endOfWeek();
            $dateRange = [$startOfWeek, $endOfWeek];

            $periodRevenueQuery->whereBetween('payment_time', $dateRange);
            $bookingsQuery->whereBetween('payment_time', $dateRange);
        } elseif ($selectedReportType === 'month') {
            $periodRevenueQuery->whereYear('payment_time', $selectedYear)->whereMonth('payment_time', $selectedMonth);
            $bookingsQuery->whereYear('payment_time', $selectedYear)->whereMonth('payment_time', $selectedMonth);
        } elseif ($selectedReportType === 'year') {
            $periodRevenueQuery->whereYear('payment_time', $selectedYear);
            $bookingsQuery->whereYear('payment_time', $selectedYear);
        }

        $periodRevenue = $periodRevenueQuery->sum('total_price');
        $detailedBookings = $bookingsQuery->orderBy('payment_time', 'desc')->get();

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
        ];
    }
}
