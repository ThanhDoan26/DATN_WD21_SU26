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

        /* Selected Seat */
        .seat.selected {
            outline: 3px solid #3b82f6;
            outline-offset: 2px;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
            animation: pulseSelection 1.5s infinite;
        }

        .seat.selected.vip {
            outline: 3px solid #d97706;
            box-shadow: 0 6px 16px rgba(217, 119, 6, 0.4);
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

        @keyframes pulseSelection {
            0% { outline-color: rgba(59, 130, 246, 0.8); }
            50% { outline-color: rgba(59, 130, 246, 0.2); }
            100% { outline-color: rgba(59, 130, 246, 0.8); }
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
                                <div class="legend-box selected">✓</div>
                                <span>Ghế Đã Chọn</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box booked">✕</div>
                                <span>Ghế Đã Đặt</span>
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
                                        @foreach($seats->sortBy('seat_number') as $seat)
                                            @php
                                                $isBooked = in_array($seat->id, $bookedSeats);
                                                $isVip = $seat->seat_type === 'VIP';
                                                $seatClass = $isBooked ? 'booked' : ($isVip ? 'vip' : 'regular');
                                            @endphp
                                            <button
                                                onclick="toggleSeat({{ $seat->id }}, this)"
                                                class="seat {{ $seatClass }}"
                                                data-seat-id="{{ $seat->id }}"
                                                data-seat-code="{{ $seat->getSeatCode() }}"
                                                data-seat-type="{{ $seat->seat_type }}"
                                                title="{{ $seat->getSeatCode() }}"
                                                {{ $isBooked ? 'disabled' : '' }}>
                                                {{ $seat->seat_number }}
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
                        <button onclick="proceedToCheckout()"
                                id="checkoutButton"
                                disabled
                                class="w-full bg-primary hover:bg-red-700 disabled:bg-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-4 rounded-lg transition mb-3">
                            <i class="fas fa-arrow-right mr-2"></i>Tiếp tục thanh toán
                        </button>
                        <a href="javascript:history.back()" class="block text-center bg-slate-700 hover:bg-slate-600 text-white py-3 px-4 rounded-lg transition">
                            <i class="fas fa-arrow-left mr-2"></i>Quay lại
                        </a>
                    </div>
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
            if (button.classList.contains('booked')) {
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

        function proceedToCheckout() {
            if (selectedSeats.size === 0) return;

            const seatIds = Array.from(selectedSeats).join(',');
            @if(isset($isWalkIn) && $isWalkIn)
                window.location.href = `/staff/walk-in/checkout?showtime_id=${showtimeId}&seat_ids=${seatIds}`;
            @else
                window.location.href = `/checkout?showtime_id=${showtimeId}&seat_ids=${seatIds}`;
            @endif
        }
    </script>
@endpush
