@extends($layout ?? 'layouts.frontend')

@push('styles')
    <style>
        /* Seat Map Styles */
        .seat-map-wrapper {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            padding: 30px 20px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow-x: auto;
            width: 100%;
        }

        .cinema-screen {
            width: 100%;
            max-width: 600px;
            margin: 0 auto 30px;
            padding: 12px 0;
            text-align: center;
            background: linear-gradient(180deg, rgba(59, 130, 246, 0.15) 0%, rgba(59, 130, 246, 0.05) 100%);
            border-top: 6px solid #1e40af;
            border-radius: 8px 8px 120px 120px;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 8px;
            color: #3b82f6;
            box-shadow: 0 8px 25px -8px rgba(59, 130, 246, 0.3);
            text-transform: uppercase;
        }

        .seat-layout-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 10px 0;
        }

        .seat-row {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            gap: 6px;
        }

        /* Sweetbox rows use tighter gap */
        .seat-row.sweetbox-row {
            gap: 6px;
        }
        .seat-row.sweetbox-row .row-seats {
            gap: 4px;
        }

        .row-label {
            font-size: 0.85rem;
            font-weight: 700;
            color: #64748b;
            width: 30px;
            user-select: none;
            text-align: center;
        }

        .row-seats {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Seat Styles */
        .seat {
            width: 42px;
            height: 42px;
            border: 2px solid transparent;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            user-select: none;
        }

        .seat:hover:not(.booked):not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
            filter: brightness(1.1);
        }

        /* Regular Seat (Xanh) */
        .seat.regular {
            background-color: #0ea5e9;
            border-color: #0284c7;
            color: #ffffff;
        }

        /* VIP Seat (Vàng) */
        .seat.vip {
            background-color: #f59e0b;
            border-color: #d97706;
            color: #1e293b;
            font-weight: 800;
        }

        /* Double / Sweetbox Seat (Hồng) */
        .seat.sweetbox {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            width: 60px;
            height: 40px;
            border-color: #be185d;
            color: #ffffff;
            font-weight: 800;
            border-radius: 10px;
            position: relative;
            font-size: 0.65rem;
            letter-spacing: 0.3px;
        }

        .seat.sweetbox::before {
            content: '♥';
            position: absolute;
            top: -6px;
            right: -4px;
            font-size: 0.55rem;
            color: #fda4af;
            filter: drop-shadow(0 0 2px rgba(236, 72, 153, 0.6));
            animation: sweetboxHeartBeat 2s infinite;
        }

        @keyframes sweetboxHeartBeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.3); }
        }

        .seat.sweetbox.selected::before {
            color: #bbf7d0;
            filter: drop-shadow(0 0 2px rgba(34, 197, 94, 0.6));
        }

        /* Selected Seat */
        .seat.selected {
            background-color: #22c55e !important;
            border-color: #16a34a !important;
            color: transparent !important;
            outline: none !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(34, 197, 94, 0.4);
            animation: pulseSelection 1.5s infinite;
            position: relative;
        }

        .seat.selected::after {
            content: '✓';
            color: #ffffff !important;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 0.9rem;
            font-weight: 700;
        }

        .seat.selected.vip {
            background-color: #22c55e !important;
            border-color: #16a34a !important;
            color: transparent !important;
            outline: none !important;
            box-shadow: 0 6px 16px rgba(34, 197, 94, 0.4);
            position: relative;
        }

        /* Booked Seat */
        .seat.booked {
            background-color: #cbd5e1 !important;
            border-color: #94a3b8 !important;
            color: #64748b !important;
            cursor: not-allowed !important;
            box-shadow: none;
            opacity: 0.6;
        }

        .seat.booked:hover {
            transform: none;
            filter: none;
        }

        /* Broken Seat - Ghế hỏng */
        .seat.broken {
            background: repeating-linear-gradient(45deg, #374151, #374151 4px, #4b5563 4px, #4b5563 8px) !important;
            border-color: #6b7280 !important;
            color: #9ca3af !important;
            cursor: not-allowed !important;
            box-shadow: none;
            opacity: 0.5;
            pointer-events: none;
        }

        .seat.broken:hover {
            transform: none;
            filter: none;
        }

        @keyframes pulseSelection {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
            70% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }

        /* Legend */
        .seat-legend {
            display: flex;
            gap: 20px;
            margin: 0 0 30px 0;
            flex-wrap: wrap;
            justify-content: center;
            background-color: rgba(30, 41, 59, 0.8);
            padding: 20px 30px;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.2);
            width: 100%;
            max-width: 600px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            font-weight: 500;
            color: #cbd5e1;
        }

        .legend-box {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.65rem;
            font-weight: 700;
            border: 2px solid transparent;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .legend-box.regular {
            background-color: #0ea5e9;
            border-color: #0284c7;
        }

        .legend-box.vip {
            background-color: #f59e0b;
            border-color: #d97706;
            color: #1e293b;
        }

        .legend-box.sweetbox {
            background-color: #ec4899;
            border-color: #db2777;
        }

        .legend-box.selected {
            background-color: #3b82f6;
            border-color: #1e40af;
            outline: 2px solid #3b82f6;
        }

        .legend-box.booked {
            background-color: #cbd5e1;
            border-color: #94a3b8;
            opacity: 0.6;
        }

        /* ===================== COUNTDOWN TIMER ===================== */
        #booking-timer-bar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 9999;
            display: none;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 4px 24px rgba(0,0,0,0.5);
            padding: 0 24px;
            height: 60px;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            animation: timerSlideDown 0.4s cubic-bezier(0.4,0,0.2,1) forwards;
        }
        @keyframes timerSlideDown {
            from { transform: translateY(-100%); opacity: 0; }
            to   { transform: translateY(0);     opacity: 1; }
        }
        #timer-progress-track {
            flex: 1; max-width: 300px;
            height: 6px; background: rgba(255,255,255,0.1);
            border-radius: 99px; overflow: hidden;
        }
        #timer-progress-fill {
            height: 100%; border-radius: 99px;
            background: linear-gradient(90deg, #22c55e, #facc15, #e50914);
            background-size: 300% 100%;
            background-position: 0% 50%;
            transition: width 1s linear, background-position 1s linear;
            width: 100%;
        }
        #timer-digits {
            font-variant-numeric: tabular-nums;
            font-size: 1.25rem; font-weight: 800;
            letter-spacing: 1px; min-width: 56px;
            text-align: center; transition: color 0.5s;
            color: #ffffff;
        }
        #timer-digits.urgent { color: #ef4444 !important; animation: timerPulse 0.8s infinite; }
        @keyframes timerPulse {
            0%,100% { opacity: 1; }
            50%      { opacity: 0.55; }
        }
        /* Expired overlay */
        #booking-expired-overlay {
            display: none; position: fixed; inset: 0; z-index: 99999;
            background: rgba(0,0,0,0.85); backdrop-filter: blur(6px);
            align-items: center; justify-content: center;
        }
        #booking-expired-overlay.active { display: flex; }
        .expired-card {
            background: #0f172a; border: 1px solid rgba(239,68,68,0.2);
            border-radius: 24px; padding: 48px 40px;
            max-width: 440px; width: 90%; text-align: center;
            box-shadow: 0 32px 64px rgba(0,0,0,0.6);
            animation: expiredZoomIn 0.35s cubic-bezier(0.34,1.56,0.64,1) forwards;
        }
        @keyframes expiredZoomIn {
            from { transform: scale(0.85); opacity: 0; }
            to   { transform: scale(1);    opacity: 1; }
        }
        .expired-icon {
            width: 72px; height: 72px; border-radius: 50%;
            background: rgba(239,68,68,0.15); border: 2px solid rgba(239,68,68,0.3);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px; font-size: 1.75rem; color: #ef4444;
        }
    </style>
@endpush

@section('content')

    <!-- Page Header -->
    <div class="bg-gradient-to-b from-slate-800 to-slate-900 pt-32 pb-16 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center gap-4 mb-4">
                <i class="fas fa-chair text-primary text-4xl"></i>
                <h1 class="text-5xl md:text-6xl font-bold">Chọn Ghế</h1>
            </div>
            <p class="text-slate-400 text-lg">
                Bước 4: Chọn ghế ngồi của bạn
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <section class="pt-16 pb-40 px-4 min-h-screen">
        <div class="max-w-6xl mx-auto">
            <!-- Movie & Showtime Info -->
            <div class="bg-slate-800 rounded-lg p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <span class="text-slate-400 text-sm">Phim</span>
                        <div class="text-xl font-bold">{{ $showtime->movie->title }}</div>
                    </div>
                    <div>
                        <span class="text-slate-400 text-sm">Rạp</span>
                        <div class="text-xl font-bold">{{ $showtime->room->cinema->name }}</div>
                    </div>
                    <div>
                        <span class="text-slate-400 text-sm">Phòng</span>
                        <div class="text-xl font-bold">{{ $showtime->room->name }}</div>
                    </div>
                    <div>
                        <span class="text-slate-400 text-sm">Suất chiếu</span>
                        <div class="text-xl font-bold">{{ $showtime->start_time->format('H:i | d/m/Y') }}</div>
                    </div>
                </div>
            </div>

            <div class="w-full">
                <!-- Seat Map -->
                <div class="w-full overflow-x-auto">
                    <!-- Seat Map Container -->
                    <div class="seat-map-wrapper min-w-max mx-auto">
                        <!-- Legend -->
                        <div class="seat-legend">
                            <div class="legend-item">
                                <div class="legend-box regular">1</div>
                                <span>Ghế Thường</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box vip">V</div>
                                <span>Ghế VIP</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box sweetbox">S</div>
                                <span>Ghế Đôi</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box selected">✓</div>
                                <span>Ghế Đã Chọn</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box booked">✕</div>
                                <span>Ghế Đã Đặt</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box" style="background:repeating-linear-gradient(45deg,#374151,#374151 3px,#4b5563 3px,#4b5563 6px);border-color:#6b7280;opacity:0.5;">✕</div>
                                <span>Ghế Hỏng</span>
                            </div>
                        </div>

                        <!-- Cinema Screen -->
                        <div class="cinema-screen">
                            <i class="fas fa-tv mr-2"></i>MÀN CHIẾU
                        </div>

                        <!-- Seat Layout -->
                        <div class="seat-layout-container">
                            @php
                                $groupedSeats = $room->seats->groupBy('row_name')->sortKeys();
                            @endphp

                            @foreach($groupedSeats as $row => $seats)
                                @php
                                    $hasSweetbox = $seats->contains(fn($s) => $s->seat_type === 'Sweetbox' || $s->seat_type === 'Double');
                                @endphp
                                <div class="seat-row {{ $hasSweetbox ? 'sweetbox-row' : '' }}">
                                    <span class="row-label">{{ $row }}</span>
                                    <div class="row-seats">
                                        @foreach($seats->sortBy(fn($s) => (int)$s->seat_number) as $seat)
                                            @php
                                                $isBooked = in_array($seat->id, $bookedSeats);
                                                $isMyPending = in_array($seat->id, $myPendingSeats ?? []);
                                                $isBroken = $seat->status === \App\Models\Seat::STATUS_BROKEN;
                                                $isVip = $seat->seat_type === 'VIP';
                                                $isSweetbox = $seat->seat_type === 'Sweetbox' || $seat->seat_type === 'Double';
                                                
                                                if ($isBroken) {
                                                    $seatClass = 'broken';
                                                } elseif ($isBooked) {
                                                    $seatClass = 'booked';
                                                } elseif ($isMyPending) {
                                                    // Nếu là ghế đang giữ của chính user, hiển thị như "Ghế đã chọn" (màu xanh)
                                                    $seatClass = 'selected ' . ($isSweetbox ? 'sweetbox' : ($isVip ? 'vip' : 'regular'));
                                                } elseif ($isSweetbox) {
                                                    $seatClass = 'sweetbox';
                                                } elseif ($isVip) {
                                                    $seatClass = 'vip';
                                                } else {
                                                    $seatClass = 'regular';
                                                }
                                                
                                                $isDisabled = $isBooked || $isBroken;
                                            @endphp
                                            <button
                                                type="button"
                                                onclick="toggleSeat({{ $seat->id }}, this)"
                                                class="seat {{ $seatClass }}"
                                                data-seat-id="{{ $seat->id }}"
                                                data-seat-code="{{ $seat->getSeatCode() }}"
                                                data-seat-type="{{ $seat->seat_type }}"
                                                title="{{ $seat->getSeatCode() }}{{ $isBroken ? ' (Ghế hỏng)' : '' }}"
                                                {{ $isDisabled ? 'disabled' : '' }}>
                                                {{ $isBroken ? '✕' : $seat->seat_number }}
                                            </button>
                                        @endforeach
                                    </div>
                                    <span class="row-label">{{ $row }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Sticky Bottom Bar: Summary & Checkout -->
    <form id="seat-selection-form" action="{{ route('checkout') }}" method="GET" class="fixed bottom-0 left-0 w-full bg-slate-900 border-t border-slate-700 shadow-[0_-10px_40px_rgba(0,0,0,0.5)] z-50">
        <input type="hidden" name="showtime_id" id="form_showtime_id" value="{{ $showtime->id }}" />
        <input type="hidden" name="seat_ids" id="form_seat_ids" value="" />
        
        <div class="max-w-7xl mx-auto px-4 py-4 md:py-6 flex flex-col md:flex-row items-center justify-between gap-6">
            <!-- Selected Seats -->
            <div class="flex-1 w-full min-w-0">
                <div class="text-slate-400 text-sm mb-1">Ghế đã chọn: <span id="seatCount" class="text-white font-bold ml-1">0 ghế</span></div>
                <div id="selectedSeatsDisplay" class="truncate text-lg font-bold text-primary">
                    <span class="text-slate-500 text-base font-normal">Vui lòng chọn ghế trên sơ đồ</span>
                </div>
            </div>

            <!-- Total Price -->
            <div class="text-left md:text-right w-full md:w-auto">
                <div class="text-slate-400 text-sm mb-1">Tổng cộng:</div>
                <div class="text-2xl font-bold text-white" id="totalPrice">0₫</div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 w-full md:w-auto mt-2 md:mt-0">
                <a href="javascript:history.back()" class="bg-slate-700 hover:bg-slate-600 text-white font-medium py-3 px-6 rounded-lg transition whitespace-nowrap text-center">
                    Quay lại
                </a>
                <button type="button"
                        onclick="proceedToCheckout()"
                        id="checkoutButton"
                        disabled
                        class="bg-primary hover:bg-red-700 disabled:bg-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-8 rounded-lg transition whitespace-nowrap flex-1 md:flex-none text-center">
                    Tiếp tục thanh toán <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
    </form>

    {{-- ======== COUNTDOWN TIMER BAR (sticky top) ======== --}}
    <div id="booking-timer-bar">
        <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
            <i class="far fa-clock" style="color:#facc15;font-size:1.1rem"></i>
            <span style="color:#94a3b8;font-size:0.85rem;font-weight:500" class="hidden-xs">Thời gian giữ ghế</span>
            <span id="timer-digits">10:00</span>
        </div>
        <div id="timer-progress-track">
            <div id="timer-progress-fill"></div>
        </div>
        <span style="font-size:0.75rem;color:#64748b;flex-shrink:0" class="hidden-xs">Thanh toán trước khi hết giờ</span>
    </div>

    {{-- ======== SESSION EXPIRED OVERLAY ======== --}}
    <div id="booking-expired-overlay">
        <div class="expired-card">
            <div class="expired-icon"><i class="fas fa-clock"></i></div>
            <h2 style="font-size:1.5rem;font-weight:800;color:#fff;margin-bottom:12px">Thời gian giữ ghế đã hết</h2>
            <p style="color:#94a3b8;font-size:0.9rem;line-height:1.6;margin-bottom:28px">
                Quá <strong style="color:#fff">10 phút</strong> mà chưa hoàn tất thanh toán,<br>
                ghế của bạn đã được giải phóng.<br>
                Vui lòng chọn lại ghế.
            </p>
            <button onclick="location.reload()"
               style="display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#e50914;color:#fff;font-weight:700;padding:14px 32px;border-radius:16px;border:none;cursor:pointer;width:100%;transition:background 0.2s">
                <i class="fas fa-redo"></i> Chọn lại ghế
            </button>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const showtimeId = {{ $showtime->id }};
        const surcharge = {{ $showtime->surcharge ?? 0 }};
        const selectedSeats = new Set();
        const ticketPrices = @json($ticketPrices->mapWithKeys(fn($price) => [$price->seat_type => (float) $price->price]));
        const STORAGE_KEY = 'selectedSeats_showtime_' + showtimeId;

        // Restore previously selected seats from sessionStorage (e.g. when navigating back from checkout)
        function restoreSelectedSeats() {
            try {
                // 1. Phục hồi các ghế đang giữ (Pending) từ Database do PHP render sẵn
                document.querySelectorAll('.seat.selected').forEach(button => {
                    const seatId = parseInt(button.getAttribute('data-seat-id'));
                    if (!isNaN(seatId)) {
                        selectedSeats.add(seatId);
                    }
                });

                // 2. Phục hồi các ghế từ sessionStorage (nếu người dùng vừa F5)
                const stored = sessionStorage.getItem(STORAGE_KEY);
                if (stored) {
                    const ids = JSON.parse(stored);
                    ids.forEach(id => {
                        const button = document.querySelector(`[data-seat-id="${id}"]`);
                        if (button && !button.disabled && !button.classList.contains('booked') && !button.classList.contains('broken')) {
                            selectedSeats.add(id);
                            button.classList.add('selected');
                        }
                    });
                }
                
                updateSummary();
                // Clear after restoring so it doesn't persist indefinitely
                sessionStorage.removeItem(STORAGE_KEY);
            } catch (e) {
                // Ignore parse errors
            }
        }

        // Save selected seats to sessionStorage
        function saveSelectedSeats() {
            const ids = Array.from(selectedSeats);
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
        }

        function toggleSeat(seatId, button) {
            if (button.classList.contains('booked') || button.classList.contains('broken')) {
                return;
            }

            if (selectedSeats.has(seatId)) {
                selectedSeats.delete(seatId);
                button.classList.remove('selected');
            } else {
                selectedSeats.add(seatId);
                button.classList.add('selected');
            }

            updateSummary();
        }

        function updateSummary() {
            const count = selectedSeats.size;
            document.getElementById('seatCount').textContent = count + ' ghế';

            if (count === 0) {
                document.getElementById('selectedSeatsDisplay').innerHTML = '<span class="text-slate-400 text-sm">Chọn ghế để tiếp tục</span>';
                document.getElementById('checkoutButton').disabled = true;
                document.getElementById('totalPrice').textContent = '0₫';
            } else {
                const codes = Array.from(selectedSeats).map(id => {
                    return document.querySelector(`[data-seat-id="${id}"]`).getAttribute('data-seat-code');
                }).join(', ');

                document.getElementById('selectedSeatsDisplay').innerHTML = `<span class="text-white font-bold">${codes}</span>`;
                document.getElementById('checkoutButton').disabled = false;

                let total = 0;
                Array.from(selectedSeats).forEach(id => {
                    const button = document.querySelector(`[data-seat-id="${id}"]`);
                    if (!button) return;
                    const seatType = button.getAttribute('data-seat-type');
                    const basePrice = ticketPrices[seatType] || 0;
                    total += basePrice + surcharge;
                });

                document.getElementById('totalPrice').textContent = number_format(total) + '₫';
            }
        }

        function number_format(num) {
            return new Intl.NumberFormat('vi-VN').format(num);
        }

        function validateSeatSelection() {
            let result = { isValid: true, bypassRule: null };
            
            const rows = document.querySelectorAll('.row-seats');
            for (let r = 0; r < rows.length; r++) {
                const rowElement = rows[r];
                const seats = Array.from(rowElement.querySelectorAll('.seat'));
                const totalSeats = seats.length;
                
                let emptyBlocks = [];
                let currentEmptyBlock = [];
                
                seats.forEach((seat, index) => {
                    let isBookedOrBroken = seat.classList.contains('booked') || seat.classList.contains('broken') || seat.disabled;
                    let isSelected = seat.classList.contains('selected');
                    let isAvailableEmpty = !isBookedOrBroken && !isSelected;
                    
                    if (isAvailableEmpty) {
                        currentEmptyBlock.push(index);
                    } else {
                        if (currentEmptyBlock.length > 0) {
                            emptyBlocks.push(currentEmptyBlock);
                            currentEmptyBlock = [];
                        }
                    }
                });
                
                if (currentEmptyBlock.length > 0) {
                    emptyBlocks.push(currentEmptyBlock);
                }
                
                // Check each empty block for gap = 1
                for (let i = 0; i < emptyBlocks.length; i++) {
                    const block = emptyBlocks[i];
                    if (block.length === 1) {
                        let emptyIndex = block[0];
                        
                        let leftAdjacent = (emptyIndex > 0) ? seats[emptyIndex - 1] : null;
                        let rightAdjacent = (emptyIndex < totalSeats - 1) ? seats[emptyIndex + 1] : null;
                        
                        let isLeftSelected = leftAdjacent && leftAdjacent.classList.contains('selected');
                        let isRightSelected = rightAdjacent && rightAdjacent.classList.contains('selected');
                        
                        // We only care if this single empty seat is adjacent to at least one selected seat
                        if (isLeftSelected || isRightSelected) {
                            let isAbsoluteStart = (emptyIndex === 0);
                            let isAbsoluteEnd = (emptyIndex === totalSeats - 1);
                            
                            if (isAbsoluteStart || isAbsoluteEnd) {
                                // Boundary Exception applies
                                result.bypassRule = 'BOUNDARY_EXCEPTION';
                            } else {
                                // REJECTED: Single seat in the middle
                                return { 
                                    isValid: false, 
                                    errorCode: 'SINGLE_SEAT_IN_MIDDLE', 
                                    message: 'Không thể bỏ trống 1 ghế ở giữa. Vui lòng chọn ghế sát mép hoặc chọn liên tiếp.' 
                                };
                            }
                        }
                    }
                }
            }
            
            return result;
        }

        function proceedToCheckout() {
            if (selectedSeats.size === 0) return;

            const validation = validateSeatSelection();
            if (!validation.isValid) {
                alert(validation.message || "Ghế không hợp lệ.");
                return;
            }

            // Save selected seats before navigating to checkout
            saveSelectedSeats();

            const seatIds = Array.from(selectedSeats).join(',');
            document.getElementById('form_seat_ids').value = seatIds;
            document.getElementById('seat-selection-form').submit();
        }

        // --- Xử lý thông báo lỗi từ session ---
        @if(session('error'))
            alert("{{ session('error') }}");
        @endif

        // --- Unit Tests cho Boundary Exception ---
        function runSeatValidationTests() {
            console.log("Running Seat Validation Tests...");
            
            function createMockRow(totalSeats, selectedIndexes) {
                let row = document.createElement('div');
                row.className = 'row-seats';
                for (let i = 0; i < totalSeats; i++) {
                    let seat = document.createElement('div');
                    seat.className = 'seat';
                    if (selectedIndexes.includes(i)) {
                        seat.classList.add('selected');
                    }
                    row.appendChild(seat);
                }
                return row;
            }

            const originalQuerySelectorAll = document.querySelectorAll.bind(document);
            let mockRows = [];
            
            document.querySelectorAll = function(selector) {
                if (selector === '.row-seats') return mockRows;
                return originalQuerySelectorAll(selector);
            };

            try {
                // Scenario A1 (Boundary Left): 5 seats [1,2,3,4,5], user selects [2,3,4,5] -> indices [1,2,3,4]
                mockRows = [createMockRow(5, [1, 2, 3, 4])];
                let resA1 = validateSeatSelection();
                console.assert(resA1.isValid === true && resA1.bypassRule === 'BOUNDARY_EXCEPTION', "Test A1 Failed");
                if (resA1.isValid) console.log("Scenario A1 Passed");

                // Scenario A2 (Boundary Right): 5 seats [1,2,3,4,5], user selects [1,2,3,4] -> indices [0,1,2,3]
                mockRows = [createMockRow(5, [0, 1, 2, 3])];
                let resA2 = validateSeatSelection();
                console.assert(resA2.isValid === true && resA2.bypassRule === 'BOUNDARY_EXCEPTION', "Test A2 Failed");
                if (resA2.isValid) console.log("Scenario A2 Passed");

                // Scenario B1 (Middle Gap): 5 seats [1,2,3,4,5], user selects [1,2,4,5] -> indices [0,1,3,4], empty [2]
                mockRows = [createMockRow(5, [0, 1, 3, 4])];
                let resB1 = validateSeatSelection();
                console.assert(resB1.isValid === false && resB1.errorCode === 'SINGLE_SEAT_IN_MIDDLE', "Test B1 Failed");
                if (!resB1.isValid) console.log("Scenario B1 Passed");
                
                // Scenario C1 (Trapped next to booked): 5 seats, index 4 booked, user selects 0,1,2. Empty at 3.
                let mockRowC1 = createMockRow(5, [0, 1, 2]);
                mockRowC1.children[4].classList.add('booked');
                mockRows = [mockRowC1];
                let resC1 = validateSeatSelection();
                console.assert(resC1.isValid === false && resC1.errorCode === 'SINGLE_SEAT_IN_MIDDLE', "Test C1 Failed");
                if (!resC1.isValid) console.log("Scenario C1 Passed (Trapped next to booked)");
            } catch (e) {
                console.error("Test execution failed:", e);
            } finally {
                // Restore original function
                document.querySelectorAll = originalQuerySelectorAll;
                console.log("Tests Completed.");
            }
        }
        
        // Execute tests automatically on load for verification
        runSeatValidationTests();

        // --- Real-time Polling Logic ---
        function pollBookedSeats() {
            fetch(`/api/booking/showtime/${showtimeId}/booked-seats`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.bookedSeats) {
                        const bookedSeatIds = data.bookedSeats;
                        
                        document.querySelectorAll('.seat').forEach(button => {
                            const seatId = parseInt(button.getAttribute('data-seat-id'));
                            const isCurrentlyBooked = button.classList.contains('booked');
                            const shouldBeBooked = bookedSeatIds.includes(seatId);

                            if (shouldBeBooked && !isCurrentlyBooked) {
                                // Ghế vừa bị người khác đặt
                                button.classList.add('booked');
                                button.disabled = true;
                                button.title = "Ghế đã được đặt";
                                
                                // Nếu người dùng đang chọn ghế này thì phải bỏ chọn
                                if (selectedSeats.has(seatId)) {
                                    selectedSeats.delete(seatId);
                                    button.classList.remove('selected');
                                    alert("Rất tiếc! Một trong những ghế bạn đang chọn vừa được người khác đặt trước. Vui lòng chọn ghế khác.");
                                }
                            } else if (!shouldBeBooked && isCurrentlyBooked) {
                                // Ghế vừa được giải phóng
                                button.classList.remove('booked');
                                button.disabled = false;
                                button.title = button.getAttribute('data-seat-code');
                            }
                        });

                        updateSummary();
                    }
                })
                .catch(error => console.error('Error polling seats:', error));
        }

        // --- Real-time Timer Logic ---
        const TIMEOUT_SECONDS = {{ \App\Services\BookingService::getHoldDuration() * 60 }};
        const serverExpiresAt = @json($expiresAtMs ?? null);

        function initSelectSeatsTimer() {
            let expiresAtMs = null;
            if (serverExpiresAt) {
                expiresAtMs = parseInt(serverExpiresAt, 10);
                sessionStorage.setItem('booking_expires_at', expiresAtMs.toString());
            } else {
                const stored = sessionStorage.getItem('booking_expires_at');
                if (stored) {
                    expiresAtMs = parseInt(stored, 10);
                }
            }

            if (!expiresAtMs || expiresAtMs <= Date.now()) {
                sessionStorage.removeItem('booking_expires_at');
                return;
            }

            const timerBar = document.getElementById('booking-timer-bar');
            const timerDigits = document.getElementById('timer-digits');
            const timerFill = document.getElementById('timer-progress-fill');
            const expiredOverlay = document.getElementById('booking-expired-overlay');

            if (timerBar) timerBar.style.display = 'flex';

            function tick() {
                const now = Date.now();
                const remaining = Math.max(0, Math.floor((expiresAtMs - now) / 1000));
                const mins = String(Math.floor(remaining / 60)).padStart(2, '0');
                const secs = String(remaining % 60).padStart(2, '0');
                const display = `${mins}:${secs}`;

                if (timerDigits) timerDigits.textContent = display;
                const pct = (remaining / TIMEOUT_SECONDS) * 100;
                if (timerFill) {
                    timerFill.style.width = pct + '%';
                    timerFill.style.backgroundPosition = `${100 - pct}% 50%`;
                }

                if (remaining <= 90) {
                    if (timerDigits) timerDigits.classList.add('urgent');
                } else {
                    if (timerDigits) timerDigits.classList.remove('urgent');
                }

                if (remaining <= 0) {
                    sessionStorage.removeItem('booking_expires_at');
                    if (timerBar) timerBar.style.display = 'none';
                    if (expiredOverlay) expiredOverlay.classList.add('active');
                }
            }

            tick();
            setInterval(tick, 1000);
        }

        // Restore seats on page load, then start polling every 3 seconds
        document.addEventListener('DOMContentLoaded', () => {
            restoreSelectedSeats();
            initSelectSeatsTimer();
            setInterval(pollBookedSeats, 3000);
        });
    </script>
@endpush
