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
    public function selectSeats(Showtime $showtime): View
    {
        // Kiểm tra showtime có hợp lệ không
        if ($showtime->status !== Showtime::STATUS_SCHEDULED && $showtime->status !== Showtime::STATUS_ONGOING) {
            return abort(404);
        }

        if ($showtime->start_time <= now()) {
            return abort(404);
        }

        // Lấy thông tin ghế và những ghế đã đặt
        $bookedSeats = $showtime->bookings()
            ->where('status', '!=', 'Cancelled')
            ->with('bookedSeats')
            ->get()
            ->flatMap(function ($booking) {
                return $booking->bookedSeats->pluck('seat_id')->toArray();
            })
            ->unique()
            ->values();

        $room = $showtime->room()->with(['seats' => function ($q) {
            $q->orderBy('row_name')
              ->orderBy('seat_number');
        }])->first();

        $ticketPrices = $showtime->ticketPrices()->get();

        return view('booking.select-seats', [
            'showtime' => $showtime,
            'room' => $room,
            'bookedSeats' => $bookedSeats->toArray(),
            'ticketPrices' => $ticketPrices,
        ]);
    }

    /**
     * Helper: Đếm số ghế còn trống (loại bỏ ghế hỏng và ghế đã đặt)
     */
    private function getAvailableSeatsCount(int $showtimeId): int
    {
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
                    ->whereIn('status', ['Pending', 'Paid']);
            })
            ->count();

        return $availableSeats - $bookedSeats;
    }
}
