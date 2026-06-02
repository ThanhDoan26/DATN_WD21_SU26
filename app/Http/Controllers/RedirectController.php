<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * RedirectController
 * ========================================
 * Controller xử lý redirect dựa trên role của user
 */
class RedirectController extends Controller
{
    /**
     * Redirect người dùng tới trang thích hợp dựa trên role
     */
    public function handleLogin(Request $request)
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Nếu user là admin → redirect tới admin dashboard
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            // Nếu user là staff/manager → redirect tới staff dashboard (TODO)
            if ($user->isStaff() || $user->isManager()) {
                return redirect()->route('dashboard');
            }
        }

        // Mặc định redirect tới dashboard
        return redirect()->route('dashboard');
    }
}
