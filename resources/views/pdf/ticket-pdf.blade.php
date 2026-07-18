<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>E-Ticket Vé Điện Tử - {{ $booking['booking_code'] }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333333;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            font-size: 12px;
            line-height: 1.4;
        }
        .ticket-wrapper {
            max-width: 680px;
            margin: 0 auto;
            padding: 20px;
            border: 2px solid #e50914;
            border-radius: 10px;
            position: relative;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px dashed #cccccc;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .logo-title {
            font-size: 24px;
            font-weight: bold;
            color: #e50914;
            letter-spacing: 1px;
        }
        .logo-sub {
            font-size: 10px;
            color: #666666;
            margin-top: 2px;
        }
        .ticket-title {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            color: #333333;
            text-transform: uppercase;
        }
        
        .main-grid {
            width: 100%;
            margin-bottom: 15px;
        }
        .movie-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .movie-title {
            font-size: 16px;
            font-weight: bold;
            color: #111111;
            margin-bottom: 5px;
        }
        .movie-meta {
            font-size: 11px;
            color: #666666;
            margin-bottom: 10px;
        }
        .meta-tag {
            background-color: #e50914;
            color: #ffffff;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 10px;
            margin-right: 5px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 6px 4px;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            color: #555555;
            width: 25%;
        }
        .info-value {
            color: #111111;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th {
            background-color: #f1f5f9;
            color: #475569;
            text-transform: uppercase;
            font-size: 10px;
            font-weight: bold;
            padding: 8px;
            text-align: left;
            border-bottom: 2px solid #cbd5e1;
        }
        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }
        
        .billing-summary {
            width: 100%;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        .billing-summary td {
            padding: 4px 8px;
        }
        .total-row {
            border-top: 1px solid #cccccc;
            font-size: 14px;
            font-weight: bold;
        }
        
        .codes-block {
            width: 100%;
            border-top: 2px dashed #cccccc;
            padding-top: 15px;
            margin-top: 15px;
            text-align: center;
        }
        .qr-code-img {
            width: 130px;
            height: 130px;
            margin-bottom: 10px;
        }
        .barcode-img {
            max-width: 240px;
            height: 40px;
            margin-top: 10px;
            margin-bottom: 5px;
        }
        .code-display {
            font-family: monospace;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        
        .notice-box {
            background-color: #fffaf0;
            border: 1px solid #feebc8;
            border-radius: 6px;
            padding: 10px 15px;
            margin-top: 15px;
            font-size: 10px;
            color: #c05621;
        }
        .notice-box h4 {
            margin: 0 0 5px 0;
            text-transform: uppercase;
            font-weight: bold;
        }
        .notice-box ul {
            margin: 0;
            padding-left: 15px;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #888888;
            border-top: 1px solid #eeeeee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="ticket-wrapper">
        
        <!-- Header -->
        <table class="header-table" style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <div class="logo-title">movieGo</div>
                    <div class="logo-sub">TRẢI NGHIỆM ĐIỆN ẢNH ĐỈNH CAO</div>
                </td>
                <td style="width: 50%; text-align: right; vertical-align: middle;">
                    <div class="ticket-title">VÉ ĐIỆN TỬ / E-TICKET</div>
                </td>
            </tr>
        </table>

        <!-- Main Info -->
        <table class="main-grid" style="width: 100%;">
            <tr>
                <td style="width: 50%; padding-right: 10px; vertical-align: top;">
                    <h3 style="margin-top: 0; margin-bottom: 10px; color: #e50914; font-size: 13px; text-transform: uppercase;">Thông Tin Đơn Hàng</h3>
                    <table class="info-table">
                        <tr>
                            <td class="info-label">Mã vé:</td>
                            <td class="info-value" style="font-weight: bold;">{{ $booking['booking_code'] }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Khách hàng:</td>
                            <td class="info-value">{{ $booking['customer_name'] }}</td>
                        </tr>
                        @if(!empty($booking['customer_phone']))
                        <tr>
                            <td class="info-label">Điện thoại:</td>
                            <td class="info-value">{{ $booking['customer_phone'] }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="info-label">Ngày đặt:</td>
                            <td class="info-value">{{ \Carbon\Carbon::parse($booking['booking_time'])->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Phương thức:</td>
                            <td class="info-value" style="text-transform: uppercase;">{{ $booking['payment_method'] ?? 'Online' }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%; padding-left: 10px; vertical-align: top;">
                    <div class="movie-card">
                        <div class="movie-title">{{ $showtime->movie->title }}</div>
                        <div class="movie-meta">
                            @if($showtime->movie->age_rating)
                                <span class="meta-tag">{{ $showtime->movie->age_rating }}</span>
                            @endif
                            <span>2D Digital</span>
                            @if($showtime->movie->duration)
                                <span> | {{ $showtime->movie->duration }} phút</span>
                            @endif
                        </div>
                        <table class="info-table">
                            <tr>
                                <td class="info-label" style="width: 30%;">Rạp:</td>
                                <td class="info-value" style="font-weight: bold;">{{ $showtime->room->cinema->name }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">Phòng:</td>
                                <td class="info-value" style="font-weight: bold; color: #e50914;">{{ $showtime->room->name }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">Suất chiếu:</td>
                                <td class="info-value" style="font-weight: bold;">
                                    {{ \Carbon\Carbon::parse($showtime->start_time)->format('H:i - d/m/Y') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="info-label">Danh sách ghế:</td>
                                <td class="info-value" style="font-weight: bold; color: #d97706;">{{ $seatsList }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Seats details -->
        <h3 style="color: #e50914; font-size: 13px; text-transform: uppercase; margin-bottom: 8px;">Chi Tiết Giá Vé</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Ghế</th>
                    <th>Loại Ghế</th>
                    <th style="text-align: right;">Giá vé</th>
                </tr>
            </thead>
            <tbody>
                @php $ticketTotal = 0; @endphp
                @foreach($booking['seats'] as $seat)
                    @php $ticketTotal += $seat->price_at_booking; @endphp
                    <tr>
                        <td style="font-weight: bold;">{{ $seat->row_name }}{{ $seat->seat_number }}</td>
                        <td>{{ $seat->seat_type }}</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($seat->price_at_booking, 0, ',', '.') }} đ</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Combos details (if purchased) -->
        @if(isset($booking['combos']) && $booking['combos']->isNotEmpty())
            <h3 style="color: #e50914; font-size: 13px; text-transform: uppercase; margin-bottom: 8px;">Bắp Nước Đi Kèm</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Tên Combo</th>
                        <th style="text-align: center;">Số lượng</th>
                        <th style="text-align: right;">Đơn giá</th>
                        <th style="text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @php $comboTotal = 0; @endphp
                    @foreach($booking['combos'] as $combo)
                        @php 
                            $qty = $combo->pivot->quantity;
                            $price = $combo->pivot->price;
                            $sub = $qty * $price;
                            $comboTotal += $sub;
                        @endphp
                        <tr>
                            <td>{{ $combo->name }}</td>
                            <td style="text-align: center;">{{ $qty }}</td>
                            <td style="text-align: right;">{{ number_format($price, 0, ',', '.') }} đ</td>
                            <td style="text-align: right; font-weight: bold;">{{ number_format($sub, 0, ',', '.') }} đ</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            @php $comboTotal = 0; @endphp
        @endif

        <!-- Payments and Codes -->
        <table style="width: 100%; border-top: 1px solid #eeeeee; padding-top: 10px;">
            <tr>
                <!-- Total pricing details -->
                <td style="width: 50%; vertical-align: top;">
                    <table class="billing-summary">
                        <tr>
                            <td style="color: #666666;">Tiền vé:</td>
                            <td style="text-align: right;">{{ number_format($ticketTotal, 0, ',', '.') }} đ</td>
                        </tr>
                        @if($comboTotal > 0)
                        <tr>
                            <td style="color: #666666;">Tiền Combo:</td>
                            <td style="text-align: right;">{{ number_format($comboTotal, 0, ',', '.') }} đ</td>
                        </tr>
                        @endif
                        @if($booking['discount_amount'] > 0)
                        <tr>
                            <td style="color: #c05621;">Giảm giá:</td>
                            <td style="text-align: right; color: #c05621;">-{{ number_format($booking['discount_amount'], 0, ',', '.') }} đ</td>
                        </tr>
                        @endif
                        <tr class="total-row">
                            <td style="padding-top: 8px;">Tổng cộng:</td>
                            <td style="text-align: right; padding-top: 8px; color: #e50914;">{{ number_format($booking['total_price'], 0, ',', '.') }} đ</td>
                        </tr>
                    </table>
                </td>
                
                <!-- QR Code & Barcode -->
                <td style="width: 50%; text-align: center; vertical-align: top;">
                    <div style="font-weight: bold; font-size: 11px; margin-bottom: 5px; text-transform: uppercase;">Mã Vào Phòng Chiếu</div>
                    <img class="qr-code-img" src="{{ $qrCode }}" alt="QR Code">
                    @if(!empty($barcode))
                        <br>
                        <img class="barcode-img" src="{{ $barcode }}" alt="Barcode">
                    @endif
                    <div class="code-display">{{ $booking['booking_code'] }}</div>
                </td>
            </tr>
        </table>

        <!-- Notice -->
        <div class="notice-box">
            <h4>⚠️ Lưu ý quan trọng:</h4>
            <ul>
                <li>Vui lòng mang theo vé E-Ticket PDF này hoặc mã QR trong email để quét tại lối vào phòng chiếu.</li>
                <li>Quý khách nên có mặt trước giờ chiếu 30 phút để chuẩn bị.</li>
                <li>Mỗi mã QR chỉ được phép quét sử dụng **một lần duy nhất**. Tuyệt đối không chia sẻ mã này cho bất kỳ ai.</li>
            </ul>
        </div>

        <!-- Footer -->
        <div class="footer">
            movieGo Cinema | Hotline: 1900 6017 | Email: support@moviego.com<br>
            Cảm ơn quý khách và chúc quý khách có một buổi xem phim vui vẻ!
        </div>

    </div>
</body>
</html>
