<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Vé - {{ $booking->booking_code }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap');
        
        body {
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #000;
            font-family: 'Roboto', sans-serif;
            font-size: 13px;
            line-height: 1.5;
        }

        .ticket-wrapper {
            width: 76mm; /* Chuẩn K80 */
            margin: 0 auto;
            padding: 10px 10px;
            box-sizing: border-box;
            position: relative;
            page-break-after: always;
        }

        .ticket-wrapper:last-child {
            page-break-after: auto;
        }

        h1, h2, h3, h4, h5, h6, p {
            margin: 0;
            padding: 0;
        }

        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }

        .logo {
            font-size: 28px;
            font-weight: 900;
            margin-bottom: 2px;
            letter-spacing: 1px;
        }
        
        .ticket-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            letter-spacing: 1px;
        }

        .movie-box {
            background-color: #000;
            color: #fff;
            padding: 10px 5px;
            text-align: center;
            border-radius: 4px;
            margin-bottom: 15px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .movie-title {
            font-size: 18px;
            font-weight: 900;
            margin-bottom: 3px;
        }
        .movie-format {
            font-size: 12px;
            font-weight: 500;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }
        .info-col .label {
            font-size: 10px;
            color: #555;
            text-transform: uppercase;
        }
        .info-col .value {
            font-size: 14px;
            font-weight: bold;
        }

        .seats-box {
            border: 2px dashed #000;
            padding: 10px;
            text-align: center;
            margin: 15px 0;
        }
        .seats-box .label {
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .seats-box .value {
            font-size: 28px;
            font-weight: 900;
            word-break: break-word;
            line-height: 1.2;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 15px 0;
        }

        .flex-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }
        .flex-row .label {
            font-size: 12px;
        }
        .flex-row .value {
            font-weight: bold;
            font-size: 13px;
        }
        .total-row {
            font-size: 16px !important;
            margin-top: 10px;
        }

        .qr-section {
            margin-top: 20px;
            text-align: center;
        }
        .qr-code {
            display: inline-block;
            padding: 5px;
            border: 2px solid #000;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .barcode {
            margin-top: 10px;
            width: 100%;
            text-align: center;
        }
        .barcode svg {
            width: 100%;
            height: 40px;
            object-fit: contain;
        }

        .footer {
            margin-top: 20px;
            font-size: 11px;
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 10px;
            font-style: italic;
        }

        @media print {
            body { background: none; }
            @page { margin: 0; }
            .no-print { display: none !important; }
        }
        
        .no-print {
            padding: 15px;
            background: #f8f9fa;
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }
        .btn-print {
            padding: 12px 25px;
            background: #000;
            color: #fff;
            border: none;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 8px;
            transition: 0.3s;
        }
        .btn-print:hover { background: #333; }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ XÁC NHẬN IN VÉ</button>
    </div>

    @foreach($seatsToPrint as $seat)
    <div class="ticket-wrapper">
        <div class="text-center">
            <div class="logo">MovieGo</div>
            <div class="ticket-title">VÉ XEM PHIM TICKET</div>
        </div>

        <div class="movie-box">
            <div class="movie-title text-uppercase">{{ $booking->showtime->movie->title ?? 'N/A' }}</div>
            <div class="movie-format">Định dạng: {{ $booking->showtime->room->format ?? '2D' }}</div>
        </div>

        <div class="grid-2">
            <div class="info-col text-left">
                <div class="label">Rạp chiếu</div>
                <div class="value">{{ $booking->showtime->room->cinema->name ?? 'N/A' }}</div>
            </div>
            <div class="info-col text-right">
                <div class="label">Phòng chiếu</div>
                <div class="value">{{ $booking->showtime->room->name ?? 'N/A' }}</div>
            </div>
            <div class="info-col text-left">
                <div class="label">Ngày chiếu</div>
                <div class="value">{{ $booking->showtime->start_time->format('d/m/Y') }}</div>
            </div>
            <div class="info-col text-right">
                <div class="label">Giờ chiếu</div>
                <div class="value">{{ $booking->showtime->start_time->format('H:i') }}</div>
            </div>
        </div>

        <div class="seats-box">
            <div class="label">Ghế (Seat) - {{ $seat->seat->seat_type ?? 'Regular' }}</div>
            <div class="value">{{ $seat->seat->row_name ?? '' }}{{ $seat->seat->seat_number ?? '' }}</div>
            <div style="font-size: 12px; margin-top: 5px;">Giá vé: {{ number_format($seat->price_at_booking) }}đ</div>
        </div>

        @if($booking->combos && $booking->combos->count() > 0 && $loop->first)
            <div class="divider"></div>
            <div class="text-bold text-center text-uppercase" style="margin-bottom: 8px;">Bắp nước (Concessions)</div>
            @foreach($booking->combos as $combo)
                <div class="flex-row">
                    <span style="font-size: 12px;">{{ $combo->name }} x{{ $combo->pivot->quantity }}</span>
                    <span class="text-bold">{{ number_format($combo->pivot->price * $combo->pivot->quantity) }}đ</span>
                </div>
            @endforeach
            <div class="text-center" style="font-size: 10px; margin-top: 5px; font-style: italic;">
                (Chi tiết bắp nước chỉ được in ở vé đầu tiên)
            </div>
        @endif

        <div class="divider"></div>
        
        <div class="flex-row">
            <span class="label">Khuyến mãi:</span>
            <span class="value">{{ $booking->discount_amount ? '-' . number_format($booking->discount_amount) . 'đ' : '0đ' }}</span>
        </div>
        <div class="flex-row total-row">
            <span class="label text-bold">TỔNG ĐƠN HÀNG:</span>
            <span class="value">{{ number_format($booking->total_price) }}đ</span>
        </div>
        <div class="flex-row" style="margin-top: 5px;">
            <span class="label text-uppercase" style="font-size: 10px;">Thanh toán:</span>
            <span class="value" style="font-size: 11px;">{{ $booking->payment_time ? $booking->payment_time->format('H:i d/m/Y') : 'Chưa TT' }}</span>
        </div>

        <div class="divider"></div>

        <div class="qr-section">
            <div class="qr-code">
                <!-- Mã QR của ghế lẻ dùng để checkin -->
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(130)->margin(0)->generate($seat->qr_code) !!}
            </div>
            <div style="font-size: 11px; margin-bottom: 10px;">Quét QR để kiểm tra vé</div>
            
            @php
                $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
            @endphp
            <div class="barcode">
                {!! $generator->getBarcode($booking->booking_code, $generator::TYPE_CODE_128, 2, 40) !!}
            </div>
            <div style="font-size: 14px; font-weight: 900; margin-top: 5px;">{{ $booking->booking_code }}</div>
        </div>

        <div class="footer">
            <div style="font-weight: bold; margin-bottom: 2px;">Cảm ơn quý khách đã chọn MovieGo!</div>
            Vui lòng giữ vé để kiểm tra khi vào rạp
        </div>
    </div>
    @endforeach

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
