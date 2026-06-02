<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cinema;

/**
 * CinemaController
 * ========================================
 * Controller quản lý cinemas - Read-only (Index only)
 */
class CinemaController extends AdminController
{
    /**
     * Display a listing of cinemas
     */
    public function index()
    {
        $cinemas = Cinema::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.cinemas.index', ['cinemas' => $cinemas]);
    }
}
