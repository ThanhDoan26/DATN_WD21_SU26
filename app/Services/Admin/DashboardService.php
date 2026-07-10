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
    public function getStatistics(int $month = null, int $year = null): array
    {
        // 1. Tổng số người dùng (đang hoạt động)
        $totalActiveUsers = User::where('status', 'ACTIVE')->count();

        // 2. Tổng số phim (chưa bị xóa mềm, count tự động bỏ qua trashed)
        $totalMovies = Movie::count();

        // 3. Tổng số rạp (chưa bị xóa mềm)
        $totalCinemas = Cinema::count();

        // 4. Tổng số suất chiếu (chưa bị xóa mềm)
        $totalShowtimes = Showtime::count();

        $paidStatuses = ['Paid', 'Used'];

        // 5. Tổng số vé đã bán (thuộc các booking đã thanh toán hoặc đã sử dụng)
        $totalTicketsSold = BookedSeat::whereHas('booking', function ($query) use ($paidStatuses) {
            $query->whereIn('status', $paidStatuses);
        })->count();

        // 6. Doanh thu
        $today = Carbon::today();

        $selectedYear = $year ?? $today->year;
        $selectedMonth = $month ?? $today->month;

        $allTimeRevenue = Booking::whereIn('status', $paidStatuses)
            ->sum('total_price');

        $dailyRevenue = Booking::whereIn('status', $paidStatuses)
            ->whereDate('payment_time', $today)
            ->sum('total_price');

        $monthlyRevenue = Booking::whereIn('status', $paidStatuses)
            ->whereYear('payment_time', $selectedYear)
            ->whereMonth('payment_time', $selectedMonth)
            ->sum('total_price');

        $yearlyRevenue = Booking::whereIn('status', $paidStatuses)
            ->whereYear('payment_time', $selectedYear)
            ->sum('total_price');

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
        ];
    }
}
