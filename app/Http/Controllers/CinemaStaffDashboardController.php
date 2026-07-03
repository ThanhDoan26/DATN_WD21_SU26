<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CinemaStaffDashboardController extends Controller
{
    /**
     * Hiển thị trang Dashboard cho Cinema Staff
     */
    public function index()
    {
        return view('staff.dashboard.index');
    }
}
