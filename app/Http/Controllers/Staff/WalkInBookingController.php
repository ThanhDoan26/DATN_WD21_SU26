<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Showtime;
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
use Illuminate\Support\Facades\Validator;
use App\Mail\TicketConfirmationMail;

class WalkInBookingController extends Controller
{
    /**
     * Step 1: Chọn phim có suất chiếu tại rạp của nhân viên
     */
    public function movies(): View
    {
        $cinemaId = Auth::user()?->cinema_id;
        if (!$cinemaId) {
            abort(403, 'Nhân viên chưa được phân công rạp.');
        }

        // Hiển thị các phim có suất chiếu hợp lệ tại rạp của nhân viên (bao gồm suất sắp chiếu và đang chiếu chưa quá 30 phút)
        $movies = Movie::whereIn('status', ['NOW_SHOWING', 'COMING_SOON'])
            ->whereHas('showtimes', function ($query) use ($cinemaId) {
                $query->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
                      ->where('start_time', '>=', now()->subMinutes(30))
                      ->where(function ($q) {
                          $q->whereNull('end_time')->orWhere('end_time', '>', now());
                      })
                      ->whereHas('room', function($r) use ($cinemaId) {
                          $r->where('cinema_id', $cinemaId);
                      });
            })
            ->withCount(['showtimes' => function ($query) use ($cinemaId) {
                $query->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
                      ->where('start_time', '>=', now()->subMinutes(30))
                      ->where(function ($q) {
                          $q->whereNull('end_time')->orWhere('end_time', '>', now());
                      })
                      ->whereHas('room', function($r) use ($cinemaId) {
                          $r->where('cinema_id', $cinemaId);
                      });
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('staff.walkin.movies', compact('movies'));
    }

    /**
     * Step 2 & 3: Chọn ngày và giờ chiếu
     */
    public function selectDatesAndShowtimes(Movie $movie): View
    {
        $cinema = Auth::user()?->cinema;
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
     * Step 4: Chọn ghế
     */
    public function selectSeats(Showtime $showtime): View
    {
        $cinemaId = Auth::user()?->cinema_id;
        if (!$cinemaId) {
            abort(403, 'Nhân viên chưa được phân công rạp.');
        }

        $showtime->loadMissing('room.cinema');

        // Kiểm tra suất chiếu có thuộc rạp của staff không
        if (!$showtime->room || $showtime->room->cinema_id !== $cinemaId) {
            abort(403, 'Bạn không có quyền truy cập suất chiếu của rạp khác.');
        }

        if (!$showtime->isWalkInBookable()) {
            abort(403, 'Suất chiếu đã bắt đầu quá 30 phút hoặc đã kết thúc, không thể đặt vé tại quầy.');
        }

        (new BookingService())->cleanupExpiredPendingBookings();

        $bookedSeats = $showtime->bookings()
            ->where('status', '!=', 'Cancelled')
            ->where(function ($query) {
                $query->whereIn('status', ['Paid', 'Used'])
                    ->orWhere(function ($pendingQuery) {
                        $pendingQuery->whereIn('status', ['Pending', 'PROCESSING'])
                            ->where('booking_time', '>=', now()->subMinutes(BookingService::getHoldDuration()));
                    });
            })
            ->with(['bookedSeats' => fn ($query) => $query->where('status', '!=', 'CANCELLED')])
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
        $cinemaId = Auth::user()?->cinema_id;
        if (!$cinemaId) {
            abort(403, 'Nhân viên chưa được phân công rạp.');
        }

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
            $staffBookingId = session('staff_walkin_booking_id');
            $staffBooking = $staffBookingId
                ? Booking::where('id', $staffBookingId)
                    ->whereNull('user_id')
                    ->where('booking_source', 'walk_in')
                    ->where('showtime_id', $showtimeId)
                    ->whereIn('status', ['Pending', 'PROCESSING'])
                    ->first()
                : null;
            $staffBookingSeatIds = $staffBooking?->bookedSeats()->pluck('seat_id')->sort()->values()->all() ?? [];
            $requestedSeatIds = collect($seatIds)->unique()->sort()->values()->all();

            $showtime = Showtime::with('room.cinema')->find($showtimeId);

            if (!$showtime) {
                abort(404, 'Suất chiếu không tồn tại.');
            }

            // Kiểm tra suất chiếu thuộc rạp của staff
            if (!$showtime->room || $showtime->room->cinema_id !== $cinemaId) {
                abort(403, 'Bạn không có quyền truy cập suất chiếu của rạp khác.');
            }

            if (!$showtime->isWalkInBookable()) {
                abort(403, 'Suất chiếu đã bắt đầu quá 30 phút hoặc đã kết thúc, không thể đặt vé tại quầy.');
            }

            $takenSeatIds = DB::table('booked_seats')
                ->join('bookings', 'booked_seats.booking_id', '=', 'bookings.id')
                ->where('bookings.showtime_id', $showtimeId)
                ->whereIn('booked_seats.seat_id', $seatIds)
                ->where('booked_seats.status', '!=', 'CANCELLED')
                ->where('bookings.status', '!=', 'Cancelled')
                ->when($staffBooking, fn ($query) => $query->where('bookings.id', '!=', $staffBooking->id))
                ->where(function ($q) {
                    $q->whereNotIn('bookings.status', ['Pending', 'PROCESSING'])
                        ->orWhere('bookings.booking_time', '>=', now()->subMinutes(config('booking.seat_hold.duration_minutes', 10)));
                })
                ->distinct()
                ->pluck('booked_seats.seat_id')
                ->toArray();

            if (!empty($takenSeatIds)) {
                $seatCodes = Seat::whereIn('id', $takenSeatIds)->get()->map(fn($seat) => $seat->getSeatCode())->implode(', ');
                return redirect()->route('staff.walkin.seats', ['showtime' => $showtimeId])
                    ->with('error', 'Ghế ' . $seatCodes . ' đã được khách chọn và đã có người đặt/giữ. Vui lòng chọn ghế khác.');
            }

            $ticketPrices = TicketPrice::where('showtime_id', $showtimeId)
                ->where('status', 'ACTIVE')
                ->get()
                ->keyBy('seat_type');

            $selectedSeats = Seat::where('room_id', $showtime->room_id)->whereIn('id', $seatIds)->get();

            if ($selectedSeats->count() !== count($seatIds)) {
                abort(404, 'Một số ghế không tồn tại hoặc không thuộc phòng chiếu này.');
            }

            if (!$staffBooking || $staffBookingSeatIds !== $requestedSeatIds) {
                if ($staffBooking) {
                    $staffBooking->update([
                        'status' => 'Cancelled',
                        'cancellation_reason' => 'Staff selected a new seat set',
                        'cancelled_at' => now(),
                    ]);
                    DB::table('booked_seats')
                        ->where('booking_id', $staffBooking->id)
                        ->update(['status' => 'CANCELLED', 'updated_at' => now()]);

                    foreach ($staffBookingSeatIds as $oldSeatId) {
                        try {
                            \Illuminate\Support\Facades\Redis::del("seat_lock:showtime_{$showtimeId}:seat_{$oldSeatId}");
                        } catch (\Throwable $e) {
                            Log::warning('Không thể giải phóng khóa Redis của booking staff cũ: ' . $e->getMessage());
                        }
                    }
                }

                try {
                    $staffBookingId = $bookingService->createBooking(
                        null,
                        $showtimeId,
                        $seatIds,
                        'CASH',
                        null,
                        [],
                        ['booking_source' => 'walk_in']
                    );
                    session(['staff_walkin_booking_id' => $staffBookingId]);
                } catch (\Throwable $e) {
                    return redirect()->route('staff.walkin.seats', ['showtime' => $showtimeId])
                        ->with('error', 'Ghế bạn chọn vừa được khách khác giữ. Vui lòng chọn ghế khác.');
                }
            } else {
                $staffBookingId = $staffBooking->id;
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
        $coupons = Coupon::activeAndValid()->get();

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
            'coupons',
            'staffBookingId'
        ))->with([
            'layout' => 'layouts.staff',
            'isWalkIn' => true,
        ]);
    }

    /**
     * Reserve
     */
    public function releaseHold(): JsonResponse
    {
        $bookingId = session('staff_walkin_booking_id');
        $booking = $bookingId
            ? Booking::where('id', $bookingId)
                ->whereNull('user_id')
                ->where('booking_source', 'walk_in')
                ->whereIn('status', ['Pending', 'PROCESSING'])
                ->first()
            : null;

        if ($booking) {
            $seatIds = $booking->bookedSeats()->pluck('seat_id')->all();
            $booking->update([
                'status' => 'Cancelled',
                'cancellation_reason' => 'Staff left checkout before payment',
                'cancelled_at' => now(),
            ]);
            DB::table('booked_seats')
                ->where('booking_id', $booking->id)
                ->update(['status' => 'CANCELLED', 'updated_at' => now()]);

            foreach ($seatIds as $seatId) {
                try {
                    \Illuminate\Support\Facades\Redis::del("seat_lock:showtime_{$booking->showtime_id}:seat_{$seatId}");
                } catch (\Throwable $e) {
                    Log::warning('Không thể giải phóng khóa Redis khi staff rời checkout: ' . $e->getMessage());
                }
            }
        }

        session()->forget('staff_walkin_booking_id');

        return response()->json(['success' => true]);
    }

    /**
     * Reserve
     */
    public function reserve(Request $request)
    {
        $cinemaId = Auth::user()?->cinema_id;
        if (!$cinemaId) {
            return response()->json(['success' => false, 'message' => 'Nhân viên chưa được phân công rạp.'], 403);
        }

        $validationRules = [
            'showtime_id' => 'required|exists:showtimes,id',
            'seat_ids' => 'required',
            'combos' => 'nullable|array',
            'payment_method' => 'nullable|string|max:100',
            'coupon_code' => 'nullable|string|max:50',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'required|email|max:255',
            'booking_id' => 'nullable|integer',
        ];
        $validator = Validator::make($request->all(), $validationRules, [
            'customer_name.required' => 'Vui lòng nhập tên khách hàng.',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại khách hàng.',
            'customer_email.required' => 'Vui lòng nhập email khách hàng.',
            'customer_email.email' => 'Email khách hàng không đúng định dạng.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $showtimeId = (int) $request->input('showtime_id');
        $showtime = Showtime::with('room.cinema')->find($showtimeId);

        if (!$showtime) {
            return response()->json(['success' => false, 'message' => 'Suất chiếu không tồn tại.'], 404);
        }

        // BẮT BUỘC kiểm tra showtime thuộc rạp của staff trước khi tạo booking hay giữ ghế
        if (!$showtime->room || $showtime->room->cinema_id !== $cinemaId) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thao tác suất chiếu của rạp khác.'], 403);
        }

        if (!$showtime->isWalkInBookable()) {
            return response()->json(['success' => false, 'message' => 'Suất chiếu đã bắt đầu quá 30 phút hoặc đã kết thúc, không thể đặt vé tại quầy.'], 403);
        }

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

        $validSeatsCount = Seat::where('room_id', $showtime->room_id)->whereIn('id', $seatIds)->count();
        if ($validSeatsCount !== count($seatIds)) {
            return response()->json(['success' => false, 'message' => 'Một hoặc nhiều ghế không hợp lệ hoặc không thuộc phòng chiếu này.'], 422);
        }

        $takenSeatIds = DB::table('booked_seats')
            ->join('bookings', 'booked_seats.booking_id', '=', 'bookings.id')
            ->where('bookings.showtime_id', $showtimeId)
            ->whereIn('booked_seats.seat_id', $seatIds)
            ->where('booked_seats.status', '!=', 'CANCELLED')
            ->where('bookings.status', '!=', 'Cancelled')
            ->when((int) $request->input('booking_id') > 0, fn ($query) => $query->where('bookings.id', '!=', (int) $request->input('booking_id')))
            ->where(function ($q) {
                $q->whereNotIn('bookings.status', ['Pending', 'PROCESSING'])
                    ->orWhere('bookings.booking_time', '>=', now()->subMinutes(config('booking.seat_hold.duration_minutes', 10)));
            })
            ->distinct()
            ->pluck('booked_seats.seat_id')
            ->toArray();

        if (!empty($takenSeatIds)) {
            $seatCodes = Seat::whereIn('id', $takenSeatIds)->get()->map(fn($seat) => $seat->getSeatCode())->implode(', ');
            return response()->json([
                'success' => false,
                'message' => 'Ghế ' . $seatCodes . ' đã được khách chọn và đã có người đặt/giữ. Vui lòng chọn ghế khác.'
            ], 409);
        }

        try {
            $bookingService = new BookingService();
            $paymentMethod = $request->input('payment_method', 'CASH');
            $combos = $request->input('combos') ?? [];
            $heldBooking = Booking::where('id', $request->input('booking_id'))
                ->where('id', session('staff_walkin_booking_id'))
                ->whereNull('user_id')
                ->where('booking_source', 'walk_in')
                ->where('showtime_id', $showtimeId)
                ->where('status', 'Pending')
                ->first();

            if ($heldBooking) {
                $bookingService->updatePendingBooking(
                    $heldBooking->id,
                    'CASH',
                    $request->input('coupon_code'),
                    $combos
                );
                $heldBooking->update([
                    'customer_name' => $request->input('customer_name'),
                    'customer_phone' => $request->input('customer_phone'),
                    'customer_email' => $request->input('customer_email'),
                ]);
                $bookingId = $heldBooking->id;
            } else {
            $extraData = [
                'booking_source' => 'walk_in',
                'customer_name' => $request->input('customer_name'),
                'customer_phone' => $request->input('customer_phone'),
                'customer_email' => $request->input('customer_email'),
            ];

            $bookingId = $bookingService->createBooking(
                null, // Walk-in has no user_id
                $showtimeId,
                $seatIds,
                $paymentMethod,
                $request->input('coupon_code'),
                $combos,
                $extraData
            );
            }

            // If it's CASH payment (Walk-in), complete it immediately (BookingObserver handles TicketConfirmationMail queued sending)
            if ($paymentMethod === 'CASH') {
                $bookingService->completePayment($bookingId, 'CASH');
                
                // If email provided, send confirmation
                $bookingDetails = $bookingService->getBookingDetails($bookingId);
                $mailSent = false;
                $hasEmail = false;

                if ($request->input('customer_email')) {
                    $hasEmail = true;
                    \Illuminate\Support\Facades\Log::info("WalkInBookingController: Đang gọi Mail::to()->send() gửi cho " . $request->input('customer_email'));
                    $showtimeWithMovie = Showtime::with(['movie', 'room.cinema'])->find($showtimeId);
                    try {
                        Mail::to($request->input('customer_email'))->send(new TicketConfirmationMail($bookingDetails, $showtimeWithMovie));
                        $mailSent = true;
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

                $message = 'Đặt vé và thanh toán thành công.';
                if ($hasEmail && !$mailSent) {
                    $message = 'Đặt vé và thanh toán thành công nhưng gửi email xác nhận thất bại. Vui lòng kiểm tra lại email hoặc liên hệ hỗ trợ.';
                }

                return response()->json([
                    'success' => true,
                    'isWalkIn' => true,
                    'redirect_url' => route('staff.walkin.success', ['booking_id' => $bookingId, 'auto_print' => 1]),
                    'message' => $message,
                ]);
            }

            // Other payment methods (e.g. Momo, VNPAY if added later for walk-in pos integration)
            return response()->json([
                'success' => true,
                'isWalkIn' => true,
                'redirect_url' => route('staff.walkin.success', ['booking_id' => $bookingId, 'auto_print' => 1]),
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
        $cinemaId = Auth::user()?->cinema_id;
        if (!$cinemaId) {
            abort(403, 'Nhân viên chưa được phân công rạp.');
        }

        $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
        ]);

        $booking = Booking::with(['showtime.movie', 'showtime.room'])->where('id', $request->query('booking_id'))->first();

        if (!$booking) {
            abort(404, 'Booking không tồn tại.');
        }

        // Kiểm tra booking thuộc rạp của staff
        if (!$booking->showtime || !$booking->showtime->room || $booking->showtime->room->cinema_id !== $cinemaId) {
            abort(403, 'Bạn không có quyền truy cập đơn đặt vé của rạp khác.');
        }

        $bookingService = new BookingService();
        $bookingDetails = $bookingService->getBookingDetails($booking->id);
        $bookingDetails['movie_title'] = $booking->showtime->movie->title ?? 'N/A';
        $bookingDetails['final_total'] = $booking->total_price;

        return view('staff.walkin.checkout-success', [
            'booking' => $bookingDetails,
            'layout' => 'layouts.staff',
            'isWalkIn' => true,
        ]);
    }
}
