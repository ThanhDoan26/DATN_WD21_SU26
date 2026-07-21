<?php

namespace App\Observers;

use App\Models\Booking;
use App\Mail\TicketConfirmationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Showtime;
use App\Services\BookingService;

class BookingObserver
{
    /**
     * Mảng static để theo dõi các đơn hàng đã được kích hoạt gửi email trong cùng một request.
     * Tránh việc gửi trùng lặp nếu cả controller và observer đều chạy.
     */
    public static array $sentBookings = [];

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        Log::info("BookingObserver: Bắt đầu sự kiện updated cho đơn hàng ID {$booking->id}, Status hiện tại: {$booking->status}");

        // Chỉ gửi email khi thuộc tính status thay đổi và giá trị mới là 'Paid'
        if ($booking->isDirty('status') && $booking->status === 'Paid') {
            
            // Nếu đơn hàng này đã được kích hoạt gửi email trong request hiện tại, bỏ qua
            if (in_array($booking->id, self::$sentBookings)) {
                Log::info("BookingObserver: TicketConfirmationMail KHÔNG được gọi vì đơn hàng ID {$booking->id} đã kích hoạt gửi email trong request này.");
                return;
            }

            self::$sentBookings[] = $booking->id;

            Log::info("BookingObserver: Phát hiện đơn hàng {$booking->booking_code} chuyển sang trạng thái Paid.");

            try {
                $bookingService = new BookingService();
                $bookingDetails = $bookingService->getBookingDetails($booking->id);
                $showtime = Showtime::with(['movie', 'room.cinema'])->find($booking->showtime_id);
                
                // Email người nhận: ưu tiên email khách vãng lai (customer_email) rồi tới email tài khoản (user->email)
                $email = $booking->customer_email ?? $booking->user?->email;

                if ($email) {
                    // Gửi email xác nhận vé (sẽ tự động đưa vào queue do TicketConfirmationMail implements ShouldQueue)
                    Log::info("BookingObserver: Đang chuẩn bị gọi Mail::to()->send() cho {$email}...");
                    Mail::to($email)->send(new TicketConfirmationMail($bookingDetails, $showtime));
                    Log::info("BookingObserver: Đã đưa email E-Ticket của đơn hàng {$booking->booking_code} vào hàng đợi gửi cho {$email}.");
                } else {
                    Log::warning("BookingObserver: TicketConfirmationMail KHÔNG được gọi vì không tìm thấy địa chỉ email của khách hàng cho đơn hàng ID {$booking->id}.");
                }
            } catch (\Exception $e) {
                Log::error("BookingObserver: Lỗi khi xử lý gửi email E-Ticket cho đơn hàng {$booking->booking_code}. Lỗi: " . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        } else {
            Log::info("BookingObserver: TicketConfirmationMail KHÔNG được gọi do status không chuyển sang Paid (isDirty: " . ($booking->isDirty('status') ? 'true' : 'false') . ", status mới: {$booking->status}).");
        }
    }
}
