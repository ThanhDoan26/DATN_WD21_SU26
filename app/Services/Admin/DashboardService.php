<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\Movie;
use App\Models\Cinema;
use App\Models\Showtime;
use App\Models\BookedSeat;

class DashboardService
{
    /**
     * Lấy các thống kê tổng quan cho Admin Dashboard
     *
     * @return array
     */
    public function getStatistics(): array
    {
        // 1. Tổng số người dùng (đang hoạt động)
        $totalActiveUsers = User::where('status', 'ACTIVE')->count();

        // 2. Tổng số phim (chưa bị xóa mềm, count tự động bỏ qua trashed)
        $totalMovies = Movie::count();

        // 3. Tổng số rạp (chưa bị xóa mềm)
        $totalCinemas = Cinema::count();

        // 4. Tổng số suất chiếu (chưa bị xóa mềm)
        $totalShowtimes = Showtime::count();

        // 5. Tổng số vé đã bán (thuộc các booking đã thanh toán thành công)
        $totalTicketsSold = BookedSeat::whereHas('booking', function ($query) {
            $query->where('status', 'Paid');
        })->count();

        return [
            'totalActiveUsers' => $totalActiveUsers,
            'totalMovies'      => $totalMovies,
            'totalCinemas'     => $totalCinemas,
            'totalShowtimes'   => $totalShowtimes,
            'totalTicketsSold' => $totalTicketsSold,
        ];
    }
}
