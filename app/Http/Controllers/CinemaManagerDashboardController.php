<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CinemaManagerDashboardController extends Controller
{
    /**
     * Hiển thị trang Dashboard cho Cinema Manager
     */
    public function index()
    {
        return view('manager.dashboard.index');
    }
}
