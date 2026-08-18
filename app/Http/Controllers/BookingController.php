<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Cinema;
use App\Models\Showtime;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Bước 1: Chọn cụm rạp
     * Hiển thị danh sách rạp có suất chiếu của phim được chọn
     */
    public function selectCinema(Movie $movie): View
    {
        // Lấy danh sách rạp có suất chiếu cho phim này
        $cinemas = Cinema::whereHas('rooms', function ($query) use ($movie) {
            $query->whereHas('showtimes', function ($q) use ($movie) {
                $q->where('movie_id', $movie->id)
                  ->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
                  ->where('start_time', '>', now());
            });
        })
        ->with(['rooms' => function ($query) use ($movie) {
            $query->whereHas('showtimes', function ($q) use ($movie) {
                $q->where('movie_id', $movie->id)
                  ->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
                  ->where('start_time', '>', now());
            });
        }])
        ->get();

        return view('booking.select-cinema', [
            'movie' => $movie,
            'cinemas' => $cinemas,
        ]);
    }

    /**
     * Bước 2 & 3: Chọn ngày và suất chiếu
     */
    public function selectDatesAndShowtimes(Movie $movie, Cinema $cinema): View
    {
        return view('booking.select-dates-and-showtimes', [
            'movie' => $movie,
            'cinema' => $cinema,
        ]);
    }

    /**
     * API: Lấy danh sách ngày chiếu
     * Bước 2: Chọn ngày chiếu - chỉ hiển thị ngày có suất chiếu
     */
    public function getDates(Request $request): JsonResponse
    {
        $movieId = $request->get('movie_id');
        $cinemaId = $request->get('cinema_id');

        // Validate
        if (!$movieId || !$cinemaId) {
            return response()->json(['error' => 'Missing parameters'], 400);
        }

        // Lấy danh sách ngày chiếu theo phim + rạp
        $dates = Showtime::where('movie_id', $movieId)
            ->whereHas('room', function ($query) use ($cinemaId) {
                $query->where('cinema_id', $cinemaId);
            })
            ->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
            ->where('start_time', '>', now())
            ->selectRaw('DATE(start_time) as date')
            ->distinct()
            ->pluck('date')
            ->sortBy(function ($date) {
                return strtotime($date);
            })
            ->values();

        return response()->json([
            'data' => $dates,
            'message' => 'Danh sách ngày chiếu',
        ]);
    }

    /**
     * API: Lấy danh sách suất chiếu
     * Bước 3: Chọn suất chiếu theo phim, rạp, ngày
     */
    public function getShowtimes(Request $request): JsonResponse
    {
        $movieId = $request->get('movie_id');
        $cinemaId = $request->get('cinema_id');
        $date = $request->get('date');

        // Validate
        if (!$movieId || !$cinemaId || !$date) {
            return response()->json(['error' => 'Missing parameters'], 400);
        }

        // Lấy danh sách suất chiếu theo phim + rạp + ngày
        $showtimes = Showtime::where('movie_id', $movieId)
            ->whereHas('room', function ($query) use ($cinemaId) {
                $query->where('cinema_id', $cinemaId);
            })
            ->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
            ->whereDate('start_time', $date)
            ->where('start_time', '>', now())
            ->with(['room' => function ($q) {
                $q->select('id', 'name', 'format', 'cinema_id')->with('cinema:id,name');
            }])
            ->select('id', 'room_id', 'start_time', 'end_time', 'status')
            ->orderBy('start_time')
            ->get()
            ->map(function ($showtime) {
                return [
                    'id' => $showtime->id,
                    'time' => $showtime->start_time->format('H:i'),
                    'start_time' => $showtime->start_time->toIso8601String(),
                    'end_time' => $showtime->end_time->toIso8601String(),
                    'room_name' => $showtime->room->name,
                    'room_format' => $showtime->room->format,
                    'cinema_name' => $showtime->room->cinema->name,
                    'available_seats' => $this->getAvailableSeatsCount($showtime->id),
                ];
            });

        return response()->json([
            'data' => $showtimes,
            'message' => 'Danh sách suất chiếu',
        ]);
    }

    /**
     * Bước 4: Chọn ghế và tiến hành đặt vé
     * Hiển thị sơ đồ ghế của suất chiếu
     */
    public function selectSeats(Showtime $showtime)
    {
        // Kiểm tra showtime có hợp lệ không
        if ($showtime->status !== Showtime::STATUS_SCHEDULED && $showtime->status !== Showtime::STATUS_ONGOING) {
            return abort(404);
        }

        if ($showtime->start_time <= now()) {
            return abort(404);
        }

        // Tự động dọn dẹp các booking quá hạn trước khi hiển thị sơ đồ ghế
        $bookingService = new \App\Services\BookingService();
        $bookingService->cleanupExpiredPendingBookings();

        // ── ACTIVE PENDING BOOKING GUARD (Frontend UX) ──
        if (\Illuminate\Support\Facades\Auth::check()) {
            $activePendingBooking = $bookingService->getActivePendingBooking(\Illuminate\Support\Facades\Auth::id());
            if ($activePendingBooking && $activePendingBooking->showtime_id != $showtime->id) {
                return redirect()->route('home')->with('show_active_booking_modal', true);
            }
        }

        // Không tự ý hủy booking của user ở đây. BookingService::createBooking() sẽ lo việc đó.

        // Lấy thông tin ghế và những ghế đã đặt (chỉ lấy ghế chưa hủy và chưa hết hạn)
        $activeBookings = $showtime->bookings()
            ->where('status', '!=', 'Cancelled')
            ->where(function ($q) {
                $q->where('status', '!=', 'Pending')
                  ->orWhere('booking_time', '>=', now()->subMinutes(config('booking.seat_hold.duration_minutes', 10)));
            })
            ->with('bookedSeats')
            ->get();

        $userId = Auth::id();
        $myPendingSeats = [];
        $bookedSeats = [];

        foreach ($activeBookings as $booking) {
            $seatIds = $booking->bookedSeats->pluck('seat_id')->toArray();
            if ($userId && $booking->status === 'Pending' && $booking->user_id == $userId) {
                $myPendingSeats = array_merge($myPendingSeats, $seatIds);
            } else {
                $bookedSeats = array_merge($bookedSeats, $seatIds);
            }
        }

        $myPendingSeats = array_values(array_unique($myPendingSeats));
        $bookedSeats = array_values(array_unique($bookedSeats));

        $expiresAtMs = null;
        if ($userId && !empty($myPendingSeats)) {
            $myPendingBooking = \App\Models\Booking::where('user_id', $userId)
                ->where('showtime_id', $showtime->id)
                ->where('status', 'Pending')
                ->orderBy('booking_time', 'desc')
                ->first();

            if ($myPendingBooking) {
                $expiresAtMs = ($myPendingBooking->booking_time->timestamp + \App\Services\BookingService::getHoldDuration() * 60) * 1000;
            }
        }

        $room = $showtime->room()->with(['seats' => function ($q) {
            $q->orderBy('row_name')
              ->orderBy('seat_number');
        }])->first();

        $ticketPrices = $showtime->ticketPrices()->get();

        return view('booking.select-seats', [
            'showtime' => $showtime,
            'room' => $room,
            'bookedSeats' => $bookedSeats,
            'myPendingSeats' => $myPendingSeats,
            'ticketPrices' => $ticketPrices,
            'expiresAtMs' => $expiresAtMs,
        ]);
    }

    /**
     * Helper: Đếm số ghế còn trống (loại bỏ ghế hỏng và ghế đã đặt)
     */
    private function getAvailableSeatsCount(int $showtimeId): int
    {
        // Dọn dẹp trước khi đếm
        $bookingService = new \App\Services\BookingService();
        $bookingService->cleanupExpiredPendingBookings();

        $room = Showtime::find($showtimeId)->room;
        
        // Chỉ đếm ghế có trạng thái AVAILABLE (loại bỏ BROKEN)
        $availableSeats = $room->seats()
            ->where('status', \App\Models\Seat::STATUS_AVAILABLE)
            ->count();

        $bookedSeats = DB::table('booked_seats')
            ->whereIn('booking_id', function ($query) use ($showtimeId) {
                $query->select('id')
                    ->from('bookings')
                    ->where('showtime_id', $showtimeId)
                    ->where(function ($q) {
                        $q->where('status', 'Paid')
                          ->orWhere(function ($q2) {
                              $q2->where('status', 'Pending')
                                 ->where('booking_time', '>=', now()->subMinutes(config('booking.seat_hold.duration_minutes', 10)));
                          });
                    });
            })
            ->count();

        return $availableSeats - $bookedSeats;
    }

    /**
     * API: Lấy danh sách ID các ghế đã được đặt hoặc đang được giữ (Pending)
     * Dành cho Frontend gọi AJAX định kỳ để cập nhật trạng thái sơ đồ ghế real-time.
     */
    public function getBookedSeatsAPI(Showtime $showtime)
    {
        // Có thể gọi cleanup để dọn đơn rác trước khi lấy danh sách
        $bookingService = new \App\Services\BookingService();
        $bookingService->cleanupExpiredPendingBookings();

        $activeBookings = $showtime->bookings()
            ->where('status', '!=', 'Cancelled')
            ->where(function ($q) {
                $q->where('status', '!=', 'Pending')
                  ->orWhere('booking_time', '>=', now()->subMinutes(config('booking.seat_hold.duration_minutes', 10)));
            })
            ->with('bookedSeats')
            ->get();

        $userId = Auth::id();
        $myPendingSeats = [];
        $bookedSeats = [];

        foreach ($activeBookings as $booking) {
            $seatIds = $booking->bookedSeats->pluck('seat_id')->toArray();
            if ($userId && $booking->status === 'Pending' && $booking->user_id == $userId) {
                $myPendingSeats = array_merge($myPendingSeats, $seatIds);
            } else {
                $bookedSeats = array_merge($bookedSeats, $seatIds);
            }
        }

        $myPendingSeats = array_values(array_unique($myPendingSeats));
        $bookedSeats = array_values(array_unique($bookedSeats));

        return response()->json([
            'success' => true,
            'bookedSeats' => $bookedSeats,
            'myPendingSeats' => $myPendingSeats
        ]);
    }

    /**
     * API: Hủy chủ động (Explicit Cancellation) lượt đặt vé Pending của User
     */
    public function cancelExplicit(Request $request)
    {
        $request->validate([
            'showtime_id' => 'required|integer'
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Tìm đúng Booking Pending của user cho suất chiếu này
        $booking = \App\Models\Booking::where('user_id', $userId)
            ->where('showtime_id', $request->showtime_id)
            ->where('status', 'Pending')
            ->first();

        // Nếu không có, coi như đã hủy hoặc hết hạn -> Trả về success (Idempotent)
        if (!$booking) {
            return response()->json(['success' => true, 'message' => 'No active pending booking found.']);
        }

        try {
            DB::transaction(function () use ($booking) {
                // 1. Cập nhật trạng thái Booking
                $booking->status = 'Cancelled';
                $booking->cancellation_reason = 'User cancelled explicitly';
                $booking->cancelled_at = now();
                $booking->save();

                // 2. Cập nhật trạng thái Ghế
                DB::table('booked_seats')
                    ->where('booking_id', $booking->id)
                    ->update([
                        'status' => 'CANCELLED',
                        'updated_at' => now()
                    ]);

                // 3. Nhả SeatHold an toàn (Không tính Spam)
                $abuseService = new \App\Services\SeatHoldAbuseService();
                $abuseService->markReleased($booking->id);
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Explicit cancel failed: ' . $e->getMessage());
            return response()->json(['error' => 'Cancellation failed'], 500);
        }
    }
}
