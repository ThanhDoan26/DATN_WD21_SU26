<?php

namespace App\Services\Manager;

use App\Models\User;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use App\Models\BookedSeat;

class DashboardService
{
    /**
     * Lấy các thống kê tổng quan cho Manager Dashboard
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $user = auth()->user();
        $cinemaId = $user->cinema_id;

        // 1. Tổng số người dùng (đang hoạt động) thuộc rạp (thường là nhân sự của rạp)
        $totalActiveUsers = User::where('status', 'ACTIVE')
            ->where('cinema_id', $cinemaId)
            ->count();

        // 2. Tổng số phim (danh mục phim là toàn hệ thống)
        $totalMovies = Movie::count();

        // 3. Tổng số phòng chiếu của rạp
        $totalRooms = Room::where('cinema_id', $cinemaId)->count();

        // 4. Tổng số suất chiếu của rạp
        $totalShowtimes = Showtime::whereHas('room', function($q) use ($cinemaId) {
            $q->where('cinema_id', $cinemaId);
        })->count();

        // 5. Tổng số vé đã bán (thuộc các booking đã thanh toán thành công của rạp)
        $totalTicketsSold = BookedSeat::whereHas('booking.showtime.room', function ($q) use ($cinemaId) {
            $q->where('cinema_id', $cinemaId);
        })->whereHas('booking', function ($query) {
            $query->where('status', 'Paid');
        })->count();

        return [
            'totalActiveUsers' => $totalActiveUsers,
            'totalMovies'      => $totalMovies,
            'totalRooms'       => $totalRooms,
            'totalShowtimes'   => $totalShowtimes,
            'totalTicketsSold' => $totalTicketsSold,
        ];
    }
}
