<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Vé Xem Phim - {{ $booking->booking_code ?? 'Beta Cinemas' }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;0,900;1,400;1,700&family=Roboto+Mono:wght@500;700&display=swap');
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            background-color: #e9ecef;
            color: #000000;
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            padding: 15px 0;
        }

        /* Top Action Bar (hidden when printing) */
        .no-print-bar {
            width: 78mm;
            max-width: 100%;
            margin: 0 auto 14px auto;
            background: #ffffff;
            border-radius: 8px;
            padding: 10px 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .no-print-bar-actions {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .btn-action {
            padding: 7px 12px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-print-primary {
            background-color: #0b4ea2;
            color: #ffffff;
            flex: 1;
        }
        .btn-print-primary:hover {
            background-color: #093c7d;
        }

        .btn-toggle-mode {
            background-color: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .btn-toggle-mode:hover {
            background-color: #e2e8f0;
        }

        .mode-hint {
            font-size: 10.5px;
            color: #64748b;
            text-align: center;
        }

        /* Main Ticket Container (Khổ chuẩn giấy in nhiệt K80: 78mm) */
        .ticket-page-wrapper {
            width: 78mm;
            margin: 0 auto 16px auto;
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
            position: relative;
            page-break-after: always;
            break-after: page;
        }

        .ticket-page-wrapper:last-child {
            page-break-after: auto;
            break-after: auto;
            margin-bottom: 0;
        }

        .ticket-structure {
            display: flex;
            flex-direction: row;
            width: 100%;
            min-height: 100%;
            background-color: #ffffff;
        }

        /* 2 dải viền dọc màu xanh đậm chứa logo Beta Cinemas lặp lại dọc 2 bên */
        .ticket-side-strip {
            width: 22px;
            min-width: 22px;
            max-width: 22px;
            background-color: #0b4ea2;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='22' height='20' viewBox='0 0 44 40'%3E%3Crect x='3' y='3' width='38' height='34' rx='6' fill='%23ffffff'/%3E%3Ctext x='6' y='20' font-family='Arial, sans-serif' font-size='14.5' font-weight='900' fill='%230b4ea2' letter-spacing='-0.5'%3Ebeta%3C/text%3E%3Ccircle cx='36' cy='14.5' r='3.2' fill='%23f37021'/%3E%3Ctext x='22' y='31.5' font-family='Arial, sans-serif' font-size='9' font-weight='700' fill='%230b4ea2' text-anchor='middle' letter-spacing='0.2'%3Ecinemas%3C/text%3E%3C/svg%3E");
            background-repeat: repeat-y;
            background-position: center top;
            background-size: 22px 20px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Nội dung vé ở giữa */
        .ticket-center-content {
            flex: 1;
            padding: 6px 7px 10px 7px;
            background-color: #ffffff;
            color: #000000;
        }

        /* Đường nét đứt chuẩn máy in nhiệt */
        .dashed-line {
            border-top: 1px dashed #333333;
            margin: 4px 0 5px 0;
            width: 100%;
        }

        /* Header Tên rạp / Địa chỉ */
        .ticket-header {
            text-align: center;
            margin-bottom: 4px;
        }
        .cinema-name {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            line-height: 1.2;
            margin-bottom: 2px;
            color: #000;
        }
        .cinema-address {
            font-size: 9px;
            color: #222222;
            line-height: 1.2;
            padding: 0 2px;
        }

        /* Tiêu đề "VÉ XEM PHIM" & Meta code */
        .ticket-title-section {
            margin-bottom: 3px;
        }
        .ticket-title-main {
            font-size: 14.5px;
            font-weight: 800;
            text-align: center;
            letter-spacing: 0.5px;
            margin: 3px 0 4px 0;
            color: #000;
        }
        .meta-code-grid {
            display: flex;
            flex-direction: column;
            gap: 1px;
            font-size: 9px;
            color: #111;
        }
        .meta-code-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .meta-left {
            text-align: left;
        }
        .meta-right {
            text-align: right;
        }

        /* Khu vực thông tin phim chính */
        .movie-info-section {
            padding: 1px 0;
        }
        .showtime-datetime {
            font-size: 12px;
            font-weight: 700;
            color: #000;
            line-height: 1.2;
        }
        .movie-title-text {
            font-size: 13.5px;
            font-weight: 800;
            color: #000000;
            line-height: 1.25;
            margin: 2px 0;
            word-break: break-word;
        }
        .movie-type-price-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }
        .movie-type-text {
            font-size: 10.5px;
            font-weight: 600;
            color: #111;
        }
        .movie-price-text {
            font-size: 11.5px;
            font-weight: 700;
            color: #000;
            text-align: right;
        }
        .vat-note-text {
            font-size: 8.5px;
            font-style: italic;
            color: #333333;
            text-align: right;
            margin-top: -1px;
        }

        /* Khu vực số ghế và số phòng (Căn chỉnh cân đối, chữ vừa vặn, không bị tràn dòng) */
        .seat-room-section {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            padding: 2px 0;
            gap: 6px;
        }
        .seat-block, .room-block {
            display: flex;
            align-items: baseline;
            gap: 4px;
            white-space: nowrap;
        }
        .room-block {
            justify-content: flex-end;
            text-align: right;
        }
        .seat-room-label {
            font-size: 10.5px;
            font-weight: 500;
            color: #333;
            white-space: nowrap;
        }
        .seat-room-value {
            font-size: 14.5px;
            font-weight: 800;
            color: #000000;
            letter-spacing: -0.2px;
            line-height: 1.2;
            white-space: nowrap;
        }

        /* Khu vực xếp hạng tuổi & thông tin giao dịch */
        .age-transaction-section {
            padding: 1px 0;
        }
        .age-rating-text {
            font-size: 9.5px;
            font-weight: 700;
            text-align: center;
            color: #111;
            margin: 3px 0 4px 0;
            line-height: 1.2;
        }
        .transaction-row {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #222;
            margin-bottom: 1px;
        }

        /* Khu vực bắp nước combo (nếu có) */
        .combos-container {
            padding: 2px 0;
        }
        .combos-header {
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 2px;
        }
        .combo-row-item {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            margin-bottom: 1.5px;
        }

        /* Khu vực ghi chú và các liên kết chân trang */
        .footer-note-section {
            text-align: center;
            padding-top: 1px;
        }
        .thank-you-title {
            font-size: 10px;
            font-weight: 700;
            color: #000;
            margin-bottom: 1.5px;
        }
        .event-hotline-note {
            font-size: 8px;
            font-style: italic;
            color: #333;
            line-height: 1.2;
            padding: 0 2px;
        }
        .brand-slogan {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            color: #000;
            margin-top: 2.5px;
            margin-bottom: 1px;
        }
        .brand-contact-links {
            font-size: 8px;
            color: #222;
        }

        /* Khu vực mã vạch & số mã vạch ở dưới cùng */
        .barcode-section {
            text-align: center;
            margin-top: 4px;
            padding-bottom: 2px;
        }
        .barcode-svg-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }
        .barcode-svg-wrapper svg {
            width: 90%;
            height: 36px;
            object-fit: contain;
        }
        .barcode-number-text {
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.5px;
            font-family: 'Roboto Mono', 'Courier New', monospace;
            color: #000;
            margin-top: 2px;
        }

        /* Template phôi trống (khi ở chế độ xem Layout Trống) */
        .blank-placeholder {
            display: inline-block;
            border-bottom: 1px dotted #888;
            min-height: 12px;
            color: #999;
            font-style: italic;
            font-size: 9px;
            padding: 0 4px;
        }

        /* Cấu hình khi in thực tế bằng lệnh window.print() */
        @media print {
            body {
                background: transparent !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            @page {
                margin: 0;
                size: 78mm auto;
            }
            .no-print {
                display: none !important;
            }
            .ticket-page-wrapper {
                box-shadow: none !important;
                margin: 0 auto !important;
                width: 78mm !important;
                max-width: 78mm !important;
            }
        }
    </style>
</head>
<body>
    @php
        // Đảm bảo $seatsToPrint luôn là collection có dữ liệu để lặp
        $seats = isset($seatsToPrint) && $seatsToPrint->isNotEmpty() 
            ? $seatsToPrint 
            : collect([null]);
    @endphp

    <!-- Thanh công cụ điều khiển (Ẩn khi in) -->
    <div class="no-print no-print-bar">
        <div class="no-print-bar-actions">
            <button class="btn-action btn-print-primary" onclick="window.print()">
                🖨️ XÁC NHẬN IN VÉ
            </button>
            <button class="btn-action btn-toggle-mode" id="btnToggleTemplate" onclick="toggleBlankTemplate()">
                📄 Xem Phôi Mẫu Trống
            </button>
        </div>
        <div class="mode-hint">
            💡 <em>Mẫu thiết kế vé nhiệt dọc chuẩn K80 (78mm) với 2 viền Beta Cinemas & cỡ chữ vừa vặn, sắc nét.</em>
        </div>
    </div>

    @foreach($seats as $seat)
    @php
        // Trích xuất dữ liệu rạp, suất chiếu, ghế, phim, booking
        $cinema = $seat?->booking?->showtime?->room?->cinema 
               ?? $booking?->showtime?->room?->cinema 
               ?? null;
        $cinemaName = $cinema?->name ?? 'BETA GIẢI PHÓNG';
        $cinemaAddress = $cinema?->address ?? 'Tầng 3, Imperial Plaza, 360 Giải Phóng, Phường Phương Liệt, Thành phố Hà Nội';
        $cinemaTax = $cinema?->tax_code ?? '0106633462';

        $showtime = $seat?->booking?->showtime ?? $booking?->showtime ?? null;
        $movie = $showtime?->movie ?? null;
        $movieTitle = $movie?->title ?? 'Người Nhện: Khởi Đầu Mới';
        $showtimeFormatted = $showtime?->start_time 
            ? $showtime->start_time->format('d/m/Y H:i') 
            : '27/08/2026 21:45';

        $room = $showtime?->room ?? null;
        $roomName = $room?->name ?? 'P6';
        $roomFormat = $room?->format ?? '2D';

        $seatModel = $seat?->seat ?? null;
        $seatRow = $seatModel?->row_name ?? 'G';
        $seatNum = $seatModel?->seat_number ?? '5';
        $seatCode = $seatRow . $seatNum;
        $seatType = $seatModel?->seat_type ?? 'Adult';
        $seatTypeFormatted = ($roomFormat ?: '2D') . ' ' . ($seatType == 'VIP' ? 'Adult V.I.P' : ($seatType == 'COUPLE' ? 'Sweetbox Couple' : 'Adult Standard'));
        $seatPrice = $seat?->price_at_booking ?? 60000;

        // Xếp hạng độ tuổi
        $ageRating = $movie?->age_rating ?? 'T13';
        $ageRatingText = match($ageRating) {
            'P', 'K', 'G' => 'Phim dành cho mọi lứa tuổi (P)',
            'T13', '13+', 'PG', 'PG-13' => 'Phim dành cho khán giả từ 13 tuổi',
            'T16', '16+' => 'Phim dành cho khán giả từ 16 tuổi',
            'T18', '18+', 'R', 'NC-17' => 'Phim dành cho khán giả từ 18 tuổi',
            default => 'Phim dành cho khán giả từ ' . $ageRating . ' tuổi'
        };

        // Người bán & giao dịch
        $salerName = Auth::user()?->name ?? 'Phan Thị Minh Anh';
        $printedTime = ($seat?->printed_at ?? $booking?->created_at ?? now())->format('d/m/Y H:i');
        $userPoints = $booking?->user?->points ?? 0;
        $bookingCode = $booking?->booking_code ?? '6717627174821208';

        // Mã số vé
        $seatTicketCode = $seat 
            ? ($seatRow . $seatNum . ($seatType == 'VIP' ? 'VIP' : 'STD') . $seat->id) 
            : '2DADUVIP104';

        // Tạo mã vạch SVG
        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
        $codeForBarcode = preg_replace('/[^0-9A-Za-z]/', '', $bookingCode);
        if (empty($codeForBarcode)) {
            $codeForBarcode = '6717627174821208';
        }
        try {
            $barcodeSvg = $generator->getBarcode($codeForBarcode, $generator::TYPE_CODE_128, 1.8, 36);
        } catch (\Exception $e) {
            $barcodeSvg = '<div style="height:36px; border:1px solid #000; display:flex; align-items:center; justify-content:center;">||||||||||||||||||||||||||||||</div>';
        }
    @endphp

    <div class="ticket-page-wrapper">
        <div class="ticket-structure">
            <!-- 1. Dải viền dọc màu xanh đậm bên trái chứa logo Beta Cinemas -->
            <div class="ticket-side-strip ticket-side-left"></div>

            <!-- 2. Thân nội dung vé ở giữa -->
            <div class="ticket-center-content">
                
                <!-- Khu vực tiêu đề trên cùng (tên rạp/địa chỉ) -->
                <div class="ticket-header">
                    <div class="cinema-name live-data">RẠP CHIẾU PHIM {{ strtoupper($cinemaName) }}</div>
                    <div class="cinema-address live-data">{{ $cinemaAddress }}</div>
                    <div class="cinema-name blank-data" style="display: none;">[ RẠP CHIẾU PHIM: ........................................ ]</div>
                    <div class="cinema-address blank-data" style="display: none;">[ ĐỊA CHỈ RẠP: ........................................................................ ]</div>
                </div>

                <!-- Tiêu đề "VÉ XEM PHIM" và các ô mã số -->
                <div class="ticket-title-section">
                    <div class="ticket-title-main">VÉ XEM PHIM</div>
                    <div class="meta-code-grid live-data">
                        <div class="meta-code-row">
                            <span class="meta-left">Ký hiệu: {{ $booking?->invoice_series ?? 'N/a' }}</span>
                            <span class="meta-right">Mẫu số: {{ $booking?->invoice_template ?? 'N/a' }}</span>
                        </div>
                        <div class="meta-code-row">
                            <span class="meta-left">Số: <strong>{{ $seatTicketCode }}</strong></span>
                            <span class="meta-right">MST: {{ $cinemaTax }}</span>
                        </div>
                    </div>
                    <div class="meta-code-grid blank-data" style="display: none;">
                        <div class="meta-code-row">
                            <span class="meta-left">Ký hiệu: .................</span>
                            <span class="meta-right">Mẫu số: .................</span>
                        </div>
                        <div class="meta-code-row">
                            <span class="meta-left">Số: ..........................</span>
                            <span class="meta-right">MST: ......................</span>
                        </div>
                    </div>
                </div>

                <div class="dashed-line"></div>

                <!-- Khu vực thông tin phim chính (ngày, giờ, tên phim) & Khu vực loại vé và giá -->
                <div class="movie-info-section">
                    <div class="live-data">
                        <div class="showtime-datetime">{{ $showtimeFormatted }}</div>
                        <div class="movie-title-text">{{ $movieTitle }}</div>
                        <div class="movie-type-price-row">
                            <span class="movie-type-text">{{ $seatTypeFormatted }}</span>
                            <span class="movie-price-text">VND {{ number_format($seatPrice) }}</span>
                        </div>
                        <div class="vat-note-text">(Đã gồm 8% VAT)</div>
                    </div>
                    <div class="blank-data" style="display: none;">
                        <div class="showtime-datetime">[ NGÀY / GIỜ CHIẾU: DD/MM/YYYY HH:MM ]</div>
                        <div class="movie-title-text" style="color: #666;">[ TÊN BỘ PHIM ]</div>
                        <div class="movie-type-price-row">
                            <span class="movie-type-text">[ LOẠI VÉ ]</span>
                            <span class="movie-price-text">VND [ GIÁ VÉ ]</span>
                        </div>
                        <div class="vat-note-text">(Đã gồm 8% VAT)</div>
                    </div>
                </div>

                <div class="dashed-line"></div>

                <!-- Khu vực số ghế và số phòng (Chữ vừa vặn, thẳng hàng, không bị tràn dòng) -->
                <div class="seat-room-section">
                    <div class="seat-block">
                        <span class="seat-room-label">Ghế/Seat</span>
                        <span class="seat-room-value live-data">{{ $seatCode }}</span>
                        <span class="seat-room-value blank-data" style="display: none; color: #888;">[ ..... ]</span>
                    </div>
                    <div class="room-block">
                        <span class="seat-room-label">Phòng/Cinema</span>
                        <span class="seat-room-value live-data">{{ $roomName }}</span>
                        <span class="seat-room-value blank-data" style="display: none; color: #888;">[ ..... ]</span>
                    </div>
                </div>

                <!-- Khu vực xếp hạng tuổi và thông tin giao dịch -->
                <div class="age-transaction-section">
                    <div class="live-data">
                        <div class="age-rating-text">** {{ $ageRatingText }} **</div>
                        <div class="transaction-row">
                            <span>Saler: {{ $salerName }}</span>
                            <span>Time: {{ $printedTime }}</span>
                        </div>
                        <div class="transaction-row">
                            <span>Điểm tích lũy: {{ $userPoints }}</span>
                            <span>Mã ĐH: {{ $bookingCode }}</span>
                        </div>
                    </div>
                    <div class="blank-data" style="display: none;">
                        <div class="age-rating-text">** [ QUY ĐỊNH ĐỘ TUỔI KHÁN GIẢ ] **</div>
                        <div class="transaction-row">
                            <span>Saler: .......................................</span>
                            <span>Time: .......................................</span>
                        </div>
                        <div class="transaction-row">
                            <span>Điểm tích lũy: .......................</span>
                            <span>Mã ĐH: .................................</span>
                        </div>
                    </div>
                </div>

                <!-- Concessions / Bắp nước nếu có đơn kèm -->
                @if($booking && $booking->combos && $booking->combos->count() > 0 && $loop->first)
                <div class="dashed-line live-data"></div>
                <div class="combos-container live-data">
                    <div class="combos-header">BẮP NƯỚC (CONCESSIONS)</div>
                    @foreach($booking->combos as $combo)
                    <div class="combo-row-item">
                        <span>{{ $combo->name }} x{{ $combo->pivot->quantity }}</span>
                        <span style="font-weight: 700;">{{ number_format($combo->pivot->price * $combo->pivot->quantity) }}đ</span>
                    </div>
                    @endforeach
                </div>
                @endif

                <div class="dashed-line"></div>

                <!-- Khu vực ghi chú và các liên kết liên hệ chân trang -->
                <div class="footer-note-section">
                    <div class="thank-you-title">Xin chân thành Cảm ơn quý khách!</div>
                    <div class="event-hotline-note">
                        Khách hàng có nhu cầu mua vé số lượng lớn hoặc thuê phòng tổ chức sự kiện vui lòng liên hệ Hotline để được ưu đãi tốt nhất
                    </div>
                    
                    <div class="dashed-line" style="margin: 4px 0;"></div>

                    <div class="brand-slogan live-data">{{ strtoupper($cinemaName) }} - RẠP NGON GIÁ NGỌT</div>
                    <div class="brand-slogan blank-data" style="display: none;">BETA CINEMAS - RẠP NGON GIÁ NGỌT</div>
                    
                    <div class="brand-contact-links">www.betacinemas.vn - facebook.com/betacinemas/</div>
                </div>

                <!-- Khu vực mã vạch và số mã vạch ở dưới cùng -->
                <div class="barcode-section">
                    <div class="barcode-svg-wrapper live-data">
                        {!! $barcodeSvg !!}
                    </div>
                    <div class="barcode-number-text live-data">{{ $codeForBarcode }}</div>

                    <div class="blank-data" style="display: none;">
                        <div class="barcode-svg-wrapper">
                            <div style="height: 36px; width: 90%; border: 1px dashed #999; display: flex; align-items: center; justify-content: center; color: #888; font-size: 9.5px; font-style: italic;">
                                [ KHU VỰC MÃ VẠCH / BARCODE ]
                            </div>
                        </div>
                        <div class="barcode-number-text" style="color: #666; font-size: 11px;">[ SỐ MÃ VẠCH ]</div>
                    </div>
                </div>

            </div>

            <!-- 3. Dải viền dọc màu xanh đậm bên phải chứa logo Beta Cinemas -->
            <div class="ticket-side-strip ticket-side-right"></div>
        </div>
    </div>
    @endforeach

    <script>
        // Tự động in khi mở trang (nếu không phải chế độ xem mẫu)
        let isAutoPrint = true;
        @if(request()->query('no_auto_print'))
            isAutoPrint = false;
        @endif

        if (isAutoPrint && !window.location.search.includes('blank=1')) {
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 500);
            };
        }

        // Toggle giữa dữ liệu thật và phôi mẫu trống
        let isBlankMode = false;
        function toggleBlankTemplate() {
            isBlankMode = !isBlankMode;
            const liveEls = document.querySelectorAll('.live-data');
            const blankEls = document.querySelectorAll('.blank-data');
            const btn = document.getElementById('btnToggleTemplate');

            liveEls.forEach(el => el.style.display = isBlankMode ? 'none' : '');
            blankEls.forEach(el => el.style.display = isBlankMode ? '' : 'none');

            if (isBlankMode) {
                btn.innerHTML = '🎬 Xem Dữ Liệu Thực Tế';
                btn.classList.remove('btn-toggle-mode');
                btn.classList.add('btn-print-primary');
            } else {
                btn.innerHTML = '📄 Xem Phôi Mẫu Trống';
                btn.classList.remove('btn-print-primary');
                btn.classList.add('btn-toggle-mode');
            }
        }

        // Khởi tạo chế độ nếu URL có ?blank=1
        if (window.location.search.includes('blank=1')) {
            toggleBlankTemplate();
        }
    </script>
</body>
</html>
