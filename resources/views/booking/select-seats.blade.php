@extends($layout ?? 'layouts.frontend')

@push('styles')
    <style>
        /* Seat Map Styles */
        .seat-map-wrapper {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            align-items: center;
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
            gap: 12px;
            width: 100%;
            min-width: 480px;
            padding: 10px 0;
        }

        .seat-row {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            gap: 8px;
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
            background-color: #ec4899;
            width: 92px; /* 2 seats (42px * 2) + gap (8px) = 92px */
            border-color: #db2777;
            color: #ffffff;
            font-weight: 800;
        }

        /* Selected Seat */
        .seat.selected {
            background-color: #22c55e !important;
            border-color: #16a34a !important;
            color: #ffffff !important;
            outline: none !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(34, 197, 94, 0.4);
            animation: pulseSelection 1.5s infinite;
        }

        .seat.selected.vip {
            background-color: #22c55e !important;
            border-color: #16a34a !important;
            color: #ffffff !important;
            outline: none !important;
            box-shadow: 0 6px 16px rgba(34, 197, 94, 0.4);
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
    <section class="py-16 px-4 min-h-screen">
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Seat Map -->
                <div class="lg:col-span-2">
                    <!-- Seat Map Container -->
                    <div class="seat-map-wrapper">
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
                                <div class="seat-row">
                                    <span class="row-label">{{ $row }}</span>
                                    <div class="row-seats">
                                        @foreach($seats->sortBy(fn($s) => (int)$s->seat_number) as $seat)
                                            @php
                                                $isBooked = in_array($seat->id, $bookedSeats);
                                                $isBroken = $seat->status === \App\Models\Seat::STATUS_BROKEN;
                                                $isVip = $seat->seat_type === 'VIP';
                                                $isSweetbox = $seat->seat_type === 'Sweetbox' || $seat->seat_type === 'Double';
                                                
                                                if ($isBroken) {
                                                    $seatClass = 'broken';
                                                } elseif ($isBooked) {
                                                    $seatClass = 'booked';
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

                <!-- Sidebar: Summary & Checkout -->
                <div>
                    <form id="seat-selection-form" action="{{ route('checkout') }}" method="GET">
                        <input type="hidden" name="showtime_id" id="form_showtime_id" value="{{ $showtime->id }}" />
                        <input type="hidden" name="seat_ids" id="form_seat_ids" value="" />

                        <!-- Summary Card -->
                        <div class="bg-slate-800 rounded-lg p-6 sticky top-24">
                            <h3 class="text-xl font-bold mb-6">Thông tin đặt vé</h3>

                            <!-- Selected Seats -->
                            <div class="mb-6 pb-6 border-b border-slate-700">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-slate-400">Ghế đã chọn:</span>
                                <span class="text-lg font-bold" id="seatCount">0 ghế</span>
                            </div>
                            <div id="selectedSeatsDisplay" class="bg-slate-900 rounded p-3 min-h-12 flex items-center">
                                <span class="text-slate-400 text-sm">Chọn ghế để tiếp tục</span>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        @if($ticketPrices->count() > 0)
                            <div class="mb-6 pb-6 border-b border-slate-700">
                                <div class="text-slate-400 text-sm mb-3">Giá vé:</div>
                                @foreach($ticketPrices as $price)
                                    <div class="flex justify-between text-sm mb-2">
                                        <span>{{ $price->type ?? 'Vé bình thường' }}</span>
                                        <span class="font-bold">{{ number_format($price->price) }}₫</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Total -->
                        <div class="mb-8">
                            <div class="flex justify-between items-center text-xl font-bold">
                                <span>Tổng cộng:</span>
                                <span class="text-2xl text-primary" id="totalPrice">0₫</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <button type="button"
                                id="checkoutButton"
                                onclick="proceedToCheckout()"
                                disabled
                                class="w-full bg-primary hover:bg-red-700 disabled:bg-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-4 rounded-lg transition mb-3">
                            <i class="fas fa-arrow-right mr-2"></i>Tiếp tục thanh toán
                        </button>
                        <a href="javascript:history.back()" class="block text-center bg-slate-700 hover:bg-slate-600 text-white py-3 px-4 rounded-lg transition">
                            <i class="fas fa-arrow-left mr-2"></i>Quay lại
                        </a>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </section>

@endsection

@push('scripts')
    <script>
        const showtimeId = {{ $showtime->id }};
        const surcharge = {{ $showtime->surcharge ?? 0 }};
        const selectedSeats = new Set();
        const ticketPrices = @json($ticketPrices->mapWithKeys(fn($price) => [$price->seat_type => (float) $price->price]));

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
            let isValid = true;
            
            document.querySelectorAll('.row-seats').forEach(rowElement => {
                const seats = Array.from(rowElement.querySelectorAll('.seat'));
                
                // Split into blocks by unavailable seats
                let blocks = [];
                let currentBlock = [];
                
                seats.forEach(seat => {
                    if (seat.classList.contains('booked') || seat.disabled) {
                        if (currentBlock.length > 0) {
                            blocks.push(currentBlock);
                            currentBlock = [];
                        }
                    } else {
                        currentBlock.push(seat);
                    }
                });
                
                if (currentBlock.length > 0) {
                    blocks.push(currentBlock);
                }
                
                // Check each block
                blocks.forEach(block => {
                    let selectedIndices = [];
                    block.forEach((seat, index) => {
                        if (seat.classList.contains('selected')) {
                            selectedIndices.push(index);
                        }
                    });
                    
                    if (selectedIndices.length > 1) {
                        let first = Math.min(...selectedIndices);
                        let last = Math.max(...selectedIndices);
                        let countInRange = last - first + 1;
                        
                        // If there is a gap, the range will be larger than the number of selected seats
                        if (countInRange > selectedIndices.length) {
                            isValid = false;
                        }
                    }
                });
            });
            
            return isValid;
        }

        function proceedToCheckout() {
            if (selectedSeats.size === 0) return;

            if (!validateSeatSelection()) {
                alert("Bạn chỉ được chọn các ghế liền kề nhau. Không được để trống ghế ở giữa.");
                return;
            }

            const seatIds = Array.from(selectedSeats).join(',');
            document.getElementById('form_seat_ids').value = seatIds;
            document.getElementById('seat-selection-form').submit();
        }
    </script>
@endpush
