<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket Vé Xem Phim - movieGo</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #0f0f12; margin: 0; padding: 0; color: #e2e8f0; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #0f0f12; padding-top: 30px; padding-bottom: 30px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #18181b; border-radius: 12px; overflow: hidden; border: 1px solid #27272a; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5); }
        .header { background: linear-gradient(135deg, #e50914, #9b050c); padding: 30px 20px; text-align: center; border-bottom: 3px solid #ff1e27; }
        .logo-text { font-size: 28px; font-weight: 800; color: #ffffff; letter-spacing: 2px; text-transform: uppercase; margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
        .logo-text span { color: #f4f4f5; }
        .thank-you-banner { font-size: 18px; font-weight: 600; color: #ffffff; margin-top: 10px; opacity: 0.9; }
        .content { padding: 30px 25px; }
        
        .section-title { font-size: 16px; font-weight: 700; text-transform: uppercase; color: #e50914; margin-top: 0; margin-bottom: 15px; letter-spacing: 1px; border-left: 3px solid #e50914; padding-left: 10px; }
        
        /* Grid Details */
        .info-grid { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .info-grid td { padding: 8px 0; vertical-align: top; border-bottom: 1px solid #27272a; }
        .info-label { font-size: 13px; color: #a1a1aa; width: 35%; }
        .info-value { font-size: 14px; color: #f4f4f5; font-weight: 600; }
        
        /* Ticket Card */
        .ticket-card { background-color: #27272a; border-radius: 8px; border: 1px solid #3f3f46; margin-bottom: 25px; overflow: hidden; }
        .ticket-main { padding: 20px; }
        .movie-title { font-size: 20px; font-weight: 800; color: #ffffff; margin: 0 0 10px 0; line-height: 1.3; }
        
        /* Badges */
        .badge { display: inline-block; padding: 3px 8px; font-size: 11px; font-weight: 700; border-radius: 4px; text-transform: uppercase; margin-right: 5px; }
        .badge-age { background-color: #dc2626; color: #ffffff; }
        .badge-format { background-color: #2563eb; color: #ffffff; }
        .badge-vip { background-color: #d97706; color: #ffffff; }
        .badge-couple { background-color: #db2777; color: #ffffff; }
        .badge-normal { background-color: #4b5563; color: #ffffff; }

        /* QR & Barcode Section */
        .code-section { background-color: #ffffff; color: #000000; padding: 25px; text-align: center; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 4px 10px rgba(0,0,0,0.25); }
        .qr-image { width: 180px; height: 180px; margin: 0 auto 15px auto; display: block; }
        .barcode-image { max-width: 80%; height: 50px; margin: 15px auto 5px auto; display: block; }
        .booking-code-text { font-family: Courier, monospace; font-size: 18px; font-weight: 700; letter-spacing: 3px; color: #18181b; margin-top: 10px; }
        .security-notice { font-size: 11px; color: #71717a; margin-top: 10px; }
        
        /* Tables */
        .table-items { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .table-items th { font-size: 12px; color: #a1a1aa; text-transform: uppercase; text-align: left; padding: 10px; background-color: #222226; font-weight: bold; border-bottom: 2px solid #27272a; }
        .table-items td { padding: 12px 10px; font-size: 14px; border-bottom: 1px solid #27272a; vertical-align: middle; }
        
        /* Totals Block */
        .billing-block { background-color: #222226; border-radius: 8px; padding: 20px; margin-bottom: 25px; border: 1px dashed #3f3f46; }
        .billing-row { display: table; width: 100%; margin-bottom: 8px; }
        .billing-cell-label { display: table-cell; font-size: 13px; color: #a1a1aa; }
        .billing-cell-value { display: table-cell; font-size: 14px; font-weight: 600; text-align: right; color: #f4f4f5; }
        .billing-row-total { display: table; width: 100%; margin-top: 12px; padding-top: 12px; border-top: 1px solid #3f3f46; }
        .billing-label-total { display: table-cell; font-size: 16px; font-weight: bold; color: #ffffff; }
        .billing-value-total { display: table-cell; font-size: 20px; font-weight: 800; text-align: right; color: #e50914; }

        /* Button */
        .btn-calendar { display: block; background-color: #10b981; color: #ffffff !important; text-decoration: none; text-align: center; padding: 12px 20px; border-radius: 6px; font-weight: bold; font-size: 15px; margin-bottom: 30px; transition: background-color 0.2s; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2); }
        
        .important-notes { background-color: #2d1810; border: 1px solid #7c2d12; border-radius: 8px; padding: 15px 20px; margin-bottom: 20px; }
        .important-notes h4 { color: #f97316; margin: 0 0 8px 0; font-size: 14px; font-weight: 700; text-transform: uppercase; }
        .important-notes ul { margin: 0; padding-left: 20px; font-size: 13px; color: #ffedd5; line-height: 1.5; }
        
        .footer { background-color: #0c0c0e; padding: 25px 20px; text-align: center; color: #71717a; font-size: 12px; border-top: 1px solid #1f1f23; }
        .footer p { margin: 5px 0; }
        .footer a { color: #e50914; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            
            <!-- Header -->
            <div class="header">
                <div class="logo-text">movie<span>Go</span></div>
                <div class="thank-you-banner">🍿 Đã đặt vé thành công! Cảm ơn bạn.</div>
            </div>

            <!-- Content -->
            <div class="content">
                
                <!-- GREETING -->
                <p style="margin-top: 0; font-size: 15px; color: #f4f4f5; line-height: 1.5;">
                    Chào <strong>{{ $booking['customer_name'] ?? 'Khách hàng' }}</strong>,
                </p>
                <p style="font-size: 14px; color: #a1a1aa; line-height: 1.5; margin-bottom: 25px;">
                    Giao dịch của bạn đã được thanh toán thành công. Dưới đây là thông tin vé điện tử (E-Ticket) chính thức của bạn. Vui lòng xuất trình mã QR tại rạp để quét vào phòng chiếu.
                </p>

                <!-- CUSTOMER & ORDER INFO -->
                <h3 class="section-title">Thông Tin Đơn Hàng</h3>
                <table class="info-grid">
                    <tr>
                        <td class="info-label">Mã đơn hàng:</td>
                        <td class="info-value" style="color: #ffffff;">{{ $booking['booking_code'] ?? 'N/A' }}</td>
                    </tr>
                    @if(!empty($booking['customer_phone']))
                    <tr>
                        <td class="info-label">Số điện thoại:</td>
                        <td class="info-value">{{ $booking['customer_phone'] }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="info-label">Ngày đặt vé:</td>
                        <td class="info-value">
                            {{ isset($booking['booking_time']) ? \Carbon\Carbon::parse($booking['booking_time'])->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label">Thời gian thanh toán:</td>
                        <td class="info-value">
                            {{ !empty($booking['payment_time']) ? \Carbon\Carbon::parse($booking['payment_time'])->format('d/m/Y H:i') : (isset($booking['booking_time']) ? \Carbon\Carbon::parse($booking['booking_time'])->format('d/m/Y H:i') : now()->format('d/m/Y H:i')) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label">Hình thức:</td>
                        <td class="info-value" style="text-transform: uppercase;">{{ $booking['payment_method'] ?? 'Online' }}</td>
                    </tr>
                </table>

                <!-- MOVIE & SHOWTIME TICKET -->
                <h3 class="section-title">Vé Xem Phim</h3>
                <div class="ticket-card">
                    <div class="ticket-main">
                        <div class="movie-title">
                            {{ $showtime->movie->title ?? 'N/A' }}
                        </div>
                        <div style="margin-bottom: 15px;">
                            @if(!empty($showtime->movie) && !empty($showtime->movie->age_rating))
                                <span class="badge badge-age">{{ $showtime->movie->age_rating }}</span>
                            @endif
                            <span class="badge badge-format">2D</span>
                            @if(!empty($showtime->movie) && !empty($showtime->movie->duration))
                                <span class="badge badge-normal" style="background-color: #3f3f46;">{{ $showtime->movie->duration }} phút</span>
                            @endif
                        </div>
                        
                        <table class="info-grid" style="margin-bottom: 0;">
                            <tr>
                                <td class="info-label" style="width: 30%;">Rạp chiếu:</td>
                                <td class="info-value" style="color: #ffffff;">{{ $showtime->room->cinema->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">Địa chỉ:</td>
                                <td class="info-value" style="font-size: 13px; font-weight: normal;">{{ $showtime->room->cinema->address ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">Phòng chiếu:</td>
                                <td class="info-value" style="color: #10b981;">{{ $showtime->room->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">Suất chiếu:</td>
                                <td class="info-value" style="color: #f4f4f5; font-size: 15px;">
                                    <strong>{{ isset($showtime->start_time) ? \Carbon\Carbon::parse($showtime->start_time)->format('H:i') : 'N/A' }}</strong> ngày <strong>{{ isset($showtime->start_time) ? \Carbon\Carbon::parse($showtime->start_time)->format('d/m/Y') : 'N/A' }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td class="info-label">Ghế đã chọn:</td>
                                <td class="info-value">
                                    <span style="font-size: 15px; color: #f59e0b;">{{ $seatsList ?? 'N/A' }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- QR CODE & BARCODE (THE CRITICAL PART) -->
                @php
                    $qrCodeUrl = $qrCode ?? '';
                    if (isset($message) && !empty($qrCode) && str_starts_with($qrCode, 'data:')) {
                        $parts = explode(',', $qrCode, 2);
                        if (count($parts) === 2) {
                            $header = $parts[0];
                            $data = $parts[1];
                            $mimeType = 'image/png';
                            if (preg_match('/data:([^;]+)/', $header, $mimeMatches)) {
                                $mimeType = $mimeMatches[1];
                            }
                            $binaryData = base64_decode($data);
                            $extension = str_contains($mimeType, 'svg') ? 'svg' : 'png';
                            $filename = 'qrcode.' . $extension;
                            $qrCodeUrl = $message->embedData($binaryData, $filename, $mimeType);
                        }
                    }

                    $barcodeUrl = $barcode ?? '';
                    if (!empty($barcode) && isset($message) && str_starts_with($barcode, 'data:')) {
                        $parts = explode(',', $barcode, 2);
                        if (count($parts) === 2) {
                            $header = $parts[0];
                            $data = $parts[1];
                            $mimeType = 'image/png';
                            if (preg_match('/data:([^;]+)/', $header, $mimeMatches)) {
                                $mimeType = $mimeMatches[1];
                            }
                            $binaryData = base64_decode($data);
                            $extension = str_contains($mimeType, 'svg') ? 'svg' : 'png';
                            $filename = 'barcode.' . $extension;
                            $barcodeUrl = $message->embedData($binaryData, $filename, $mimeType);
                        }
                    }
                @endphp
                <div class="code-section">
                    <div style="font-size: 14px; font-weight: 700; color: #18181b; text-transform: uppercase; margin-bottom: 15px;">Mã Vào Phòng Chiếu (E-Ticket QR)</div>
                    
                    <!-- QR Code Image -->
                    <img class="qr-image" src="{{ $qrCodeUrl }}" alt="Ticket QR Code">
                    
                    <!-- Optional Barcode Image -->
                    @if(!empty($barcode))
                        <img class="barcode-image" src="{{ $barcodeUrl }}" alt="Ticket Barcode">
                    @endif

                    <div class="booking-code-text">{{ $booking['booking_code'] ?? 'N/A' }}</div>
                    <div class="security-notice">
                        🔒 Check-in an toàn tại quầy hoặc cửa kiểm vé. QR chứa mã hóa bảo mật chống vé giả.
                    </div>
                </div>

                <!-- GOOGLE CALENDAR BUTTON -->
                <a href="{{ $calendarUrl ?? '#' }}" class="btn-calendar" target="_blank">
                    📅 Thêm suất chiếu vào Google Calendar
                </a>

                <!-- SEATS DETAIL LIST -->
                <h3 class="section-title">Chi Tiết Ghế</h3>
                <table class="table-items">
                    <thead>
                        <tr>
                            <th>Mã Ghế</th>
                            <th>Loại Ghế</th>
                            <th style="text-align: right;">Giá vé</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $ticketTotal = 0; @endphp
                        @foreach(($booking['seats'] ?? []) as $seat)
                            @php $ticketTotal += $seat->price_at_booking ?? 0; @endphp
                            <tr>
                                <td><strong style="color: #ffffff; font-size: 15px;">{{ $seat->row_name ?? '' }}{{ $seat->seat_number ?? '' }}</strong></td>
                                <td>
                                    @if(isset($seat->seat_type) && $seat->seat_type === 'VIP')
                                        <span class="badge badge-vip">VIP</span>
                                    @elseif(isset($seat->seat_type) && $seat->seat_type === 'COUPLE')
                                        <span class="badge badge-couple">Couple</span>
                                    @else
                                        <span class="badge badge-normal">Thường</span>
                                    @endif
                                </td>
                                <td style="text-align: right; color: #ffffff; font-weight: 600;">{{ number_format($seat->price_at_booking ?? 0, 0, ',', '.') }} đ</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- COMBO FOOD & DRINK LIST (IF PURCHASED) -->
                @if(isset($booking['combos']) && !empty($booking['combos']) && method_exists($booking['combos'], 'isNotEmpty') && $booking['combos']->isNotEmpty())
                    <h3 class="section-title">Bắp Nước Đi Kèm</h3>
                    <table class="table-items">
                        <thead>
                            <tr>
                                <th>Combo</th>
                                <th style="text-align: center;">Số lượng</th>
                                <th style="text-align: right;">Đơn giá</th>
                                <th style="text-align: right;">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $comboTotal = 0; @endphp
                            @foreach($booking['combos'] as $combo)
                                @php 
                                    $qty = $combo->pivot->quantity ?? 0;
                                    $price = $combo->pivot->price ?? 0;
                                    $sub = $qty * $price;
                                    $comboTotal += $sub;
                                @endphp
                                <tr>
                                    <td style="color: #ffffff;">{{ $combo->name ?? 'Combo' }}</td>
                                    <td style="text-align: center; color: #ffffff;">{{ $qty }}</td>
                                    <td style="text-align: right; color: #a1a1aa;">{{ number_format($price, 0, ',', '.') }} đ</td>
                                    <td style="text-align: right; color: #ffffff; font-weight: 600;">{{ number_format($sub, 0, ',', '.') }} đ</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    @php $comboTotal = 0; @endphp
                @endif

                <!-- BILLING INFORMATION -->
                <h3 class="section-title">Chi Tiết Thanh Toán</h3>
                <div class="billing-block">
                    <div class="billing-row">
                        <div class="billing-cell-label">Tổng tiền vé:</div>
                        <div class="billing-cell-value">{{ number_format($ticketTotal ?? 0, 0, ',', '.') }} đ</div>
                    </div>
                    @if(isset($comboTotal) && $comboTotal > 0)
                    <div class="billing-row">
                        <div class="billing-cell-label">Tổng tiền Combo:</div>
                        <div class="billing-cell-value">{{ number_format($comboTotal, 0, ',', '.') }} đ</div>
                    </div>
                    @endif
                    @if(isset($booking['discount_amount']) && $booking['discount_amount'] > 0)
                    <div class="billing-row">
                        <div class="billing-cell-label" style="color: #10b981;">Giảm giá (Voucher):</div>
                        <div class="billing-cell-value" style="color: #10b981;">-{{ number_format($booking['discount_amount'] ?? 0, 0, ',', '.') }} đ</div>
                    </div>
                    @endif
                    <div class="billing-row-total">
                        <div class="billing-label-total">Tổng thanh toán:</div>
                        <div class="billing-value-total">{{ number_format($booking['total_price'] ?? 0, 0, ',', '.') }} đ</div>
                    </div>
                </div>

                <!-- IMPORTANT NOTES -->
                <div class="important-notes">
                    <h4>⚠️ Lưu ý quan trọng cho khách xem phim:</h4>
                    <ul>
                        <li>Vui lòng có mặt tại rạp **trước giờ chiếu 30 phút** để nhận vé hoặc vào phòng.</li>
                        <li>Mang theo email hoặc file vé PDF đính kèm để quét mã vào rạp.</li>
                        <li>Vé QR Code chỉ có giá trị **quét một lần duy nhất**. Tuyệt đối không chia sẻ mã này cho người khác.</li>
                    </ul>
                </div>

            </div>

            <!-- Footer -->
            <div class="footer">
                <p>movieGo - Trải nghiệm điện ảnh đỉnh cao</p>
                <p>Hotline hỗ trợ: <strong>1900 6017</strong> | Email: <a href="mailto:support@moviego.com">support@moviego.com</a></p>
                <p>© {{ date('Y') }} movieGo Cinema. All rights reserved.</p>
            </div>

        </div>
    </div>
</body>
</html>
