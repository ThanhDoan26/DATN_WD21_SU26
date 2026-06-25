<?php

namespace App\Http\Controllers;

use App\Services\BookingHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingHistoryController extends Controller
{
    protected $bookingService;

    public function __construct(BookingHistoryService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Display a listing of the user's bookings.
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $bookings = $this->bookingService->getUserBookings($userId);

        return view('user.booking-history.index', compact('bookings'));
    }

    /**
     * Display the specified booking detail.
     */
    public function show(string $bookingCode): View
    {
        $userId = Auth::id();
        $booking = $this->bookingService->getBookingDetails($bookingCode, $userId);

        if (!$booking) {
            abort(404, 'Không tìm thấy thông tin đặt vé.');
        }

        return view('user.booking-history.show', compact('booking'));
    }
}
