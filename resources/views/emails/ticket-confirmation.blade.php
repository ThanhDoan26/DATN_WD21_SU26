<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Xác nhận đặt vé</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f5; margin: 0; padding: 20px; color: #333; }
        .container { max-w-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background-color: #e50914; color: #ffffff; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .booking-code { text-align: center; margin-bottom: 20px; }
        .booking-code span { background-color: #f1f5f9; padding: 10px 20px; border-radius: 4px; font-size: 20px; font-weight: bold; letter-spacing: 2px; border: 1px dashed #cbd5e1; display: inline-block; }
        .details { margin-bottom: 20px; border-top: 1px solid #e2e8f0; padding-top: 20px; }
        .details p { margin: 10px 0; font-size: 15px; }
        .details strong { width: 120px; display: inline-block; color: #475569; }
        .seats { background-color: #f8fafc; padding: 15px; border-radius: 6px; margin-top: 20px; }
        .seats table { width: 100%; border-collapse: collapse; }
        .seats th, .seats td { padding: 10px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .seats th { color: #64748b; font-weight: normal; font-size: 14px; }
        .total { font-size: 20px; font-weight: bold; color: #e50914; text-align: right; margin-top: 20px; }
        .footer { background-color: #f8fafc; padding: 20px; text-align: center; color: #64748b; font-size: 13px; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Cảm ơn bạn đã đặt vé tại movieGo!</h1>
        </div>
        <div class="content">
            <p>Chào bạn,</p>
            <p>Đơn đặt vé của bạn đã được thanh toán thành công. Dưới đây là thông tin chi tiết vé của bạn:</p>
            
            <div class="booking-code">
                Mã vé: <br>
                <span>{{ $booking['booking_code'] }}</span>
            </div>

            <div class="details">
                <p><strong>Phim:</strong> {{ $showtime->movie->title }}</p>
                <p><strong>Rạp:</strong> {{ $showtime->room->cinema->name }}</p>
                <p><strong>Phòng chiếu:</strong> {{ $showtime->room->name }}</p>
                <p><strong>Suất chiếu:</strong> {{ \Carbon\Carbon::parse($showtime->start_time)->format('H:i - d/m/Y') }}</p>
            </div>

            <div class="seats">
                <h3>Ghế đã đặt:</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Mã ghế</th>
                            <th>Loại ghế</th>
                            <th style="text-align: right">Giá</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($booking['seats'] as $seat)
                            <tr>
                                <td><strong>{{ $seat->row_name }}{{ $seat->seat_number }}</strong></td>
                                <td>{{ $seat->seat_type }}</td>
                                <td style="text-align: right">{{ number_format($seat->price_at_booking, 0, ',', '.') }} đ</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="total">
                    Tổng tiền: {{ number_format($booking['total_price'], 0, ',', '.') }} đ
                </div>
            </div>

            <p style="margin-top: 30px; text-align: center;">Vui lòng xuất trình mã vé này tại quầy để nhận vé hoặc vào phòng chiếu.</p>
        </div>
        <div class="footer">
            <p>movieGo - Trải nghiệm điện ảnh đỉnh cao</p>
            <p>Hotline: 1900 xxxx</p>
        </div>
    </div>
</body>
</html>
