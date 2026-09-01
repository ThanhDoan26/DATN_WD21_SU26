<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\BookedSeat;
use App\Models\Showtime;
use Illuminate\Support\Facades\DB;
use App\Services\QRCodeService;

class CinemaStaffDashboardController extends Controller
{
    /**
     * Hiển thị trang Dashboard cho Cinema Staff
     */
    public function index()
    {
        $user = auth()->user();
        $cinemaId = $user->cinema_id;

        // Base queries
        $baseSeatsQuery = BookedSeat::query();
        $baseBookingQuery = Booking::query();

        if ($cinemaId) {
            $baseSeatsQuery->whereHas('booking.showtime.room', function ($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            });
            $baseBookingQuery->whereHas('showtime.room', function ($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            });
        }
 
        // ── KPI Cards ──────────────────────────────────────────────
        // Vé đã in hôm nay
        $printedToday = (clone $baseSeatsQuery)
            ->whereDate('printed_at', today())
            ->count(); 

        // Vé chưa in (Đã thanh toán)
        $unprintedTickets = (clone $baseSeatsQuery)
            ->whereIn('status', ['PAID', 'USED'])
            ->where(function($q) {
                $q->whereNull('printed_at')->orWhere('print_count', 0);
            })
            ->count();

        // Tổng vé đã in
        $totalPrintedTickets = (clone $baseSeatsQuery)
            ->where('print_count', '>', 0)
            ->count();

        // Booking mới hôm nay
        $bookingsToday = (clone $baseBookingQuery)
            ->whereDate('created_at', today())
            ->whereIn('status', [\App\Models\Booking::STATUS_PAID, \App\Models\Booking::STATUS_USED])
            ->count();

        // Doanh thu hôm nay
        $revenueToday = (clone $baseBookingQuery)
            ->whereDate('payment_time', today())
            ->whereIn('status', [\App\Models\Booking::STATUS_PAID, \App\Models\Booking::STATUS_USED])
            ->sum('total_price');

        // ── Biểu đồ doanh thu 7 ngày ───────────────────────────────
        $revenueChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $dayRevenue = (clone $baseBookingQuery)
                ->whereDate('payment_time', $date)
                ->whereIn('status', [\App\Models\Booking::STATUS_PAID, \App\Models\Booking::STATUS_USED])
                ->sum('total_price');
            $revenueChart[] = [
                'date'    => $date->format('d/m'),
                'revenue' => (float) $dayRevenue,
            ];
        }

        // ── Tỷ lệ in vé hôm nay ────────────────────────────────────
        $totalTicketsForToday = (clone $baseSeatsQuery)
            ->whereHas('booking.showtime', function ($q) {
                $q->whereDate('start_time', today());
            })
            ->whereIn('status', ['PAID', 'USED'])
            ->count();
        $printRate = $totalTicketsForToday > 0
            ? round($printedToday / $totalTicketsForToday * 100)
            : 0;

        // ── Suất chiếu hôm nay ────────────────────────────────────
        $todayShowtimes = Showtime::with(['movie', 'room'])
            ->whereDate('start_time', today())
            ->when($cinemaId, function ($q) use ($cinemaId) {
                $q->whereHas('room', fn($r) => $r->where('cinema_id', $cinemaId));
            })
            ->orderBy('start_time')
            ->limit(10)
            ->get();

        // ── Hoạt động in vé gần đây ───────────────────────────────
        $recentPrints = (clone $baseSeatsQuery)
            ->with(['booking.user', 'booking.showtime.movie', 'seat'])
            ->where('print_count', '>', 0)
            ->whereNotNull('printed_at')
            ->orderByDesc('printed_at')
            ->limit(8)
            ->get();

        // ── Vé sắp chiếu (suất chiếu trong 2h tới) ────────────────
        $expiringSoon = (clone $baseSeatsQuery)
            ->with(['booking.showtime.movie', 'booking.showtime.room'])
            ->whereIn('status', ['PAID', 'USED'])
            ->whereHas('booking.showtime', function ($q) {
                $q->where('start_time', '>=', now())
                  ->where('start_time', '<=', now()->addHours(2));
            })
            ->limit(5)
            ->get();

        return view('staff.dashboard.index', compact(
            'printedToday', 'unprintedTickets', 'totalPrintedTickets',
            'bookingsToday', 'revenueToday',
            'revenueChart', 'printRate',
            'todayShowtimes', 'recentPrints', 'expiringSoon'
        ));
    }

    /**
     * Màn hình tra cứu vé
     */
    public function searchForm(Request $request)
    {
        $user = auth()->user();
        $cinemaId = $user?->cinema_id;
        if (!$cinemaId) {
            abort(403, 'Nhân viên chưa được phân công rạp.');
        }

        $code = $request->query('code');
        $result = null;
        $warnings = [];
        $isOtherCinema = false;
        $ticketCinemaName = '';
        $searchType = null; // 'booking' or 'seat'

        if ($code) {
            $originalCode = trim($code);
            
            // 1. Kiểm tra xem chuỗi quét được có phải là JSON hợp lệ và chứa 'checksum' và 'booking_code' hay không
            $decoded = json_decode($originalCode, true);
            if (is_array($decoded) && isset($decoded['checksum']) && isset($decoded['booking_code'])) {
                // 2. Nếu đúng là JSON vé điện tử:
                $qrCodeService = app(QRCodeService::class);
                $verifyResult = $qrCodeService->verifyTicketQRCode($originalCode);
                if (isset($verifyResult['valid']) && !$verifyResult['valid']) {
                    $warnings[] = $verifyResult['message'];
                }
                $code = strtoupper($decoded['booking_code']);
                $extractedToken = null;
            } else {
                // 3. Nếu KHÔNG phải JSON (mã booking thường hoặc URL):
                $code = strtoupper($originalCode);
                
                // Bóc tách token nếu QR Code là URL (VD: http://.../tickets/xxx-yyy)
                $extractedToken = $originalCode;
                if (filter_var($originalCode, FILTER_VALIDATE_URL)) {
                    $segments = explode('/', parse_url($originalCode, PHP_URL_PATH));
                    $extractedToken = end($segments);
                }
                $extractedToken = strtolower($extractedToken);
            }

            // 1. Tìm theo mã booking - Cho phép tra cứu toàn hệ thống
            $booking = Booking::with([
                'user',
                'showtime.movie',
                'showtime.room.cinema',
                'bookedSeats.seat',
                'combos'
            ])
            ->where(function ($q) use ($code, $extractedToken) {
                $q->where('booking_code', $code);
                if (!empty($extractedToken)) {
                    $q->orWhere('ticket_token', $extractedToken);
                }
            })
            ->first();

            if ($booking) {
                $result = $booking;
                $searchType = 'booking';

                $ticketCinema = $booking->showtime?->room?->cinema;
                $ticketCinemaId = $ticketCinema?->id;
                $ticketCinemaName = $ticketCinema?->name ?? 'Rạp khác';
                $isOtherCinema = ($ticketCinemaId && $ticketCinemaId != $cinemaId);

                if ($isOtherCinema) {
                    $warnings[] = "⚠️ VÉ THUỘC RẠP KHÁC: Vé này thuộc chi nhánh '{$ticketCinemaName}'. Bạn chỉ có quyền tra cứu/xem thông tin vé, KHÔNG THỂ Chỉnh sửa hoặc In vé tại rạp này.";
                } else {
                    // Kiểm tra trạng thái thanh toán
                    if ($booking->status === 'Pending') {
                        $warnings[] = "Vé chưa thanh toán (Trạng thái đơn: Chờ thanh toán).";
                    } elseif ($booking->status === 'Cancelled') {
                        $warnings[] = "Đơn hàng vé này đã bị hủy.";
                    }

                    // Kiểm tra suất chiếu hết hạn
                    $showtime = $booking->showtime;
                    if ($showtime) {
                        if ($showtime->status === Showtime::STATUS_CANCELLED) {
                            $warnings[] = "Suất chiếu này đã bị hủy bỏ.";
                        } elseif ($showtime->status === Showtime::STATUS_COMPLETED || ($showtime->end_time && $showtime->end_time->isPast())) {
                            $warnings[] = "Suất chiếu của vé này đã diễn ra hoặc đã kết thúc (" . ($showtime->end_time ? $showtime->end_time->format('d/m/Y H:i') : '') . "). Vé đã hết hạn.";
                        }
                    }
                }
            } else {
                // 2. Tìm theo mã QR ghế - Cho phép tra cứu toàn hệ thống
                $bookedSeat = BookedSeat::with([
                    'seat',
                    'booking.user',
                    'booking.showtime.movie',
                    'booking.showtime.room.cinema',
                    'booking.combos'
                ])
                ->where('qr_code', $code)
                ->first();

                if ($bookedSeat) {
                    $result = $bookedSeat;
                    $searchType = 'seat';
                    $booking = $bookedSeat->booking;

                    $ticketCinema = $booking?->showtime?->room?->cinema;
                    $ticketCinemaId = $ticketCinema?->id;
                    $ticketCinemaName = $ticketCinema?->name ?? 'Rạp khác';
                    $isOtherCinema = ($ticketCinemaId && $ticketCinemaId != $cinemaId);

                    if ($isOtherCinema) {
                        $warnings[] = "⚠️ VÉ THUỘC RẠP KHÁC: Vé này thuộc chi nhánh '{$ticketCinemaName}'. Bạn chỉ có quyền tra cứu/xem thông tin vé, KHÔNG THỂ Chỉnh sửa hoặc In vé tại rạp này.";
                    } else {
                        // Kiểm tra trạng thái ghế
                        if ($bookedSeat->status === 'CANCELLED') {
                            $warnings[] = "Ghế này đã bị hủy bỏ.";
                        } elseif ($bookedSeat->status === 'RESERVED') {
                            $warnings[] = "Vé chưa được thanh toán (Trạng thái ghế: Đã đặt trước).";
                        }

                        // Kiểm tra suất chiếu hết hạn
                        if ($booking) {
                            $showtime = $booking->showtime;
                            if ($showtime) {
                                if ($showtime->status === Showtime::STATUS_CANCELLED) {
                                    $warnings[] = "Suất chiếu của vé này đã bị hủy.";
                                } elseif ($showtime->status === Showtime::STATUS_COMPLETED || ($showtime->end_time && $showtime->end_time->isPast())) {
                                    $warnings[] = "Suất chiếu của vé này đã diễn ra hoặc đã kết thúc (" . ($showtime->end_time ? $showtime->end_time->format('d/m/Y H:i') : '') . "). Vé đã hết hạn.";
                                }
                            }
                        }
                    }
                } else {
                    // Không tìm thấy vé
                    $warnings[] = "Mã vé hoặc mã QR không tồn tại trên hệ thống. Vui lòng kiểm tra lại.";
                }
            }
        }

        return view('staff.ticket.search', compact('code', 'result', 'searchType', 'warnings', 'isOtherCinema', 'ticketCinemaName'));
    }

    /**
     * API lookup phục vụ cho quét QR qua AJAX
     */
    public function lookup(Request $request)
    {
        $originalCode = trim($request->query('code'));
        $code = strtoupper($originalCode);
        if (!$originalCode) {
            return response()->json(['success' => false, 'error' => 'Vui lòng cung cấp mã vé.'], 400);
        }

        // Bóc tách token nếu QR Code là URL (VD: http://.../tickets/xxx-yyy)
        $extractedToken = $originalCode;
        if (filter_var($originalCode, FILTER_VALIDATE_URL)) {
            $segments = explode('/', parse_url($originalCode, PHP_URL_PATH));
            $extractedToken = end($segments);
        }
        $extractedToken = strtolower($extractedToken);

        $user = auth()->user();
        $cinemaId = $user?->cinema_id;
        if (!$cinemaId) {
            return response()->json(['success' => false, 'error' => 'Nhân viên chưa được phân công rạp.'], 403);
        }

        $warnings = [];
        $canCheckIn = false;
        $isOtherCinema = false;

        // 1. Tìm kiếm theo booking trên toàn hệ thống
        $booking = Booking::with([
            'user',
            'showtime.movie',
            'showtime.room.cinema',
            'bookedSeats.seat'
        ])
        ->where(function ($q) use ($code, $extractedToken) {
            $q->where('booking_code', $code);
            if (!empty($extractedToken)) {
                $q->orWhere('ticket_token', $extractedToken);
            }
        })
        ->first();

        if ($booking) {
            $ticketCinema = $booking->showtime?->room?->cinema;
            $ticketCinemaId = $ticketCinema?->id;
            $ticketCinemaName = $ticketCinema?->name ?? 'Rạp khác';
            $isOtherCinema = ($ticketCinemaId && $ticketCinemaId != $cinemaId);

            if ($isOtherCinema) {
                $warnings[] = "Vé này thuộc rạp {$ticketCinemaName}. Bạn chỉ có quyền tra cứu thông tin, không thể in vé tại rạp này.";
            } else {
                if ($booking->status === 'Pending') {
                    $warnings[] = "Vé chưa thanh toán (Trạng thái đơn: Chờ thanh toán).";
                } elseif ($booking->status === 'Cancelled') {
                    $warnings[] = "Đơn hàng vé này đã bị hủy.";
                }

                $showtime = $booking->showtime;
                if ($showtime) {
                    if ($showtime->status === Showtime::STATUS_CANCELLED) {
                        $warnings[] = "Suất chiếu đã bị hủy.";
                    } elseif ($showtime->status === Showtime::STATUS_COMPLETED || ($showtime->end_time && $showtime->end_time->isPast())) {
                        $warnings[] = "Suất chiếu đã kết thúc. Vé hết hạn.";
                    }
                }
            }

            // Chuẩn bị data trả về
            $seatsData = [];
            foreach ($booking->bookedSeats as $bs) {
                $seatsData[] = [
                    'id' => $bs->id,
                    'seat_code' => $bs->seat ? ($bs->seat->row_name . $bs->seat->seat_number) : 'N/A',
                    'price' => number_format($bs->price_at_booking) . 'đ',
                    'status' => $bs->status,
                    'qr_code' => $bs->qr_code,
                    'printed_at' => $bs->printed_at ? $bs->printed_at->format('d/m/Y H:i:s') : null,
                ];
            }

            return response()->json([
                'success' => true,
                'type' => 'booking',
                'is_other_cinema' => $isOtherCinema,
                'can_print' => !$isOtherCinema,
                'warnings' => $warnings,
                'data' => [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'customer_name' => $booking->user->name ?? ($booking->notes ?? 'Khách tại quầy'),
                    'customer_email' => $booking->user->email ?? 'N/A',
                    'movie_title' => $booking->showtime->movie->title ?? 'N/A',
                    'cinema_name' => $booking->showtime->room->cinema->name ?? 'N/A',
                    'room_name' => $booking->showtime->room->name ?? 'N/A',
                    'start_time' => $booking->showtime->start_time->format('d/m/Y H:i'),
                    'end_time' => $booking->showtime->end_time ? $booking->showtime->end_time->format('d/m/Y H:i') : 'N/A',
                    'total_price' => number_format($booking->total_price) . 'đ',
                    'status' => $booking->status,
                    'seats' => $seatsData,
                ]
            ]);
        }

        // 2. Tìm theo mã QR ghế trên toàn hệ thống
        $bookedSeat = BookedSeat::with([
            'seat',
            'booking.user',
            'booking.showtime.movie',
            'booking.showtime.room.cinema'
        ])
        ->where('qr_code', $code)
        ->first();

        if ($bookedSeat) {
            $booking = $bookedSeat->booking;
            $ticketCinema = $booking?->showtime?->room?->cinema;
            $ticketCinemaId = $ticketCinema?->id;
            $ticketCinemaName = $ticketCinema?->name ?? 'Rạp khác';
            $isOtherCinema = ($ticketCinemaId && $ticketCinemaId != $cinemaId);

            if ($isOtherCinema) {
                $warnings[] = "Vé này thuộc rạp {$ticketCinemaName}. Bạn chỉ có quyền tra cứu thông tin, không thể in vé tại rạp này.";
            } else {
                if ($bookedSeat->status === 'CANCELLED') {
                    $warnings[] = "Ghế này đã bị hủy bỏ.";
                } elseif ($bookedSeat->status === 'RESERVED') {
                    $warnings[] = "Vé chưa được thanh toán (Trạng thái ghế: Đã đặt trước).";
                }

                if ($booking) {
                    $showtime = $booking->showtime;
                    if ($showtime) {
                        if ($showtime->status === Showtime::STATUS_CANCELLED) {
                            $warnings[] = "Suất chiếu của vé này đã bị hủy.";
                        } elseif ($showtime->status === Showtime::STATUS_COMPLETED || ($showtime->end_time && $showtime->end_time->isPast())) {
                            $warnings[] = "Suất chiếu của vé này đã diễn ra hoặc đã kết thúc. Vé đã hết hạn.";
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'type' => 'seat',
                'is_other_cinema' => $isOtherCinema,
                'can_print' => !$isOtherCinema,
                'warnings' => $warnings,
                'data' => [
                    'id' => $bookedSeat->id,
                    'qr_code' => $bookedSeat->qr_code,
                    'seat_code' => $bookedSeat->seat ? ($bookedSeat->seat->row_name . $bookedSeat->seat->seat_number) : 'N/A',
                    'price' => number_format($bookedSeat->price_at_booking) . 'đ',
                    'status' => $bookedSeat->status,
                    'printed_at' => $bookedSeat->printed_at ? $bookedSeat->printed_at->format('d/m/Y H:i:s') : null,
                    'booking_code' => $booking->booking_code ?? 'N/A',
                    'customer_name' => $booking->user->name ?? 'N/A',
                    'movie_title' => $booking->showtime->movie->title ?? 'N/A',
                    'cinema_name' => $booking->showtime->room->cinema->name ?? 'N/A',
                    'room_name' => $booking->showtime->room->name ?? 'N/A',
                    'start_time' => $booking->showtime->start_time->format('d/m/Y H:i'),
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Mã vé hoặc mã QR không tồn tại trên hệ thống. Vui lòng kiểm tra lại.'
        ], 404);
    }

    /**
     * Xử lý xác nhận Check-in
     */
    public function checkIn(Request $request)
    {
        $type = $request->input('type'); // 'booking' or 'seat'
        $id = $request->input('id'); // booking_id or booked_seat_id
        $seatId = $request->input('seat_id'); // Optional, checkin specific seat from booking search

        $user = auth()->user();
        $cinemaId = $user?->cinema_id;
        if (!$cinemaId) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Nhân viên chưa được phân công rạp.'], 403);
            }
            return back()->with('error', 'Nhân viên chưa được phân công rạp.');
        }

        try {
            DB::beginTransaction();

            if ($type === 'booking') {
                $booking = Booking::with(['bookedSeats', 'showtime.room.cinema'])->find($id);

                if (!$booking) {
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng.'], 404);
                    }
                    return back()->with('error', 'Không tìm thấy đơn hàng.');
                }

                $ticketCinemaId = $booking->showtime?->room?->cinema_id;
                $ticketCinemaName = $booking->showtime?->room?->cinema?->name ?? 'Rạp khác';
                if ($ticketCinemaId && $ticketCinemaId != $cinemaId) {
                    $errMsg = "Không thể check-in: Đơn hàng này thuộc rạp '{$ticketCinemaName}'. Bạn chỉ có quyền tra cứu thông tin, không thể check-in vé của rạp khác.";
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => $errMsg], 403);
                    }
                    return back()->with('error', $errMsg);
                }

                if ($booking->status === 'Pending') {
                    return back()->with('error', 'Đơn hàng chưa thanh toán, không thể check-in.');
                }

                if ($booking->status === 'Cancelled') {
                    return back()->with('error', 'Đơn hàng đã bị hủy, không thể check-in.');
                }

                // Check showtime
                if ($booking->showtime->status === Showtime::STATUS_COMPLETED || ($booking->showtime->end_time && $booking->showtime->end_time->isPast())) {
                    return back()->with('error', 'Suất chiếu đã kết thúc. Vé đã hết hạn.');
                }

                $checkedCount = 0;

                if ($seatId) {
                    // Check-in cụ thể 1 ghế trong booking
                    $bookedSeat = BookedSeat::where('booking_id', $booking->id)->where('id', $seatId)->first();
                    if (!$bookedSeat) {
                        return back()->with('error', 'Không tìm thấy ghế yêu cầu.');
                    }
                    if ($bookedSeat->status !== 'PAID') {
                        return back()->with('error', 'Ghế này không ở trạng thái hợp lệ để check-in.');
                    }
                    if ($bookedSeat->checkin()) {
                        $checkedCount = 1;
                    } else {
                        return back()->with('error', 'Ghế này đã được check-in trước đó.');
                    }
                } else {
                    // Check-in toàn bộ các ghế PAID trong booking
                    $paidSeats = $booking->bookedSeats->where('status', 'PAID');
                    if ($paidSeats->count() == 0) {
                        return back()->with('error', 'Không có ghế nào đủ điều kiện check-in.');
                    }

                    foreach ($paidSeats as $seat) {
                        if ($seat->checkin()) {
                            $checkedCount++;
                        }
                    }
                    
                    if ($checkedCount == 0) {
                        return back()->with('error', 'Các ghế này đã được check-in trước đó.');
                    }
                }

                // Reload booking seats to re-evaluate total status
                $booking->load('bookedSeats');
                $totalSeats = $booking->bookedSeats->count();
                $usedSeats = $booking->bookedSeats->where('status', 'USED')->count();
                $cancelledSeats = $booking->bookedSeats->where('status', 'CANCELLED')->count();

                if (($usedSeats + $cancelledSeats) === $totalSeats) {
                    $booking->update(['status' => 'Used']);
                }

                DB::commit();

                // ── Broadcast LiveTicketScanned to Staff Channel ──
                try {
                    $cinemaIdTarget = $booking->showtime?->room?->cinema_id ?? ($cinemaId ?? 1);
                    $movieTitle = $booking->showtime?->movie?->title ?? 'N/A';
                    $roomName = $booking->showtime?->room?->name ?? 'N/A';
                    $showtimeFormatted = $booking->showtime?->start_time ? $booking->showtime->start_time->format('H:i d/m/Y') : 'N/A';
                    $seatCodes = $booking->bookedSeats->map(fn($s) => $s->seat ? ($s->seat->row_name . $s->seat->seat_number) : '')->filter()->implode(', ');

                    event(new \App\Events\LiveTicketScanned(
                        $cinemaIdTarget,
                        $booking->booking_code ?? 'N/A',
                        $seatCodes,
                        $movieTitle,
                        $roomName,
                        $showtimeFormatted,
                        'SUCCESS',
                        auth()->user()?->name ?? 'Nhân viên',
                        "Đã check-in thành công {$checkedCount} ghế."
                    ));
                } catch (\Throwable $evEx) {
                    \Illuminate\Support\Facades\Log::warning('Broadcasting LiveTicketScanned failed: ' . $evEx->getMessage());
                }

                // Trả về JSON nếu là request AJAX, hoặc redirect back
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => "Đã check-in thành công {$checkedCount} ghế."
                    ]);
                }

                return redirect()->route('staff.ticket.search', ['code' => $booking->booking_code])
                    ->with('success', "Đã check-in thành công {$checkedCount} ghế của đơn hàng.");

            } elseif ($type === 'seat') {
                $bookedSeat = BookedSeat::with(['booking', 'booking.showtime.room.cinema'])->find($id);

                if (!$bookedSeat) {
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => 'Không tìm thấy vé.'], 404);
                    }
                    return back()->with('error', 'Không tìm thấy vé.');
                }

                $ticketCinemaId = $bookedSeat->booking?->showtime?->room?->cinema_id;
                $ticketCinemaName = $bookedSeat->booking?->showtime?->room?->cinema?->name ?? 'Rạp khác';
                if ($ticketCinemaId && $ticketCinemaId != $cinemaId) {
                    $errMsg = "Không thể check-in: Vé này thuộc rạp '{$ticketCinemaName}'. Bạn chỉ có quyền tra cứu thông tin, không thể check-in vé của rạp khác.";
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => $errMsg], 403);
                    }
                    return back()->with('error', $errMsg);
                }

                $booking = $bookedSeat->booking;

                if ($bookedSeat->status !== 'PAID') {
                    return back()->with('error', 'Vé này đã được sử dụng hoặc chưa sẵn sàng check-in.');
                }

                if ($booking) {
                    // Check showtime
                    if ($booking->showtime->status === Showtime::STATUS_COMPLETED || ($booking->showtime->end_time && $booking->showtime->end_time->isPast())) {
                        return back()->with('error', 'Suất chiếu đã kết thúc. Vé đã hết hạn.');
                    }
                }

                if (!$bookedSeat->checkin()) {
                    return back()->with('error', 'Ghế này đã được check-in trước đó.');
                }

                if ($booking) {
                    $totalSeats = $booking->bookedSeats()->count();
                    $usedSeats = $booking->bookedSeats()->where('status', 'USED')->count();
                    $cancelledSeats = $booking->bookedSeats()->where('status', 'CANCELLED')->count();

                    if (($usedSeats + $cancelledSeats) === $totalSeats) {
                        $booking->update(['status' => 'Used']);
                    }
                }

                DB::commit();

                // ── Broadcast LiveTicketScanned for single seat ──
                try {
                    $cinemaIdTarget = $booking?->showtime?->room?->cinema_id ?? ($cinemaId ?? 1);
                    $movieTitle = $booking?->showtime?->movie?->title ?? 'N/A';
                    $roomName = $booking?->showtime?->room?->name ?? 'N/A';
                    $showtimeFormatted = $booking?->showtime?->start_time ? $booking->showtime->start_time->format('H:i d/m/Y') : 'N/A';
                    $seatCode = $bookedSeat->seat ? ($bookedSeat->seat->row_name . $bookedSeat->seat->seat_number) : 'Ghế';

                    event(new \App\Events\LiveTicketScanned(
                        $cinemaIdTarget,
                        $booking?->booking_code ?? 'N/A',
                        $seatCode,
                        $movieTitle,
                        $roomName,
                        $showtimeFormatted,
                        'SUCCESS',
                        auth()->user()?->name ?? 'Nhân viên',
                        "Đã check-in thành công cho ghế {$seatCode}."
                    ));
                } catch (\Throwable $evEx) {
                    \Illuminate\Support\Facades\Log::warning('Broadcasting LiveTicketScanned failed: ' . $evEx->getMessage());
                }

                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => "Đã check-in thành công cho ghế " . ($bookedSeat->seat ? ($bookedSeat->seat->row_name . $bookedSeat->seat->seat_number) : '') . "."
                    ]);
                }

                return redirect()->route('staff.ticket.search', ['code' => $bookedSeat->qr_code])
                    ->with('success', "Đã check-in thành công cho ghế " . ($bookedSeat->seat ? ($bookedSeat->seat->row_name . $bookedSeat->seat->seat_number) : '') . ".");
            }

            return back()->with('error', 'Loại yêu cầu check-in không hợp lệ.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Lỗi check-in: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * In vé khổ K80
     */
    public function printTicket(Request $request, $type, $id)
    {
        $user = auth()->user();
        $cinemaId = $user?->cinema_id;
        if (!$cinemaId) {
            abort(403, 'Nhân viên chưa được phân công rạp.');
        }

        $seatsToPrint = collect();
        $booking = null;

        if ($type === 'booking') {
            $booking = Booking::with([
                'user',
                'showtime.movie',
                'showtime.room.cinema',
                'bookedSeats.seat',
                'combos'
            ])->find($id);

            if (!$booking) {
                abort(404, 'Không tìm thấy đơn hàng.');
            }

            $ticketCinemaId = $booking->showtime?->room?->cinema_id;
            $ticketCinemaName = $booking->showtime?->room?->cinema?->name ?? 'Rạp khác';
            if ($ticketCinemaId && $ticketCinemaId != $cinemaId) {
                abort(403, "Nhân viên không có quyền in vé của rạp khác ({$ticketCinemaName}).");
            }

            $seatsToPrint = $booking->bookedSeats;
            foreach ($seatsToPrint as $seatItem) {
                $seatItem->increment('print_count');
                $seatItem->update(['printed_at' => now()]);
            }
        } elseif ($type === 'seat') {
            $bookedSeat = BookedSeat::with([
                'seat',
                'booking.user',
                'booking.showtime.movie',
                'booking.showtime.room.cinema',
                'booking.combos'
            ])->find($id);
            
            if (!$bookedSeat) {
                abort(404, 'Không tìm thấy vé.');
            }

            $ticketCinemaId = $bookedSeat->booking?->showtime?->room?->cinema_id;
            $ticketCinemaName = $bookedSeat->booking?->showtime?->room?->cinema?->name ?? 'Rạp khác';
            if ($ticketCinemaId && $ticketCinemaId != $cinemaId) {
                abort(403, "Nhân viên không có quyền in vé của rạp khác ({$ticketCinemaName}).");
            }

            $booking = $bookedSeat->booking;

            $bookedSeat->increment('print_count');
            $bookedSeat->update(['printed_at' => now()]);
            $seatsToPrint->push($bookedSeat);
        } else {
            return abort(404, 'Loại in vé không hợp lệ.');
        }

        if ($seatsToPrint->isEmpty()) {
            return abort(404, 'Không có ghế nào để in.');
        }

        return view('staff.ticket.print', compact('seatsToPrint', 'booking', 'type'));
    }
}
