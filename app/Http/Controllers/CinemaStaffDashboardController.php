<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\BookedSeat;
use App\Models\Showtime;
use Illuminate\Support\Facades\DB;

class CinemaStaffDashboardController extends Controller
{
    /**
     * Hiển thị trang Dashboard cho Cinema Staff
     */
    public function index()
    {
        $user = auth()->user();
        $cinemaId = $user->cinema_id;

        // Base queries for booked seats
        $baseSeatsQuery = BookedSeat::query();

        if ($cinemaId) {
            $baseSeatsQuery->whereHas('booking.showtime.room', function ($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            });
        }

        // 1. Vé check-in hôm nay (status = USED, checked_in_at là hôm nay)
        $checkedInToday = (clone $baseSeatsQuery)
            ->where('status', 'USED')
            ->whereDate('checked_in_at', today())
            ->count();

        // 2. Vé chưa sử dụng (status = PAID)
        $unusedTickets = (clone $baseSeatsQuery)
            ->where('status', 'PAID')
            ->count();

        // 3. Vé đã sử dụng (status = USED)
        $usedTickets = (clone $baseSeatsQuery)
            ->where('status', 'USED')
            ->count();

        return view('staff.dashboard.index', compact('checkedInToday', 'unusedTickets', 'usedTickets'));
    }

    /**
     * Màn hình tra cứu vé
     */
    public function searchForm(Request $request)
    {
        $code = $request->query('code');
        $result = null;
        $warnings = [];
        $canCheckIn = false;
        $searchType = null; // 'booking' or 'seat'

        if ($code) {
            $code = trim(strtoupper($code));
            $user = auth()->user();
            $cinemaId = $user->cinema_id;

            // 1. Tìm theo mã booking
            $booking = Booking::with([
                'user',
                'showtime',
                'showtime.movie',
                'showtime.room',
                'showtime.room.cinema',
                'bookedSeats',
                'bookedSeats.seat'
            ])->where('booking_code', $code)->first();

            if ($booking) {
                $result = $booking;
                $searchType = 'booking';

                // Kiểm tra rạp (nếu nhân viên thuộc rạp cụ thể)
                if ($cinemaId && $booking->showtime->room->cinema_id != $cinemaId) {
                    $warnings[] = "Vé thuộc rạp khác: " . ($booking->showtime->room->cinema->name ?? 'N/A') . ". Không thể check-in tại rạp hiện tại.";
                }

                // Kiểm tra trạng thái thanh toán
                if ($booking->status === 'Pending') {
                    $warnings[] = "Vé chưa thanh toán (Trạng thái đơn: Chờ thanh toán). Vui lòng yêu cầu khách thanh toán trước.";
                } elseif ($booking->status === 'Cancelled') {
                    $warnings[] = "Đơn hàng vé này đã bị hủy.";
                }

                // Kiểm tra trạng thái sử dụng của các ghế
                $allSeats = $booking->bookedSeats;
                $usedSeatsCount = $allSeats->where('status', 'USED')->count();
                $paidSeatsCount = $allSeats->where('status', 'PAID')->count();
                $cancelledSeatsCount = $allSeats->where('status', 'CANCELLED')->count();

                if ($allSeats->count() > 0) {
                    if ($usedSeatsCount == $allSeats->count() || $booking->status === 'Used') {
                        $warnings[] = "Vé này đã được sử dụng (Đã check-in toàn bộ ghế).";
                    } elseif ($paidSeatsCount === 0 && $usedSeatsCount > 0) {
                        $warnings[] = "Vé này đã được check-in một phần, các ghế còn lại không hợp lệ để check-in.";
                    }
                } else {
                    $warnings[] = "Đơn hàng này không có ghế nào được đăng ký.";
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

                // Có thể checkin nếu: Đơn hàng 'Paid' và có ít nhất 1 ghế có trạng thái 'PAID'
                // và không bị sai rạp
                $hasEligibleSeats = $paidSeatsCount > 0;
                $isCorrectCinema = !$cinemaId || $booking->showtime->room->cinema_id == $cinemaId;
                if ($booking->status === 'Paid' && $hasEligibleSeats && $isCorrectCinema) {
                    $canCheckIn = true;
                }
            } else {
                // 2. Tìm theo mã QR ghế
                $bookedSeat = BookedSeat::with([
                    'seat',
                    'booking',
                    'booking.user',
                    'booking.showtime',
                    'booking.showtime.movie',
                    'booking.showtime.room',
                    'booking.showtime.room.cinema'
                ])->where('qr_code', $code)->first();

                if ($bookedSeat) {
                    $result = $bookedSeat;
                    $searchType = 'seat';
                    $booking = $bookedSeat->booking;

                    // Kiểm tra rạp
                    if ($booking && $cinemaId && $booking->showtime->room->cinema_id != $cinemaId) {
                        $warnings[] = "Vé thuộc rạp khác: " . ($booking->showtime->room->cinema->name ?? 'N/A') . ". Không thể check-in tại rạp hiện tại.";
                    }

                    // Kiểm tra trạng thái ghế
                    if ($bookedSeat->status === 'USED') {
                        $warnings[] = "Ghế này đã được sử dụng (Check-in vào lúc: " . ($bookedSeat->checked_in_at ? $bookedSeat->checked_in_at->format('d/m/Y H:i:s') : 'N/A') . ").";
                    } elseif ($bookedSeat->status === 'CANCELLED') {
                        $warnings[] = "Ghế này đã bị hủy bỏ.";
                    } elseif ($bookedSeat->status === 'RESERVED') {
                        $warnings[] = "Vé chưa được thanh toán (Trạng thái ghế: Đã đặt trước). Vui lòng yêu cầu thanh toán.";
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

                    $isCorrectCinema = !$booking || !$cinemaId || $booking->showtime->room->cinema_id == $cinemaId;
                    if ($bookedSeat->status === 'PAID' && $isCorrectCinema) {
                        $canCheckIn = true;
                    }
                } else {
                    // Không tìm thấy vé
                    $warnings[] = "Mã vé hoặc mã QR không tồn tại trên hệ thống. Vui lòng kiểm tra lại.";
                }
            }
        }

        return view('staff.ticket.search', compact('code', 'result', 'searchType', 'warnings', 'canCheckIn'));
    }

    /**
     * API lookup phục vụ cho quét QR qua AJAX
     */
    public function lookup(Request $request)
    {
        $code = trim(strtoupper($request->query('code')));
        if (!$code) {
            return response()->json(['success' => false, 'error' => 'Vui lòng cung cấp mã vé.'], 400);
        }

        $user = auth()->user();
        $cinemaId = $user->cinema_id;
        $warnings = [];
        $canCheckIn = false;

        // Tìm kiếm
        $booking = Booking::with([
            'user',
            'showtime',
            'showtime.movie',
            'showtime.room',
            'showtime.room.cinema',
            'bookedSeats',
            'bookedSeats.seat'
        ])->where('booking_code', $code)->first();

        if ($booking) {
            if ($cinemaId && $booking->showtime->room->cinema_id != $cinemaId) {
                $warnings[] = "Vé thuộc rạp khác: " . ($booking->showtime->room->cinema->name ?? 'N/A') . ". Không thể check-in tại rạp hiện tại.";
            }

            if ($booking->status === 'Pending') {
                $warnings[] = "Vé chưa thanh toán (Trạng thái đơn: Chờ thanh toán).";
            } elseif ($booking->status === 'Cancelled') {
                $warnings[] = "Đơn hàng vé này đã bị hủy.";
            }

            $allSeats = $booking->bookedSeats;
            $usedSeatsCount = $allSeats->where('status', 'USED')->count();
            $paidSeatsCount = $allSeats->where('status', 'PAID')->count();

            if ($allSeats->count() > 0) {
                if ($usedSeatsCount == $allSeats->count() || $booking->status === 'Used') {
                    $warnings[] = "Vé đã được sử dụng (Đã check-in toàn bộ ghế).";
                }
            } else {
                $warnings[] = "Đơn hàng không có ghế.";
            }

            $showtime = $booking->showtime;
            if ($showtime) {
                if ($showtime->status === Showtime::STATUS_CANCELLED) {
                    $warnings[] = "Suất chiếu đã bị hủy.";
                } elseif ($showtime->status === Showtime::STATUS_COMPLETED || ($showtime->end_time && $showtime->end_time->isPast())) {
                    $warnings[] = "Suất chiếu đã kết thúc. Vé hết hạn.";
                }
            }

            $hasEligibleSeats = $paidSeatsCount > 0;
            $isCorrectCinema = !$cinemaId || $booking->showtime->room->cinema_id == $cinemaId;
            if ($booking->status === 'Paid' && $hasEligibleSeats && $isCorrectCinema) {
                $canCheckIn = true;
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
                    'checked_in_at' => $bs->checked_in_at ? $bs->checked_in_at->format('d/m/Y H:i:s') : null,
                ];
            }

            return response()->json([
                'success' => true,
                'type' => 'booking',
                'can_checkin' => $canCheckIn,
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

        // Tìm theo mã QR ghế
        $bookedSeat = BookedSeat::with([
            'seat',
            'booking',
            'booking.user',
            'booking.showtime',
            'booking.showtime.movie',
            'booking.showtime.room',
            'booking.showtime.room.cinema'
        ])->where('qr_code', $code)->first();

        if ($bookedSeat) {
            $booking = $bookedSeat->booking;

            if ($booking && $cinemaId && $booking->showtime->room->cinema_id != $cinemaId) {
                $warnings[] = "Vé thuộc rạp khác: " . ($booking->showtime->room->cinema->name ?? 'N/A') . ". Không thể check-in tại rạp hiện tại.";
            }

            if ($bookedSeat->status === 'USED') {
                $warnings[] = "Ghế này đã được sử dụng (Check-in vào lúc: " . ($bookedSeat->checked_in_at ? $bookedSeat->checked_in_at->format('d/m/Y H:i:s') : 'N/A') . ").";
            } elseif ($bookedSeat->status === 'CANCELLED') {
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

            $isCorrectCinema = !$booking || !$cinemaId || $booking->showtime->room->cinema_id == $cinemaId;
            if ($bookedSeat->status === 'PAID' && $isCorrectCinema) {
                $canCheckIn = true;
            }

            return response()->json([
                'success' => true,
                'type' => 'seat',
                'can_checkin' => $canCheckIn,
                'warnings' => $warnings,
                'data' => [
                    'id' => $bookedSeat->id,
                    'qr_code' => $bookedSeat->qr_code,
                    'seat_code' => $bookedSeat->seat ? ($bookedSeat->seat->row_name . $bookedSeat->seat->seat_number) : 'N/A',
                    'price' => number_format($bookedSeat->price_at_booking) . 'đ',
                    'status' => $bookedSeat->status,
                    'checked_in_at' => $bookedSeat->checked_in_at ? $bookedSeat->checked_in_at->format('d/m/Y H:i:s') : null,
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
        $cinemaId = $user->cinema_id;

        try {
            DB::beginTransaction();

            if ($type === 'booking') {
                $booking = Booking::with(['bookedSeats', 'showtime.room'])->findOrFail($id);

                // Security check
                if ($cinemaId && $booking->showtime->room->cinema_id != $cinemaId) {
                    return back()->with('error', 'Không thể check-in vé thuộc rạp khác.');
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
                    $bookedSeat = BookedSeat::where('booking_id', $booking->id)->where('id', $seatId)->firstOrFail();
                    if ($bookedSeat->status !== 'PAID') {
                        return back()->with('error', 'Ghế này không ở trạng thái hợp lệ để check-in.');
                    }
                    $bookedSeat->checkin();
                    $checkedCount = 1;
                } else {
                    // Check-in toàn bộ các ghế PAID trong booking
                    $paidSeats = $booking->bookedSeats->where('status', 'PAID');
                    if ($paidSeats->count() == 0) {
                        return back()->with('error', 'Không có ghế nào đủ điều kiện check-in.');
                    }

                    foreach ($paidSeats as $seat) {
                        $seat->checkin();
                        $checkedCount++;
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
                $bookedSeat = BookedSeat::with(['booking', 'booking.showtime.room'])->findOrFail($id);
                $booking = $bookedSeat->booking;

                if ($cinemaId && $booking && $booking->showtime->room->cinema_id != $cinemaId) {
                    return back()->with('error', 'Không thể check-in vé thuộc rạp khác.');
                }

                if ($bookedSeat->status !== 'PAID') {
                    return back()->with('error', 'Vé này đã được sử dụng hoặc chưa sẵn sàng check-in.');
                }

                if ($booking) {
                    // Check showtime
                    if ($booking->showtime->status === Showtime::STATUS_COMPLETED || ($booking->showtime->end_time && $booking->showtime->end_time->isPast())) {
                        return back()->with('error', 'Suất chiếu đã kết thúc. Vé đã hết hạn.');
                    }
                }

                $bookedSeat->checkin();

                if ($booking) {
                    $totalSeats = $booking->bookedSeats()->count();
                    $usedSeats = $booking->bookedSeats()->where('status', 'USED')->count();
                    $cancelledSeats = $booking->bookedSeats()->where('status', 'CANCELLED')->count();

                    if (($usedSeats + $cancelledSeats) === $totalSeats) {
                        $booking->update(['status' => 'Used']);
                    }
                }

                DB::commit();

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
}
