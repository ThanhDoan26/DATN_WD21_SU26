<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Vé Xem Phim & Phiếu Combo - {{ $booking->booking_code ?? 'MovieGo' }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;0,900;1,400;1,700&family=Roboto+Mono:wght@500;700&display=swap');
        
        * {
            box-sizing: border-box !important;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        :root {
            --ticket-width: 80mm; /* Khổ in nhiệt chuẩn K80 */
        }

        html, body {
            background-color: #e9ecef;
            color: #000000;
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            font-size: 11.5px;
            line-height: 1.35;
            padding: 15px 0;
            width: 100%;
        }

        /* Top Action Bar (hidden when printing) */
        .no-print-bar {
            width: var(--ticket-width);
            max-width: 100%;
            margin: 0 auto 16px auto;
            background: #ffffff;
            border-radius: 10px;
            padding: 12px 14px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .no-print-bar-actions {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 8px 14px;
            font-size: 13px;
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
            min-width: 180px;
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

        .size-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 11px;
            color: #475569;
            background: #f8fafc;
            padding: 6px;
            border-radius: 6px;
        }
        .btn-size {
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-size.active {
            background: #0b4ea2;
            color: #ffffff;
            border-color: #0b4ea2;
        }

        .mode-hint {
            font-size: 11px;
            color: #64748b;
            text-align: center;
        }

        /* Main Ticket Container */
        .ticket-page-wrapper {
            width: var(--ticket-width) !important;
            max-width: var(--ticket-width) !important;
            min-width: var(--ticket-width) !important;
            margin: 0 auto 20px auto !important;
            background: #ffffff !important;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: hidden !important;
            page-break-after: always;
            break-after: page;
        }

        .ticket-page-wrapper:last-child {
            page-break-after: auto;
            break-after: auto;
            margin-bottom: 0 !important;
        }

        .ticket-structure {
            width: 100% !important;
            max-width: 100% !important;
            background-color: #ffffff;
            overflow: hidden !important;
        }

        /* Thân nội dung vé */
        .ticket-center-content {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            padding: 12px 14px 16px 14px;
            background-color: #ffffff;
            color: #000000;
            overflow: hidden !important;
        }

        /* Đường nét đứt chuẩn máy in nhiệt */
        .dashed-line {
            border-top: 1.5px dashed #333333;
            margin: 6px 0 7px 0;
            width: 100%;
        }

        /* Header Tên rạp / Địa chỉ */
        .ticket-header {
            text-align: center;
            margin-bottom: 6px;
            width: 100%;
            overflow: hidden;
        }
        .cinema-name {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            line-height: 1.25;
            margin-bottom: 3px;
            color: #000;
            word-break: break-word;
        }
        .cinema-address {
            font-size: 10.5px;
            color: #222222;
            line-height: 1.25;
            padding: 0 4px;
            word-break: break-word;
        }

        /* Tiêu đề vé */
        .ticket-title-section {
            margin-bottom: 4px;
            text-align: center;
        }
        .ticket-title-main {
            font-size: 17.5px;
            font-weight: 900;
            text-align: center;
            letter-spacing: 0.8px;
            margin: 4px 0 5px 0;
            color: #000;
        }
        .ticket-title-combo {
            font-size: 16px;
            font-weight: 900;
            text-align: center;
            letter-spacing: 0.5px;
            margin: 4px 0 5px 0;
            color: #0b4ea2;
        }

        .meta-code-grid {
            display: flex;
            flex-direction: column;
            gap: 2px;
            font-size: 10.5px;
            color: #111;
        }
        .meta-code-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .meta-left {
            text-align: left;
            white-space: nowrap;
        }
        .meta-right {
            text-align: right;
            white-space: nowrap;
        }

        /* Khu vực thông tin phim chính */
        .movie-info-section {
            padding: 2px 0;
            width: 100%;
        }
        .showtime-datetime {
            font-size: 14px;
            font-weight: 800;
            color: #000;
            line-height: 1.2;
        }
        .movie-title-text {
            font-size: 16px;
            font-weight: 900;
            color: #000000;
            line-height: 1.25;
            margin: 3px 0 4px 0;
            word-break: break-word;
        }
        .movie-type-price-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }
        .movie-type-text {
            font-size: 12.5px;
            font-weight: 700;
            color: #111;
            white-space: nowrap;
        }
        .movie-price-text {
            font-size: 13.5px;
            font-weight: 800;
            color: #000;
            text-align: right;
            white-space: nowrap;
        }
        .vat-note-text {
            font-size: 10px;
            font-style: italic;
            color: #333333;
            text-align: right;
            margin-top: -1px;
        }

        /* Khu vực số ghế và số phòng (Vé 1) */
        .seat-room-section {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            padding: 3px 0;
            gap: 8px;
            width: 100%;
        }
        .seat-block, .room-block {
            display: flex;
            align-items: baseline;
            gap: 5px;
            white-space: nowrap;
        }
        .room-block {
            justify-content: flex-end;
            text-align: right;
        }
        .seat-room-label {
            font-size: 12px;
            font-weight: 600;
            color: #333;
            white-space: nowrap;
        }
        .seat-room-value {
            font-size: 18px;
            font-weight: 900;
            color: #000000;
            letter-spacing: -0.2px;
            line-height: 1.2;
            white-space: nowrap;
        }

        /* Khu vực xếp hạng tuổi & thông tin giao dịch */
        .age-transaction-section {
            padding: 2px 0;
            width: 100%;
        }
        .age-rating-text {
            font-size: 11.5px;
            font-weight: 800;
            text-align: center;
            color: #111;
            margin: 4px 0 5px 0;
            line-height: 1.2;
        }
        .transaction-row {
            display: flex;
            justify-content: space-between;
            font-size: 10.5px;
            color: #222;
            margin-bottom: 2px;
            white-space: nowrap;
        }

        /* ================= VÉ 2: COMBO / BẮP NƯỚC ================= */
        .combo-ref-section {
            font-size: 10.5px;
            color: #222;
            margin-bottom: 3px;
            width: 100%;
        }
        .combo-ref-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            white-space: nowrap;
        }
        .combo-ref-movie {
            font-weight: 800;
            color: #000;
            margin-bottom: 3px;
            word-break: break-word;
        }

        .combo-items-list {
            margin: 5px 0;
            width: 100%;
        }
        .combo-items-header {
            font-size: 11.5px;
            font-weight: 900;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 4px;
            color: #000;
        }
        .combo-item-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 4px;
            font-size: 11px;
            width: 100%;
        }
        .combo-item-name {
            flex: 1;
            padding-right: 8px;
            line-height: 1.25;
            word-break: break-word;
        }
        .combo-item-desc {
            font-size: 9.5px;
            color: #555;
            font-style: italic;
        }
        .combo-item-price {
            font-weight: 800;
            white-space: nowrap;
        }

        .combo-total-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12.5px;
            font-weight: 900;
            margin-top: 5px;
            padding-top: 4px;
            border-top: 1.5px dashed #666;
            white-space: nowrap;
        }

        .combo-status-box {
            border: 2px dashed #000;
            background: #f8fafc;
            padding: 5px 8px;
            text-align: center;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.5px;
            margin: 6px 0;
            border-radius: 4px;
            white-space: nowrap;
        }

        .combo-notice-text {
            font-size: 10px;
            font-style: italic;
            text-align: center;
            color: #222;
            line-height: 1.3;
            padding: 0 4px;
            margin: 4px 0;
            word-break: break-word;
        }

        /* Khu vực ghi chú và các liên kết chân trang */
        .footer-note-section {
            text-align: center;
            padding-top: 2px;
            width: 100%;
            overflow: hidden;
        }
        .thank-you-title {
            font-size: 11.5px;
            font-weight: 800;
            color: #000;
            margin-bottom: 2px;
        }
        .event-hotline-note {
            font-size: 9.5px;
            font-style: italic;
            color: #333;
            line-height: 1.25;
            padding: 0 4px;
            word-break: break-word;
        }
        .brand-slogan {
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #000;
            margin-top: 4px;
            margin-bottom: 2px;
            word-break: break-word;
        }
        .brand-contact-links {
            font-size: 9.5px;
            color: #222;
            word-break: break-word;
        }

        /* Khu vực mã vạch & số mã vạch ở dưới cùng */
        .barcode-section {
            text-align: center;
            margin-top: 6px;
            padding-bottom: 4px;
            width: 100%;
            overflow: hidden;
        }
        .barcode-svg-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
            margin: 0 auto;
        }
        .barcode-svg-wrapper svg {
            max-width: 100% !important;
            width: auto !important;
            height: 42px !important;
            display: block;
        }
        .barcode-number-text {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.8px;
            font-family: 'Roboto Mono', 'Courier New', monospace;
            color: #000;
            margin-top: 3px;
            text-align: center;
            word-break: break-all;
            max-width: 100%;
            overflow: hidden;
        }

        /* Cấu hình khi in thực tế bằng lệnh window.print() */
        @media print {
            html, body {
                background: transparent !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }
            @page {
                margin: 0;
                size: portrait;
            }
            .no-print {
                display: none !important;
            }
            .ticket-page-wrapper {
                box-shadow: none !important;
                margin: 0 auto !important;
                width: var(--ticket-width) !important;
                max-width: var(--ticket-width) !important;
                min-width: var(--ticket-width) !important;
                page-break-after: always !important;
                break-after: page !important;
                overflow: hidden !important;
            }
            .ticket-page-wrapper:last-child {
                page-break-after: auto !important;
                break-after: auto !important;
            }
        }
    </style>
</head>
<body>
    @php
        // 1. Chuẩn bị danh sách ghế in (Vé 1)
        $seats = isset($seatsToPrint) && $seatsToPrint->isNotEmpty() 
            ? $seatsToPrint 
            : collect([null]);

        // 2. Lấy thông tin chung của đơn hàng
        $firstSeat = $seats->first();
        $cinema = $firstSeat?->booking?->showtime?->room?->cinema 
               ?? $booking?->showtime?->room?->cinema 
               ?? null;
        $cinemaName = $cinema?->name ?? 'MOVIEGO CINEMAS';
        $cinemaAddress = $cinema?->address ?? 'Tầng 3, Trung tâm Thương mại MovieGo';
        $cinemaTax = $cinema?->tax_code ?? '0106633462';

        $showtime = $firstSeat?->booking?->showtime ?? $booking?->showtime ?? null;
        $movie = $showtime?->movie ?? null;
        $movieTitle = $movie?->title ?? 'Người Nhện: Khởi Đầu Mới';
        $showtimeFormatted = $showtime?->start_time 
            ? $showtime->start_time->format('d/m/Y H:i') 
            : '27/08/2026 21:45';

        $room = $showtime?->room ?? null;
        $roomName = $room?->name ?? 'P6';
        $roomFormat = $room?->format ?? '2D';

        $ageRating = $movie?->age_rating ?? 'T13';
        $ageRatingText = match($ageRating) {
            'P', 'K', 'G' => 'Phim dành cho mọi lứa tuổi (P)',
            'T13', '13+', 'PG', 'PG-13' => 'Phim dành cho khán giả từ 13 tuổi',
            'T16', '16+' => 'Phim dành cho khán giả từ 16 tuổi',
            'T18', '18+', 'R', 'NC-17' => 'Phim dành cho khán giả từ 18 tuổi',
            default => 'Phim dành cho khán giả từ ' . $ageRating . ' tuổi'
        };

        $salerName = Auth::user()?->name ?? 'Phan Thị Minh Anh';
        $printedTime = ($firstSeat?->printed_at ?? $booking?->created_at ?? now())->format('d/m/Y H:i');
        $userPoints = $booking?->user?->points ?? 0;
        $bookingCode = $booking?->booking_code ?? '6717627174821208';

        // Tổng hợp toàn bộ mã ghế trong đơn hàng (dùng cho phiếu combo đối soát)
        $allSeatsArray = [];
        if ($booking && $booking->bookedSeats) {
            foreach ($booking->bookedSeats as $bs) {
                if ($bs->seat) {
                    $allSeatsArray[] = $bs->seat->row_name . $bs->seat->seat_number;
                }
            }
        }
        $allSeatsSummary = !empty($allSeatsArray) ? implode(', ', $allSeatsArray) : ($firstSeat?->seat ? ($firstSeat->seat->row_name . $firstSeat->seat->seat_number) : 'G5');

        // Danh sách Combo bắp nước của đơn hàng
        $combosList = $booking?->combos ?? collect([]);
        $hasCombos = $combosList->isNotEmpty();
        $totalComboPrice = $combosList->sum(fn($c) => $c->pivot->price * $c->pivot->quantity);

        // Khởi tạo Barcode Generator
        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
    @endphp

    <!-- Thanh công cụ điều khiển (Ẩn khi in) -->
    <div class="no-print no-print-bar">
        <div class="no-print-bar-actions">
            <button class="btn-action btn-print-primary" onclick="window.print()">
                🖨️ XÁC NHẬN IN ({{ count($seats) }} VÉ XEM PHIM{{ $hasCombos ? ' + 1 PHIẾU COMBO' : '' }})
            </button>
            <button class="btn-action btn-toggle-mode" id="btnToggleTemplate" onclick="toggleBlankTemplate()">
                📄 Xem Phôi Mẫu Trống
            </button>
        </div>
        <div class="size-controls">
            <span>Kích cỡ khổ in:</span>
            <button type="button" class="btn-size active" onclick="setTicketSize('80mm', this)">80mm (Chuẩn K80)</button>
            <button type="button" class="btn-size" onclick="setTicketSize('90mm', this)">90mm (Rộng vừa)</button>
            <button type="button" class="btn-size" onclick="setTicketSize('100mm', this)">100mm (Phóng to)</button>
        </div>
        <div class="mode-hint">
            💡 <em>Khổ in đã được <strong>phóng to sắc nét</strong>, chữ to rõ ràng, tự động phân tách Vé Phim & Phiếu Combo.</em>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- LIÊN 1: CÁC VÉ XEM PHIM (Mỗi ghế 1 trang in độc lập, có page-break-after)  -->
    <!-- ========================================================================= -->
    @foreach($seats as $seat)
    @php
        $seatModel = $seat?->seat ?? null;
        $seatRow = $seatModel?->row_name ?? 'G';
        $seatNum = $seatModel?->seat_number ?? '5';
        $seatCode = $seatRow . $seatNum;
        $seatType = $seatModel?->seat_type ?? 'Adult';
        $seatTypeFormatted = ($roomFormat ?: '2D') . ' ' . ($seatType == 'VIP' ? 'Adult V.I.P' : ($seatType == 'COUPLE' ? 'Sweetbox Couple' : 'Adult Standard'));
        $seatPrice = $seat?->price_at_booking ?? 60000;

        $seatTicketCode = $seat 
            ? ($seatRow . $seatNum . ($seatType == 'VIP' ? 'VIP' : 'STD') . $seat->id) 
            : '2DADUVIP104';

        // Lấy mã barcode ngắn gọn chuẩn Code128 (không dùng base64 JWT token dài)
        $cleanBookingCode = preg_replace('/[^0-9A-Za-z]/', '', $bookingCode);
        if (empty($cleanBookingCode)) $cleanBookingCode = '6717627174821208';
        
        $ticketBarcode = $cleanBookingCode;
        try {
            $ticketBarcodeSvg = $generator->getBarcode($ticketBarcode, $generator::TYPE_CODE_128, 1.6, 40);
        } catch (\Exception $e) {
            $ticketBarcodeSvg = '<div style="height:40px; border:1px solid #000; display:flex; align-items:center; justify-content:center; font-size:11px;">||||||||||||||||||||||||||||||</div>';
        }
    @endphp

    <div class="ticket-page-wrapper">
        <div class="ticket-structure">
            <!-- Thân nội dung Vé Xem Phim -->
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

                <!-- Khu vực thông tin phim chính (ngày, giờ, tên phim) & Loại vé, Giá -->
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

                <!-- Khu vực số ghế và số phòng -->
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

                <div class="dashed-line"></div>

                <!-- Khu vực ghi chú và các liên kết liên hệ chân trang -->
                <div class="footer-note-section">
                    <div class="thank-you-title">Xin chân thành Cảm ơn quý khách!</div>
                    <div class="event-hotline-note">
                        Khách hàng có nhu cầu mua vé số lượng lớn hoặc thuê phòng tổ chức sự kiện vui lòng liên hệ Hotline để được ưu đãi tốt nhất
                    </div>
                    
                    <div class="dashed-line" style="margin: 5px 0;"></div>

                    <div class="brand-slogan live-data">{{ strtoupper($cinemaName) }} - RẠP PHIM CHẤT LƯỢNG CAO</div>
                    <div class="brand-slogan blank-data" style="display: none;">MOVIEGO CINEMAS - RẠP PHIM CHẤT LƯỢNG CAO</div>
                    
                    <div class="brand-contact-links">www.moviego.vn - Hotline: 1900 6868</div>
                </div>

                <!-- Khu vực mã vạch vé phim ở dưới cùng -->
                <div class="barcode-section">
                    <div class="barcode-svg-wrapper live-data">
                        {!! $ticketBarcodeSvg !!}
                    </div>
                    <div class="barcode-number-text live-data">{{ $ticketBarcode }}</div>

                    <div class="blank-data" style="display: none;">
                        <div class="barcode-svg-wrapper">
                            <div style="height: 40px; width: 90%; border: 1px dashed #999; display: flex; align-items: center; justify-content: center; color: #888; font-size: 10px; font-style: italic;">
                                [ MÃ VẠCH VÉ XEM PHIM ]
                            </div>
                        </div>
                        <div class="barcode-number-text" style="color: #666; font-size: 12px;">[ SỐ MÃ VẠCH ]</div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endforeach

    <!-- ========================================================================= -->
    <!-- LIÊN 2: PHIẾU BẮP NƯỚC / COMBO (In riêng tờ thứ 2 khi có bắp nước)        -->
    <!-- ========================================================================= -->
    @if($hasCombos || request()->query('show_combo'))
    @php
        $comboBarcode = 'CB-' . $cleanBookingCode;
        try {
            $comboBarcodeSvg = $generator->getBarcode($comboBarcode, $generator::TYPE_CODE_128, 1.6, 40);
        } catch (\Exception $e) {
            $comboBarcodeSvg = '<div style="height:40px; border:1px solid #000; display:flex; align-items:center; justify-content:center; font-size:11px;">||||||||||||||||||||||||||||||</div>';
        }
    @endphp

    <div class="ticket-page-wrapper combo-ticket-wrapper">
        <div class="ticket-structure">
            <!-- Thân nội dung Phiếu Bắp Nước -->
            <div class="ticket-center-content">
                
                <!-- 1. Header Tên rạp / Địa chỉ (Giữ nguyên phong cách Beta) -->
                <div class="ticket-header">
                    <div class="cinema-name live-data">RẠP CHIẾU PHIM {{ strtoupper($cinemaName) }}</div>
                    <div class="cinema-address live-data">{{ $cinemaAddress }}</div>
                    <div class="cinema-name blank-data" style="display: none;">[ RẠP CHIẾU PHIM: ........................................ ]</div>
                    <div class="cinema-address blank-data" style="display: none;">[ ĐỊA CHỈ RẠP: ........................................................................ ]</div>
                </div>

                <!-- 2. Tiêu đề "PHIẾU BẮP NƯỚC / COMBO" -->
                <div class="ticket-title-section">
                    <div class="ticket-title-combo">PHIẾU BẮP NƯỚC / COMBO</div>
                    <div style="font-size: 9.5px; text-align: center; color: #555; font-weight: 600;">(PHIẾU NHẬN ĐỒ ĐĂNG KÝ)</div>
                </div>

                <div class="dashed-line"></div>

                <!-- 3. Thông tin tham chiếu phim, suất chiếu, ghế để nhân viên đối soát -->
                <div class="combo-ref-section">
                    <div class="live-data">
                        <div class="combo-ref-movie">Phim: {{ $movieTitle }}</div>
                        <div class="combo-ref-row">
                            <span>Suất: {{ $showtimeFormatted }}</span>
                            <span>Phòng: <strong>{{ $roomName }}</strong></span>
                        </div>
                        <div class="combo-ref-row">
                            <span>Ghế: <strong>{{ $allSeatsSummary }}</strong></span>
                            <span>Mã ĐH: {{ $bookingCode }}</span>
                        </div>
                    </div>
                    <div class="blank-data" style="display: none;">
                        <div class="combo-ref-movie">Phim: [ TÊN BỘ PHIM ]</div>
                        <div class="combo-ref-row">
                            <span>Suất: [ DD/MM/YYYY HH:MM ]</span>
                            <span>Phòng: [ ..... ]</span>
                        </div>
                        <div class="combo-ref-row">
                            <span>Ghế: [ ..... ]</span>
                            <span>Mã ĐH: [ .................... ]</span>
                        </div>
                    </div>
                </div>

                <div class="dashed-line"></div>

                <!-- 4. Nội dung chính: Danh sách các món Combo đã mua -->
                <div class="combo-items-list">
                    <div class="combo-items-header">DANH SÁCH MÓN ĐÃ MUA</div>
                    
                    <div class="live-data">
                        @foreach($combosList as $combo)
                        <div class="combo-item-row">
                            <div class="combo-item-name">
                                <strong>{{ $combo->pivot->quantity }}x</strong> {{ $combo->name }}
                                @if(!empty($combo->description))
                                    <div class="combo-item-desc">({{ $combo->description }})</div>
                                @endif
                            </div>
                            <div class="combo-item-price">
                                {{ number_format($combo->pivot->price * $combo->pivot->quantity) }}đ
                            </div>
                        </div>
                        @endforeach

                        <div class="combo-total-box">
                            <span>TỔNG TIỀN COMBO:</span>
                            <span>{{ number_format($totalComboPrice) }} VND</span>
                        </div>
                    </div>

                    <div class="blank-data" style="display: none;">
                        <div class="combo-item-row">
                            <div class="combo-item-name">
                                <strong>1x</strong> Combo MovieGo 2 (1 Bắp + 2 Nước)
                                <div class="combo-item-desc">(1 Bắp ngọt 60oz + 2 Coca 22oz)</div>
                            </div>
                            <div class="combo-item-price">85,000đ</div>
                        </div>
                        <div class="combo-item-row">
                            <div class="combo-item-name">
                                <strong>1x</strong> Xúc xích nướng phô mai
                            </div>
                            <div class="combo-item-price">35,000đ</div>
                        </div>
                        <div class="combo-total-box">
                            <span>TỔNG TIỀN COMBO:</span>
                            <span>120,000 VND</span>
                        </div>
                    </div>
                </div>

                <!-- 5. Trạng thái nhận hàng -->
                <div class="combo-status-box">
                    [ ĐÃ THU TIỀN — CHƯA NHẬN ]
                </div>

                <!-- 6. Ghi chú hướng dẫn khách hàng -->
                <div class="combo-notice-text">
                    👉 Quý khách vui lòng mang phiếu này đến <strong>Quầy Bắp Nước (Concession)</strong> để nhận đồ.
                </div>

                <div class="dashed-line"></div>

                <!-- 7. Chân phiếu & Hotline -->
                <div class="footer-note-section">
                    <div class="brand-slogan live-data">{{ strtoupper($cinemaName) }} - QUẦY BẮP NƯỚC</div>
                    <div class="brand-slogan blank-data" style="display: none;">MOVIEGO CINEMAS - QUẦY BẮP NƯỚC</div>
                    <div class="brand-contact-links">Saler: {{ $salerName }} | In lúc: {{ $printedTime }}</div>
                </div>

                <!-- 8. Mã vạch riêng của phiếu Combo để quét xuất kho tại quầy bắp nước -->
                <div class="barcode-section">
                    <div class="barcode-svg-wrapper live-data">
                        {!! $comboBarcodeSvg !!}
                    </div>
                    <div class="barcode-number-text live-data">{{ $comboBarcode }}</div>

                    <div class="blank-data" style="display: none;">
                        <div class="barcode-svg-wrapper">
                            <div style="height: 40px; width: 90%; border: 1px dashed #999; display: flex; align-items: center; justify-content: center; color: #888; font-size: 10px; font-style: italic;">
                                [ MÃ VẠCH QUÉT NHẬN BẮP NƯỚC ]
                            </div>
                        </div>
                        <div class="barcode-number-text" style="color: #666; font-size: 12px;">CB-6717627174821208</div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endif

    <script>
        // Hàm thay đổi kích thước khổ in linh hoạt
        function setTicketSize(width, btn) {
            document.documentElement.style.setProperty('--ticket-width', width);
            document.querySelectorAll('.btn-size').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
        }

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
