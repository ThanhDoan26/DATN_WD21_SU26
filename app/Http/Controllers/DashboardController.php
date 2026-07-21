<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Showtime;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Hiển thị trang Dashboard.
     * Đối với Manager, hiển thị danh sách thống kê suất chiếu của rạp.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Nếu không đăng nhập
        if (!$user) {
            return redirect()->route('login');
        }

        $showtimes = collect();
        $date = $request->input('date', Carbon::today()->toDateString()); // Mặc định hôm nay

        // Nếu là Manager, lấy danh sách thống kê suất chiếu của rạp do manager này quản lý
        if ($user->isManager() && $user->cinema_id) {
            $showtimes = Showtime::with(['movie', 'room'])
                ->whereHas('room', function ($query) use ($user) {
                    $query->where('cinema_id', $user->cinema_id);
                })
                ->whereDate('start_time', $date)
                ->orderBy('start_time', 'asc')
                ->paginate(15);
        }

        return view('dashboard', compact('showtimes', 'date'));
    }
}
