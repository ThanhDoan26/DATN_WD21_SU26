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
    public function getStatistics(int $month = null, int $year = null, int $cinemaId = null): array
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

        $bookingsQuery = Booking::with(['user', 'showtime.movie', 'showtime.room.cinema', 'bookedSeats'])
            ->whereIn('status', $paidStatuses);

        if ($cinemaId) {
            $bookingsQuery->whereHas('showtime.room', function ($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            });
        }

        if ($selectedMonth) {
            $bookingsQuery->whereMonth('payment_time', $selectedMonth);
        }

        if ($selectedYear) {
            $bookingsQuery->whereYear('payment_time', $selectedYear);
        }

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
            'selectedMonth'    => $selectedMonth,
            'selectedYear'     => $selectedYear,
            'selectedCinemaId' => $cinemaId,
            'detailedBookings' => $detailedBookings,
        ];
    }
}
