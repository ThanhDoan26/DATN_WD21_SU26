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
    public function selectCinema(Movie $movie): mixed
    {

        // Lấy danh sách rạp có suất chiếu còn mở bán online (trước giờ chiếu tối thiểu 15 phút)
        $cinemas = Cinema::whereHas('rooms', function ($query) use ($movie) {
            $query->whereHas('showtimes', function ($q) use ($movie) {
                $q->where('movie_id', $movie->id)
                  ->where('status', Showtime::STATUS_SCHEDULED)
                  ->where('start_time', '>', now()->addMinutes(15));
            });
        })
        ->with(['rooms' => function ($query) use ($movie) {
            $query->whereHas('showtimes', function ($q) use ($movie) {
                $q->where('movie_id', $movie->id)
                  ->where('status', Showtime::STATUS_SCHEDULED)
                  ->where('start_time', '>', now()->addMinutes(15));
            });
        }])
        ->get();

        $cities = $cinemas->pluck('city')->filter()->unique()->values();

        return view('booking.select-cinema', [
            'movie' => $movie,
            'cinemas' => $cinemas,
            'cities' => $cities,
        ]);
    }

    /**
     * Bước 2 & 3: Chọn ngày và suất chiếu
     */
    public function selectDatesAndShowtimes(Movie $movie, Cinema $cinema): mixed
    {

        return view('booking.select-dates-and-showtimes', [
            'movie' => $movie,
            'cinema' => $cinema,
        ]);
    }

    /**
     * API: Lấy danh sách ngày chiếu
     * Bước 2: Chọn ngày chiếu - chỉ hiển thị ngày có suất chiếu còn mở bán online
     */
    public function getDates(Request $request): JsonResponse
    {
        $movieId = $request->get('movie_id');
        $cinemaId = $request->get('cinema_id');

        // Validate
        if (!$movieId || !$cinemaId) {
            return response()->json(['error' => 'Missing parameters'], 400);
        }

        $movie = Movie::find($movieId);

        // Lấy danh sách ngày chiếu theo phim + rạp (chỉ lấy suất mở bán online)
        $dates = Showtime::where('movie_id', $movieId)
            ->whereHas('room', function ($query) use ($cinemaId) {
                $query->where('cinema_id', $cinemaId);
            })
            ->where('status', Showtime::STATUS_SCHEDULED)
            ->where('start_time', '>', now()->addMinutes(15))
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
     * Bước 3: Chọn suất chiếu theo phim, rạp, ngày (chỉ lấy suất mở bán online)
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

        $movie = Movie::find($movieId);

        // Lấy danh sách suất chiếu theo phim + rạp + ngày
        $showtimes = Showtime::where('movie_id', $movieId)
            ->whereHas('room', function ($query) use ($cinemaId) {
                $query->where('cinema_id', $cinemaId);
            })
            ->where('status', Showtime::STATUS_SCHEDULED)
            ->whereDate('start_time', $date)
            ->where('start_time', '>', now()->addMinutes(15))
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
        $showtime->loadMissing('movie');

        // Kiểm tra suất chiếu có còn được phép đặt vé online không
        if (!$showtime->isOnlineBookable()) {
            return redirect()->route('home')->with('error', 'Suất chiếu này đã đóng cổng đặt vé trực tuyến (cần đặt trước giờ chiếu tối thiểu 15 phút). Vui lòng mua vé tại quầy hoặc chọn suất chiếu khác.');
        }

        // Tự động dọn dẹp các booking quá hạn trước khi hiển thị sơ đồ ghế
        $bookingService = new \App\Services\BookingService();
        $bookingService->cleanupExpiredPendingBookings();

        // Không tự ý hủy booking của user ở đây. BookingService::createBooking() sẽ lo việc đó.

        // Lấy thông tin ghế và những ghế đã đặt (chỉ lấy ghế chưa hủy và chưa hết hạn)
        $activeBookings = $showtime->bookings()
            ->where('status', '!=', 'Cancelled')
            ->where(function ($q) {
                $q->whereNotIn('status', ['Pending', 'PROCESSING'])
                  ->orWhere('booking_time', '>=', now()->subMinutes(config('booking.seat_hold.duration_minutes', 10)));
            })
            ->with('bookedSeats')
            ->get();

        $userId = Auth::id();
        $myPendingSeats = [];
        $bookedSeats = [];

        foreach ($activeBookings as $booking) {
            $seatIds = $booking->bookedSeats->pluck('seat_id')->toArray();
            if ($userId && in_array($booking->status, ['Pending', 'PROCESSING']) && $booking->user_id == $userId) {
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
                ->whereIn('status', ['Pending', 'PROCESSING'])
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
        $userId = Auth::id();
        $myPendingSeats = [];
        $bookedSeats = [];

        // 1. Fetch Paid seats from Database
        $paidBookings = $showtime->bookings()
            ->whereIn('status', ['Paid', 'Used'])
            ->with(['bookedSeats' => fn ($query) => $query->where('status', '!=', 'CANCELLED')])
            ->get();
            
        foreach ($paidBookings as $booking) {
            $bookedSeats = array_merge($bookedSeats, $booking->bookedSeats->pluck('seat_id')->toArray());
        }

        // 2. Fetch Pending seats from Redis (with DB fallback if Redis is down)
        try {
            $redisPrefix = "seat_lock:showtime_{$showtime->id}:seat_*";
            $keys = \Illuminate\Support\Facades\Redis::keys($redisPrefix);
            
            foreach ($keys as $key) {
                preg_match('/seat_(\d+)$/', $key, $matches);
                if (isset($matches[1])) {
                    $seatId = (int) $matches[1];
                    $lockData = \Illuminate\Support\Facades\Redis::get("seat_lock:showtime_{$showtime->id}:seat_{$seatId}");
                    if ($lockData) {
                        $data = json_decode($lockData, true);
                        if ($userId && isset($data['user_id']) && $data['user_id'] == $userId) {
                            $myPendingSeats[] = $seatId;
                        } else {
                            $bookedSeats[] = $seatId;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Redis unavailable in getBookedSeatsAPI, falling back to DB: " . $e->getMessage());
            $pendingBookings = $showtime->bookings()
                ->whereIn('status', ['Pending', 'PROCESSING'])
                ->where('booking_time', '>=', now()->subMinutes(\App\Services\BookingService::getHoldDuration()))
                ->with(['bookedSeats' => fn ($query) => $query->where('status', '!=', 'CANCELLED')])
                ->get();

            foreach ($pendingBookings as $booking) {
                $seatIds = $booking->bookedSeats->pluck('seat_id')->toArray();
                if ($userId && $booking->user_id == $userId) {
                    $myPendingSeats = array_merge($myPendingSeats, $seatIds);
                } else {
                    $bookedSeats = array_merge($bookedSeats, $seatIds);
                }
            }
        }

        $myPendingSeats = array_values(array_unique($myPendingSeats));
        $bookedSeats = array_values(array_unique($bookedSeats));

        return response()->json([
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
            'showtime_id' => 'nullable|integer',
            'booking_id' => 'nullable|integer',
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['error' => 'Bạn chưa đăng nhập.'], 401);
        }

        $query = \App\Models\Booking::where('user_id', $userId)
            ->where('status', 'Pending');

        if ($request->booking_id) {
            $query->where('id', $request->booking_id);
        } elseif ($request->showtime_id) {
            $query->where('showtime_id', $request->showtime_id);
            if ($userId) {
                $query->where(function($q) use ($userId) {
                    $q->where('user_id', $userId)->orWhereNull('user_id');
                });
            } else {
                $query->whereNull('user_id');
            }
        } else {
            return response()->json(['error' => 'Vui lòng cung cấp showtime_id hoặc booking_id'], 422);
        }

        $bookings = $query->get();

        $showtime = null;
        if ($request->showtime_id) {
            $showtime = \App\Models\Showtime::find($request->showtime_id);
        } elseif ($bookings->isNotEmpty()) {
            $showtime = \App\Models\Showtime::find($bookings->first()->showtime_id);
        }

        $redirectUrl = $showtime && $showtime->movie_id 
            ? route('movies.show', $showtime->movie_id) 
            : route('home');

        session()->flash('info', 'Đã hủy quá trình đặt vé.');

        // Nếu không có, coi như đã hủy hoặc hết hạn -> Trả về success (Idempotent)
        if ($bookings->isEmpty()) {
            return response()->json([
                'success' => true,
                'redirect_url' => $redirectUrl,
                'message' => 'Đã hủy quá trình đặt vé.'
            ]);
        }

        try {
            $bookingService = new \App\Services\BookingService();
            foreach ($bookings as $booking) {
                $bookingService->cancelBooking($booking->id, 'User cancelled explicitly');
            }

            return response()->json([
                'success' => true,
                'redirect_url' => $redirectUrl,
                'message' => 'Đã hủy quá trình đặt vé thành công.'
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Explicit cancel failed: ' . $e->getMessage());
            return response()->json(['error' => 'Cancellation failed'], 500);
        }
    }
}
