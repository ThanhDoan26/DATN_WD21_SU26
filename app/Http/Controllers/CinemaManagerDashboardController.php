<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Manager\DashboardService;

class CinemaManagerDashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Hiển thị trang Dashboard cho Cinema Manager
     */
    public function index()
    {
        $statistics = $this->dashboardService->getStatistics();

        return view('manager.dashboard.index', compact('statistics'));
    }
}
