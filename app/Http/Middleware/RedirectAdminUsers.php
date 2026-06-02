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

            // Nếu user là admin
            if ($user->isAdmin()) {
                // Nếu user đang cố gắng truy cập dashboard bình thường → redirect admin dashboard
                if ($request->path() === 'dashboard') {
                    return redirect()->route('admin.dashboard');
                }
            } else {
                // Nếu user không phải admin nhưng cố gắng truy cập /admin → redirect dashboard
                if (str_starts_with($request->path(), 'admin')) {
                    return redirect()->route('dashboard');
                }
            }
        }

        return $next($request);
    }
}
