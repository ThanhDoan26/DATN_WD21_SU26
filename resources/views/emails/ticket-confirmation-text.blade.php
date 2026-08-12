Chào {{ $booking['customer_name'] ?? 'Khách hàng' }},

Cảm ơn bạn đã đặt vé tại movieGo! Đơn hàng của bạn đã được thanh toán thành công.

Dưới đây là thông tin vé điện tử (E-Ticket) chính thức của bạn:

--- THÔNG TIN ĐƠN HÀNG ---
- Mã đơn hàng: {{ $booking['booking_code'] ?? 'N/A' }}
@if(!empty($booking['customer_phone']))
- Số điện thoại: {{ $booking['customer_phone'] }}
@endif
- Ngày đặt vé: {{ isset($booking['booking_time']) ? \Carbon\Carbon::parse($booking['booking_time'])->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}
- Thời gian thanh toán: {{ !empty($booking['payment_time']) ? \Carbon\Carbon::parse($booking['payment_time'])->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}
- Hình thức thanh toán: {{ $booking['payment_method'] ?? 'Online' }}

--- VÉ XEM PHIM ---
- Phim: {{ $showtime->movie->title ?? 'N/A' }}
- Rạp chiếu: {{ $showtime->room->cinema->name ?? 'N/A' }}
- Địa chỉ: {{ $showtime->room->cinema->address ?? 'N/A' }}
- Phòng chiếu: {{ $showtime->room->name ?? 'N/A' }}
- Suất chiếu: {{ isset($showtime->start_time) ? \Carbon\Carbon::parse($showtime->start_time)->format('H:i d/m/Y') : 'N/A' }}
- Ghế đã chọn: {{ $seatsList ?? 'N/A' }}

* Vui lòng xem và quét mã QR trong file PDF đính kèm tại rạp để quét vào phòng chiếu.

--- CHI TIẾT THANH TOÁN ---
- Tổng thanh toán: {{ number_format($booking['total_price'] ?? 0, 0, ',', '.') }} đ

--- LƯU Ý QUAN TRỌNG ---
1. Vui lòng có mặt tại rạp trước giờ chiếu 30 phút để nhận vé hoặc vào phòng.
2. Mang theo email này hoặc file vé PDF đính kèm để quét mã vào rạp.
3. Vé QR Code chỉ có giá trị quét một lần duy nhất. Tuyệt đối không chia sẻ mã này cho người khác.

Hotline hỗ trợ: 1900 6017 | Email: support@moviego.com
© {{ date('Y') }} movieGo Cinema. All rights reserved.
