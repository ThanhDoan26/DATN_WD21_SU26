<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Cinema;
use App\Models\Showtime;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Combo;
use App\Models\Coupon;
use App\Models\TicketPrice;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketConfirmationMail;

class WalkInBookingController extends Controller
{
    public function movies(): View
    {
        $cinemaId = Auth::user()->cinema_id;

        // Nghiệp vụ chuyên nghiệp: Chỉ hiển thị các phim thực sự đang có hoặc sắp có suất chiếu
        // tại RẠP CỦA NHÂN VIÊN.
        $movies = Movie::whereIn('status', ['NOW_SHOWING', 'COMING_SOON'])
            ->whereHas('showtimes', function ($query) use ($cinemaId) {
                $query->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
                      ->where('start_time', '>', now())
                      ->whereHas('room', function($r) use ($cinemaId) {
                          $r->where('cinema_id', $cinemaId);
                      });
            })
            ->withCount(['showtimes' => function ($query) use ($cinemaId) {
                $query->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
                      ->where('start_time', '>', now())
                      ->whereHas('room', function($r) use ($cinemaId) {
                          $r->where('cinema_id', $cinemaId);
                      });
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('staff.walkin.movies', compact('movies'));
    }

    /**
     * Step 2 & 3: Select Dates and Showtimes
     */
    public function selectDatesAndShowtimes(Movie $movie): View
    {
        $cinema = Auth::user()->cinema;
        if (!$cinema) {
            abort(403, 'Nhân viên chưa được phân công rạp.');
        }

        return view('staff.walkin.dates-showtimes', [
            'movie' => $movie,
            'cinema' => $cinema,
            'layout' => 'layouts.staff',
            'isWalkIn' => true,
        ]);
    }



    /**
     * Step 2 & 3: Select Dates and Showtimes
     */
    /**
     * Step 4: Select Seats
     */
    public function selectSeats(Showtime $showtime): View
    {
        if ($showtime->status !== Showtime::STATUS_SCHEDULED && $showtime->status !== Showtime::STATUS_ONGOING) {
            return abort(404);
        }

        if ($showtime->start_time <= now()) {
            return abort(404);
        }

        // Kiểm tra suất chiếu có thuộc rạp của staff không
        if (Auth::user()->cinema_id && $showtime->room->cinema_id !== Auth::user()->cinema_id) {
            abort(403, 'Bạn không có quyền truy cập suất chiếu của rạp khác.');
        }

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

        return view('staff.walkin.seats', [
            'showtime' => $showtime,
            'room' => $room,
            'bookedSeats' => $bookedSeats->toArray(),
            'ticketPrices' => $ticketPrices,
            'layout' => 'layouts.staff',
            'isWalkIn' => true,
        ]);
    }

    /**
     * Checkout
     */
    public function checkout(Request $request)
    {
        $bookingService = new BookingService();
        $bookingService->cleanupExpiredPendingBookings();

        $showtime = null;
        $selectedSeats = collect();
        $ticketPrices = collect();
        $seatSummary = [];
        $subtotal = 0;
        $surcharge = 0;
        $total = 0;
        $showtimeId = $request->query('showtime_id');
        $seatIds = $request->query('seat_ids');

        if (is_array($showtimeId)) {
            $showtimeId = $showtimeId[0] ?? null;
        }
        if ($showtimeId !== null) {
            $showtimeId = (int) $showtimeId;
        }

        if (is_array($seatIds)) {
            $seatIds = implode(',', array_filter($seatIds, fn($item) => $item !== null && $item !== ''));
        }

        if ($seatIds && is_string($seatIds)) {
            $seatIds = array_filter(array_map('intval', explode(',', $seatIds)));
        } else {
            $seatIds = [];
        }

        if ($showtimeId && !empty($seatIds)) {
            $showtime = Showtime::with('room.cinema')->find($showtimeId);

            if (!$showtime) {
                abort(404, 'Suất chiếu không tồn tại.');
            }

            if (!in_array($showtime->status, [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])) {
                abort(404, 'Suất chiếu này không còn khả dụng.');
            }

            if ($showtime->start_time <= now()) {
                abort(404, 'Suất chiếu này đã bắt đầu hoặc kết thúc.');
            }

            $ticketPrices = TicketPrice::where('showtime_id', $showtimeId)
                ->where('status', 'ACTIVE')
                ->get()
                ->keyBy('seat_type');

            $selectedSeats = Seat::whereIn('id', $seatIds)->get();

            if ($selectedSeats->count() !== count($seatIds)) {
                abort(404, 'Một số ghế không tồn tại.');
            }

            foreach ($selectedSeats as $seat) {
                $priceRow = $ticketPrices[$seat->seat_type] ?? null;
                $seatPrice = $priceRow ? (float) $priceRow->price : 0;
                $seatFinalPrice = $seatPrice + (float) $showtime->surcharge;

                $seatSummary[] = [
                    'id' => $seat->id,
                    'code' => $seat->getSeatCode(),
                    'type' => $seat->seat_type,
                    'base_price' => $seatPrice,
                    'surcharge' => (float) $showtime->surcharge,
                    'final_price' => $seatFinalPrice,
                ];

                $subtotal += $seatPrice;
                $total += $seatFinalPrice;
            }

            $surcharge = (float) $showtime->surcharge;
        }

        $combos = Combo::where('status', 'ACTIVE')->get();
        $coupons = Coupon::where('status', 'ACTIVE')->get();

        return view('staff.walkin.checkout', compact(
            'showtime',
            'selectedSeats',
            'ticketPrices',
            'seatSummary',
            'subtotal',
            'surcharge',
            'total',
            'seatIds',
            'showtimeId',
            'combos',
            'coupons'
        ))->with([
            'layout' => 'layouts.staff',
            'isWalkIn' => true,
        ]);
    }

    /**
     * Reserve
     */
    public function reserve(Request $request)
    {
        $request->validate([
            'showtime_id' => 'required|exists:showtimes,id',
            'seat_ids' => 'required',
            'combos' => 'nullable|array',
            'payment_method' => 'nullable|string|max:100',
            'coupon_code' => 'nullable|string|max:50',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
        ]);

        $seatIdsInput = $request->input('seat_ids');
        if (is_string($seatIdsInput)) {
            $seatIds = array_filter(array_map('intval', explode(',', $seatIdsInput)));
        } elseif (is_array($seatIdsInput)) {
            $seatIds = array_filter(array_map('intval', $seatIdsInput));
        } else {
            $seatIds = [];
        }

        if (empty($seatIds)) {
            return response()->json(['success' => false, 'message' => 'Vui lòng chọn ít nhất 1 ghế.'], 422);
        }

        try {
            $bookingService = new BookingService();
            
            $extraData = [
                'booking_source' => 'walk_in',
                'customer_name' => $request->input('customer_name'),
                'customer_phone' => $request->input('customer_phone'),
                'customer_email' => $request->input('customer_email'),
            ];

            $paymentMethod = $request->input('payment_method', 'CASH');

            $bookingId = $bookingService->createBooking(
                null, // Walk-in has no user_id
                (int) $request->input('showtime_id'),
                $seatIds,
                $paymentMethod,
                $request->input('coupon_code'),
                $request->input('combos', []),
                $extraData
            );

            // If it's CASH payment (Walk-in), complete it immediately
            if ($paymentMethod === 'CASH') {
                $bookingService->completePayment($bookingId, 'CASH');
                
                // If email provided, send confirmation
                $bookingDetails = $bookingService->getBookingDetails($bookingId);
                if ($request->input('customer_email')) {
                    \Illuminate\Support\Facades\Log::info("WalkInBookingController: Đang gọi Mail::to()->send() gửi cho " . $request->input('customer_email'));
                    $showtime = Showtime::with(['movie', 'room.cinema'])->find($request->input('showtime_id'));
                    try {
                        Mail::to($request->input('customer_email'))->send(new TicketConfirmationMail($bookingDetails, $showtime));
                    } catch (\Exception $e) {
                        Log::error('Walk-in payment email failed: ' . $e->getMessage(), [
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                } else {
                    \Illuminate\Support\Facades\Log::warning("WalkInBookingController: TicketConfirmationMail KHÔNG được gọi do khách hàng không cung cấp email.");
                }

                return response()->json([
                    'success' => true,
                    'isWalkIn' => true,
                    'redirect_url' => route('staff.walkin.success', ['booking_id' => $bookingId]),
                    'message' => 'Đặt vé và thanh toán thành công.',
                ]);
            }

            // Other payment methods (e.g. Momo, VNPAY if added later for walk-in pos integration)
            return response()->json([
                'success' => true,
                'isWalkIn' => true,
                'redirect_url' => route('staff.walkin.success', ['booking_id' => $bookingId]),
                'message' => 'Đã giữ ghế thành công.',
            ]);
        } catch (\Exception $e) {
            Log::error('Walkin Checkout reserve failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Success Page
     */
    public function success(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
        ]);

        $booking = Booking::with('showtime.movie')->where('id', $request->query('booking_id'))->first();

        if (!$booking) {
            abort(404, 'Booking không tồn tại.');
        }

        $bookingService = new BookingService();
        $bookingDetails = $bookingService->getBookingDetails($booking->id);
        $bookingDetails['movie_title'] = $booking->showtime->movie->title;
        $bookingDetails['final_total'] = $booking->total_price;

        return view('staff.walkin.checkout-success', [
            'booking' => $bookingDetails,
            'layout' => 'layouts.staff',
            'isWalkIn' => true,
        ]);
    }
}
