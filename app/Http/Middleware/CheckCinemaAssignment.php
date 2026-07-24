<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckCinemaAssignment
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->cinema_id) {
            abort(403, 'Nhân viên chưa được phân công rạp.');
        }

        $showtime = $request->route()?->parameter('showtime');
        if ($showtime) {
            if (!isset($showtime->room) || $showtime->room->cinema_id !== $user->cinema_id) {
                abort(403, 'Bạn không có quyền truy cập suất chiếu của rạp khác.');
            }
        }

        $room = $request->route()?->parameter('room');
        if ($room && $room->cinema_id !== $user->cinema_id) {
            abort(403, 'Bạn không có quyền truy cập phòng chiếu của rạp khác.');
        }

        $cinema = $request->route()?->parameter('cinema');
        if ($cinema && $cinema->id !== $user->cinema_id) {
            abort(403, 'Bạn không có quyền truy cập rạp khác.');
        }

        return $next($request);
    }
}
