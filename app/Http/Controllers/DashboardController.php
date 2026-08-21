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

        // Chuyển hướng về dashboard tương ứng vai trò
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->isManager()) {
            return redirect()->route('manager.dashboard');
        }
        if ($user->isStaff()) {
            return redirect()->route('staff.dashboard');
        }

        $showtimes = collect();
        $date = $request->input('date', Carbon::today()->toDateString()); // Mặc định hôm nay

        return view('dashboard', compact('showtimes', 'date'));
    }
}
