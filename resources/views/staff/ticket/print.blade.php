<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>In Vé - {{ $booking->booking_code }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            margin: 0;
            padding: 10px;
            width: 80mm;
            background: #fff;
            color: #000;
        }
        .ticket {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px dashed #000;
            padding-bottom: 20px;
        }
        .cinema-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .movie-title {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
        }
        .info {
            text-align: left;
            font-size: 12px;
            margin-bottom: 5px;
            line-height: 1.5;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
        }
        .seat {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        .qr-code {
            margin: 15px 0;
        }
        .qr-code svg {
            max-width: 120px;
            height: auto;
        }
        .footer {
            font-size: 10px;
            text-align: center;
            margin-top: 10px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print(); if(window.top === window.self) { setTimeout(() => window.close(), 1000); }">
    <div class="no-print" id="action-buttons" style="margin-bottom: 20px; text-align: center; display: none;">
        <button onclick="window.print()" style="padding: 10px 20px; margin-right: 10px; cursor: pointer;">In lại</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer;">Đóng</button>
    </div>
    <script>
        if (window.top === window.self) {
            document.getElementById('action-buttons').style.display = 'block';
        }
    </script>

    @foreach($seats as $seat)
    <div class="ticket">
        <div class="cinema-name">{{ $booking->showtime->room->cinema->name ?? 'CINEMA' }}</div>
        <div>Phòng: {{ $booking->showtime->room->name }} ({{ $booking->showtime->room->format }})</div>
        <hr style="border-top: 1px dashed #000; margin: 10px 0;">
        
        <div class="movie-title">{{ $booking->showtime->movie->title }}</div>
        
        <div class="info">
            <div class="info-row">
                <span>Suất chiếu:</span> 
                <span>{{ $booking->showtime->start_time->format('H:i') }} - {{ $booking->showtime->start_time->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span>Ghế:</span> 
                <span class="seat">{{ $seat->seat->row_name }}{{ $seat->seat->seat_number }}</span>
            </div>
            <div class="info-row">
                <span>Loại vé:</span> 
                <span>{{ $seat->seat->seat_type }}</span>
            </div>
            <div class="info-row">
                <span>Giá vé:</span> 
                <span>{{ number_format($seat->price_at_booking) }}đ</span>
            </div>
            <div class="info-row">
                <span>Khách hàng:</span> 
                <span>{{ $booking->user->name ?? ($booking->notes ?? 'Khách tại quầy') }}</span>
            </div>
        </div>
        
        @if($seat->status !== 'USED')
        <div class="qr-code">
            <!-- generate QR code image for this specific seat's qr_code -->
            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)->margin(1)->generate($seat->qr_code) !!}
        </div>
        @else
        <div style="margin: 15px 0; font-weight: bold; font-style: italic; border: 1px solid #000; padding: 10px; display: inline-block;">
            ĐÃ CHECK-IN
        </div>
        @endif
        
        <div class="footer">
            Cảm ơn quý khách và chúc xem phim vui vẻ!<br>
            Mã Đơn: {{ $booking->booking_code }}
        </div>
    </div>
    @endforeach
</body>
</html>
