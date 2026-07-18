<?php

namespace App\Services;

use Carbon\Carbon;

class GoogleCalendarService
{
    /**
     * Tạo link thêm nhanh vào Google Calendar
     *
     * @param string $movieTitle
     * @param string $startTime Định dạng chuỗi ngày giờ bắt đầu suất chiếu
     * @param int $duration Thời lượng phim (phút)
     * @param string $cinemaName Tên rạp chiếu
     * @param string $cinemaAddress Địa chỉ rạp chiếu
     * @param string $roomName Tên phòng chiếu
     * @param string $seatsList Danh sách mã ghế (ví dụ: A1, A2)
     * @param string $bookingCode Mã đơn hàng đặt vé
     * @return string URL Google Calendar
     */
    public function generateCalendarUrl(
        string $movieTitle,
        string $startTime,
        int $duration,
        string $cinemaName,
        string $cinemaAddress,
        string $roomName,
        string $seatsList,
        string $bookingCode
    ): string {
        $startCarbon = Carbon::parse($startTime);
        
        // Tính toán thời gian kết thúc dựa trên thời lượng phim
        $endCarbon = (clone $startCarbon)->addMinutes($duration);

        // Chuyển đổi định dạng giờ quốc tế UTC để Google Calendar nhận dạng chính xác (YYYYMMDDTHHmmSSZ)
        $startUtc = $startCarbon->setTimezone('UTC')->format('Ymd\THis\Z');
        $endUtc = $endCarbon->setTimezone('UTC')->format('Ymd\THis\Z');

        $text = 'Xem phim ' . $movieTitle . ' - movieGo';
        
        $details = "🎬 Vé xem phim điện ảnh tại movieGo\n"
                 . "-----------------------------------\n"
                 . "• Phim: {$movieTitle}\n"
                 . "• Mã đơn: {$bookingCode}\n"
                 . "• Phòng chiếu: {$roomName}\n"
                 . "• Danh sách ghế: {$seatsList}\n"
                 . "• Địa điểm: {$cinemaName}\n"
                 . "• Địa chỉ: {$cinemaAddress}\n\n"
                 . "⚠️ Lưu ý: Quý khách vui lòng có mặt trước giờ chiếu 30 phút để nhận vé.";

        $location = "{$cinemaName}, {$cinemaAddress}";

        // Xây dựng các tham số URL
        $params = [
            'action' => 'TEMPLATE',
            'text' => $text,
            'dates' => "{$startUtc}/{$endUtc}",
            'details' => $details,
            'location' => $location,
            'trp' => 'true',
        ];

        return 'https://calendar.google.com/calendar/render?' . http_build_query($params);
    }
}
