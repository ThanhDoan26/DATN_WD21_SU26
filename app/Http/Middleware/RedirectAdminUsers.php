<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * RedirectAdminUsers Middleware
 * ========================================
 * Tự động chuyển hướng admin users tới admin dashboard
 * thay vì dashboard bình thường
 */
class RedirectAdminUsers
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Nếu user đã login
        if (auth()->check()) {
            $user = auth()->user();

            // Nếu user đang cố gắng truy cập /dashboard → redirect về dashboard đúng role
            if ($request->path() === 'dashboard') {
                if ($user->isAdmin()) {
                    return redirect()->route('admin.dashboard');
                }
                if ($user->isManager()) {
                    return redirect()->route('manager.dashboard');
                }
                if ($user->isStaff()) {
                    return redirect()->route('staff.dashboard');
                }
            }

            // Nếu user không phải admin nhưng cố gắng truy cập /admin → redirect về dashboard đúng role
            if (!$user->isAdmin() && str_starts_with($request->path(), 'admin')) {
                if ($user->isManager()) {
                    return redirect()->route('manager.dashboard');
                }
                if ($user->isStaff()) {
                    return redirect()->route('staff.dashboard');
                }
                return redirect('/');
            }
        }

        return $next($request);
    }
}
