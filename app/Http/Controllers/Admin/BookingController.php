<?php

namespace App\Http\Controllers\Admin;

use App\Models\Booking;
use App\Models\User;
use App\Models\Showtime;
use App\Services\SeatMapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * BookingController
 * ========================================
 * Controller quản lý bookings
 */
class BookingController extends AdminController
{
    /**
     * Display a listing of bookings
     */
    public function index()
    {
        // Get filter parameters
        $search = request('search');
        $status = request('status');
        $fromDate = request('from_date');
        $toDate = request('to_date');
        $paymentMethod = request('payment_method');
        $minPrice = request('min_price');
        $maxPrice = request('max_price');
        $sortBy = request('sort_by', 'id');
        $sortOrder = request('sort_order', 'desc');
        $perPage = request('per_page', 10);

        // Build query with filters
        $query = Booking::with(['user', 'showtime', 'showtime.movie', 'showtime.room', 'showtime.room.cinema', 'bookedSeats'])
            ->when($search, function($q) use ($search) {
                return $q->where('booking_code', 'like', "%$search%")
                         ->orWhereHas('user', function($q) use ($search) {
                             $q->where('name', 'like', "%$search%")
                               ->orWhere('email', 'like', "%$search%");
                         });
            })
            ->when($status, function($q) use ($status) {
                return $q->where('status', $status);
            })
            ->when($fromDate, function($q) use ($fromDate) {
                return $q->whereDate('booking_time', '>=', $fromDate);
            })
            ->when($toDate, function($q) use ($toDate) {
                return $q->whereDate('booking_time', '<=', $toDate);
            })
            ->when($paymentMethod, function($q) use ($paymentMethod) {
                return $q->where('payment_method', $paymentMethod);
            })
            ->when($minPrice, function($q) use ($minPrice) {
                return $q->where('total_price', '>=', $minPrice);
            })
            ->when($maxPrice, function($q) use ($maxPrice) {
                return $q->where('total_price', '<=', $maxPrice);
            });

        // Get status counts for filter buttons
        $statusCounts = [
            'all' => Booking::count(),
            'Paid' => Booking::where('status', 'Paid')->count(),
            'Pending' => Booking::where('status', 'Pending')->count(),
            'Used' => Booking::where('status', 'Used')->count(),
            'Cancelled' => Booking::where('status', 'Cancelled')->count(),
        ];

        // Get distinct payment methods
        $paymentMethods = Booking::whereNotNull('payment_method')
            ->distinct()
            ->pluck('payment_method')
            ->sort()
            ->values();

        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $bookings = $query->paginate((int)$perPage)->appends(request()->query());

        // Get min/max prices for filter range
        $priceStats = Booking::selectRaw('MIN(total_price) as min_price, MAX(total_price) as max_price')->first();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'statusCounts' => $statusCounts,
            'paymentMethods' => $paymentMethods,
            'priceStats' => $priceStats,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'payment_method' => $paymentMethod,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'per_page' => $perPage,
            ]
        ]);
    }

    /**
     * Show the form for creating a new booking
     */
    public function create()
    {
        $users = User::where('status', 'ACTIVE')->get();
        $showtimes = Showtime::with(['movie', 'room.cinema'])->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])->get();
        return view('admin.bookings.create', compact('users', 'showtimes'));
    }

    /**
     * Store a newly created booking in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'showtime_id' => 'required|exists:showtimes,id',
            'total_price' => 'required|numeric|min:0',
            'status' => 'required|in:Pending,Paid,Cancelled,Used',
            'payment_method' => 'nullable|string|max:100',
            'booking_code' => 'required|string|max:50|unique:bookings,booking_code',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['booking_time'] = now();
        if ($validated['status'] === 'Paid') {
            $validated['payment_time'] = now();
        }

        Booking::create($validated);

        return redirect()->route('admin.bookings.index')
                         ->with('success', 'Tạo đơn hàng thành công!');
    }

    /**
     * Show the form for editing a booking
     */
    public function show(Booking $booking)
    {
        $booking = $booking->load(['user', 'showtime', 'showtime.movie', 'showtime.room', 'showtime.room.cinema', 'bookedSeats', 'bookedSeats.seat']);
        $seatMapService = new SeatMapService();
        $seatMapData = $seatMapService->generateSeatMapData($booking);
        return view('admin.bookings.show', compact('booking', 'seatMapData'));
    }

    /**
     * Show the form for editing a booking
     */
    public function edit(Booking $booking)
    {
        $users = User::where('status', 'ACTIVE')
            ->orWhere('id', $booking->user_id)
            ->get();
            
        $showtimes = Showtime::with(['movie', 'room.cinema'])
            ->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
            ->orWhere('id', $booking->showtime_id)
            ->get();
            
        return view('admin.bookings.edit', compact('booking', 'users', 'showtimes'));
    }

    /**
     * Update a booking in storage
     */
    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'showtime_id' => 'required|exists:showtimes,id',
            'total_price' => 'required|numeric|min:0',
            'status' => 'required|in:Pending,Paid,Cancelled,Used',
            'payment_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($booking, $validated) {
            $oldStatus = $booking->status;
            $newStatus = $validated['status'];

            if ($newStatus === 'Paid' && $oldStatus !== 'Paid') {
                $validated['payment_time'] = now();
                $booking->bookedSeats()->update(['status' => 'CONFIRMED']);
            }

            if ($newStatus === 'Cancelled' && $oldStatus !== 'Cancelled') {
                $validated['cancelled_at'] = now();

                // Hoàn lại lượt dùng mã giảm giá nếu có
                if ($booking->coupon_id && in_array($oldStatus, ['Paid', 'Pending', 'SUCCESS'])) {
                    $booking->coupon()->decrement('used_count');
                }

                // Cập nhật trạng thái ghế đã đặt
                $booking->bookedSeats()->update(['status' => 'CANCELLED']);
            }

            $booking->update($validated);
        });

        return redirect()->route('admin.bookings.index')
                         ->with('success', 'Cập nhật đơn hàng thành công!');
    }

    /**
     * Delete a booking from storage
     */
    public function destroy(Booking $booking)
    {
        if (in_array($booking->status, ['Paid', 'Used', 'SUCCESS'])) {
            return redirect()->route('admin.bookings.index')
                ->with('error', 'Không thể xóa đơn hàng đã thanh toán hoặc đã sử dụng để bảo toàn dữ liệu tài chính và báo cáo doanh thu. Vui lòng chuyển trạng thái sang "Cancelled" nếu cần hủy đơn.');
        }

        DB::transaction(function () use ($booking) {
            if ($booking->coupon_id && in_array($booking->status, ['Paid', 'Pending', 'SUCCESS'])) {
                $booking->coupon()->decrement('used_count');
            }

            $booking->bookedSeats()->delete();
            $booking->combos()->detach();
            $booking->delete();
        });

        return redirect()->route('admin.bookings.index')
                         ->with('success', 'Xóa đơn hàng thành công!');
    }
}
