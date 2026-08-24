<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Showtime;
use App\Models\Room;
use App\Models\Cinema;
use App\Models\Booking;

class CheckCinemaAssignment
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->cinema_id) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Nhân viên không được phân công rạp.'], 403);
            }
            abort(403, 'Nhân viên không được phân công rạp.');
        }

        // 1. Kiểm tra showtime (từ route parameter hoặc input/query)
        $showtime = $request->route()?->parameter('showtime');
        if (!$showtime) {
            $showtimeId = $request->input('showtime_id') ?? $request->query('showtime_id');
            if ($showtimeId && (is_numeric($showtimeId) || is_string($showtimeId))) {
                $showtime = Showtime::with('room')->find($showtimeId);
            }
        } elseif (!$showtime instanceof Showtime) {
            $showtime = Showtime::with('room')->find($showtime);
        }

        if ($showtime && $showtime instanceof Showtime) {
            $showtime->loadMissing('room');
            if (!$showtime->room || $showtime->room->cinema_id !== $user->cinema_id) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Bạn không được quyền truy cập suất chiếu của rạp khác.'], 403);
                }
                abort(403, 'Bạn không được quyền truy cập suất chiếu của rạp khác.');
            }
        }

        // 2. Kiểm tra room (từ route parameter hoặc input/query)
        $room = $request->route()?->parameter('room');
        if (!$room) {
            $roomId = $request->input('room_id') ?? $request->query('room_id');
            if ($roomId && (is_numeric($roomId) || is_string($roomId))) {
                $room = Room::find($roomId);
            }
        } elseif (!$room instanceof Room) {
            $room = Room::find($room);
        }

        if ($room && $room instanceof Room) {
            if ($room->cinema_id !== $user->cinema_id) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Bạn không được quyền truy cập phòng chiếu của rạp khác.'], 403);
                }
                abort(403, 'Bạn không được quyền truy cập phòng chiếu của rạp khác.');
            }
        }

        // 3. Kiểm tra cinema (từ route parameter hoặc input/query)
        $cinema = $request->route()?->parameter('cinema');
        if (!$cinema) {
            $cinemaParamId = $request->input('cinema_id') ?? $request->query('cinema_id');
            if ($cinemaParamId && (is_numeric($cinemaParamId) || is_string($cinemaParamId))) {
                $cinema = Cinema::find($cinemaParamId);
            }
        } elseif (!$cinema instanceof Cinema) {
            $cinema = Cinema::find($cinema);
        }

        if ($cinema && $cinema instanceof Cinema) {
            if ($cinema->id !== $user->cinema_id) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Bạn không được quyền truy cập rạp khác.'], 403);
                }
                abort(403, 'Bạn không được quyền truy cập rạp khác.');
            }
        }

        // 4. Kiểm tra booking (từ route parameter hoặc input/query)
        $booking = $request->route()?->parameter('booking');
        if (!$booking) {
            $bookingId = $request->input('booking_id') ?? $request->query('booking_id');
            if ($bookingId && (is_numeric($bookingId) || is_string($bookingId))) {
                $booking = Booking::with('showtime.room')->find($bookingId);
            }
        } elseif (!$booking instanceof Booking) {
            $booking = Booking::with('showtime.room')->find($booking);
        }

        if ($booking && $booking instanceof Booking) {
            $booking->loadMissing('showtime.room');
            if ($booking->showtime && $booking->showtime->room && $booking->showtime->room->cinema_id !== $user->cinema_id) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Bạn không được quyền truy cập đơn đặt vé của rạp khác.'], 403);
                }
                abort(403, 'Bạn không được quyền truy cập đơn đặt vé của rạp khác.');
            }
        }

        return $next($request);
    }
}
