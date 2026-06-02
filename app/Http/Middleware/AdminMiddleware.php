<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AdminMiddleware
 * ========================================
 * Middleware kiểm tra xem user có phải admin không
 */
class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Check if user has admin role
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access. Admin role required.');
        }

        return $next($request);
    }
}
