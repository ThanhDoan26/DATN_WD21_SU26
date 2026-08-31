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

        /* Sweetbox rows gap matches regular rows */
        .seat-row.sweetbox-row {
            gap: 6px;
        }
        .seat-row.sweetbox-row .row-seats {
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
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            width: 92px;
            height: 42px;
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
            pointer-events: none !important;
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

        @keyframes seatConflictShake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-4px); }
            40%, 80% { transform: translateX(4px); }
        }

        .seat.seat-conflict-flash {
            animation: seatConflictShake 0.4s ease-in-out 3;
            border-color: #ef4444 !important;
            box-shadow: 0 0 14px rgba(239, 68, 68, 0.9) !important;
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
            <!-- Navigation -->
            <div class="flex items-center gap-4 mb-6">
                <a href="{{ route('booking.select-dates-showtimes', ['movie' => $showtime->movie_id, 'cinema' => $showtime->room->cinema_id]) }}" 
                   class="text-slate-300 hover:text-white flex items-center gap-2 transition-colors px-4 py-2 bg-slate-800/50 rounded-lg backdrop-blur-sm border border-slate-700/50 hover:bg-slate-700/50">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <a href="{{ route('home') }}" 
                   class="text-slate-300 hover:text-white flex items-center gap-2 transition-colors px-4 py-2 bg-slate-800/50 rounded-lg backdrop-blur-sm border border-slate-700/50 hover:bg-slate-700/50">
                    <i class="fas fa-home"></i> Trang chủ
                </a>
            </div>

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
                                                
                                                $typeClass = $isSweetbox ? 'sweetbox' : ($isVip ? 'vip' : 'regular');

                                                if ($isBroken) {
                                                    $seatClass = 'broken ' . $typeClass;
                                                } elseif ($isBooked) {
                                                    $seatClass = 'booked ' . $typeClass;
                                                } elseif ($isMyPending) {
                                                    // Nếu là ghế đang giữ của chính user, hiển thị như "Ghế đã chọn" (màu xanh)
                                                    $seatClass = 'selected ' . $typeClass;
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
    <form id="seat-selection-form" action="{{ route('checkout.init') }}" method="POST" class="fixed bottom-0 left-0 w-full bg-slate-900 border-t border-slate-700 shadow-[0_-10px_40px_rgba(0,0,0,0.5)] z-50">
        @csrf
        <input type="hidden" name="showtime_id" id="form_showtime_id" value="{{ $showtime->id }}" />
        <input type="hidden" name="seat_ids" id="form_seat_ids" value="" />
        <input type="hidden" name="combos" id="form_combos" value="" />
        
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
                <button type="button" onclick="handleCancelClick()" id="btnCancelAction" class="bg-slate-700 hover:bg-red-600 text-white font-medium py-3 px-6 rounded-lg transition whitespace-nowrap text-center border border-slate-600">
                    Hủy đặt vé
                </button>
                <a href="{{ route('booking.select-dates-showtimes', ['movie' => $showtime->movie_id, 'cinema' => $showtime->room->cinema_id]) }}" class="bg-slate-700 hover:bg-slate-600 text-white font-medium py-3 px-6 rounded-lg transition whitespace-nowrap text-center">
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

    {{-- ======== CANCEL CONFIRMATION MODAL ======== --}}
    <div id="cancelModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.8); align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div style="background: #1e293b; padding: 2rem; border-radius: 1rem; max-width: 400px; width: 90%; border: 1px solid #334155; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
            <div style="font-size: 3rem; color: #ef4444; text-align: center; margin-bottom: 1rem;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 style="color: white; font-size: 1.25rem; font-weight: bold; text-align: center; margin-bottom: 1rem;">Xác nhận hủy đặt vé</h3>
            <p style="color: #94a3b8; text-align: center; margin-bottom: 2rem;">
                Bạn có chắc muốn hủy lượt đặt vé này không? Các ghế bạn đang giữ sẽ được nhả lại cho hệ thống.
            </p>
            <div style="display: flex; gap: 1rem;">
                <button type="button" onclick="document.getElementById('cancelModal').style.display='none'" style="flex: 1; padding: 0.75rem; background: #334155; color: white; border-radius: 0.5rem; font-weight: 500; transition: background 0.2s;">
                    Đóng
                </button>
                <button type="button" onclick="confirmCancelBooking()" id="btnConfirmCancel" style="flex: 1; padding: 0.75rem; background: #ef4444; color: white; border-radius: 0.5rem; font-weight: bold; transition: background 0.2s;">
                    Hủy đặt vé
                </button>
            </div>
        </div>
    </div>

    {{-- ======== RESUME BOOKING MODAL ======== --}}
    <div id="resumeBookingModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.8); align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div style="background: #1e293b; padding: 2rem; border-radius: 1rem; max-width: 450px; width: 90%; border: 1px solid #334155; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
            <div style="font-size: 3rem; color: #facc15; text-align: center; margin-bottom: 1rem;">
                <i class="fas fa-clock"></i>
            </div>
            <h3 style="color: white; font-size: 1.25rem; font-weight: bold; text-align: center; margin-bottom: 1rem;">Đơn hàng đang chờ thanh toán</h3>
            <p style="color: #94a3b8; text-align: center; margin-bottom: 2rem;">
                Bạn đang có một đơn hàng giữ chỗ cho suất chiếu này chưa hoàn tất thanh toán. Bạn có muốn tiếp tục thanh toán đơn hàng này không?
            </p>
            <div style="display: flex; gap: 1rem;">
                <button type="button" onclick="cancelAndStartOver()" id="btnStartOver" style="flex: 1; padding: 0.75rem; background: #334155; color: white; border-radius: 0.5rem; font-weight: 500; transition: background 0.2s;">
                    Đặt lại từ đầu
                </button>
                <button type="button" onclick="proceedToCheckout()" style="flex: 1; padding: 0.75rem; background: #22c55e; color: white; border-radius: 0.5rem; font-weight: bold; transition: background 0.2s; text-align: center;">
                    Tiếp tục thanh toán
                </button>
            </div>
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
        const MAX_TICKETS_PER_BOOKING = {{ (int) config('booking.seat_hold.max_seats_per_booking', 8) }};

        // Restore previously selected seats from DB + sessionStorage (e.g. when clicking "Quay lại chọn thêm ghế")
        function restoreSelectedSeats() {
            try {
                const resumeKey = 'resume_seats_showtime_' + showtimeId;
                const isResuming = sessionStorage.getItem(resumeKey) === '1' || new URLSearchParams(window.location.search).has('resume_seats');
                const serverHasPendingSeats = @json(!empty($myPendingSeats));
                const stored = sessionStorage.getItem(STORAGE_KEY);

                // 1. Phục hồi các ghế đang giữ (Pending) từ Database do PHP render sẵn
                document.querySelectorAll('.seat.selected').forEach(button => {
                    const seatId = parseInt(button.getAttribute('data-seat-id'));
                    if (!isNaN(seatId)) {
                        selectedSeats.add(seatId);
                    }
                });

                // 2. Chỉ phục hồi từ sessionStorage khi người dùng thực sự bấm "Quay lại chọn thêm ghế" (có cờ isResuming) HOẶC Server xác nhận có đơn Pending
                if ((isResuming || serverHasPendingSeats) && stored) {
                    const ids = JSON.parse(stored);
                    ids.forEach(id => {
                        const button = document.querySelector(`[data-seat-id="${id}"]`);
                        if (button && !button.disabled && !button.classList.contains('booked') && !button.classList.contains('broken')) {
                            selectedSeats.add(id);
                            button.classList.add('selected');
                        }
                    });
                }

                // Dọn dẹp cờ resume & storage tạm sau khi khôi phục xong để không ảnh hưởng phiên mới
                sessionStorage.removeItem(resumeKey);
                sessionStorage.removeItem(STORAGE_KEY);

                updateSummary();
            } catch (e) {
                // Ignore parse errors
            }
        }

        function openCancelModal() {
            const modal = document.getElementById('cancelModal');
            if (modal) {
                modal.style.display = 'flex';
            }
        }

        function closeCancelModal() {
            const modal = document.getElementById('cancelModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        function handleCancelClick() {
            const serverHasPendingSeats = @json(!empty($myPendingSeats));
            const hasActiveBooking = (serverExpiresAt && parseInt(serverExpiresAt, 10) > Date.now()) || serverHasPendingSeats;

            if (hasActiveBooking) {
                openCancelModal();
            } else if (selectedSeats.size > 0) {
                if (confirm("Bạn có chắc muốn hủy đặt vé và quay lại trang chi tiết phim không?")) {
                    selectedSeats.clear();
                    sessionStorage.removeItem(STORAGE_KEY);
                    sessionStorage.removeItem('resume_seats_showtime_' + showtimeId);
                    sessionStorage.removeItem('selectedCombos_showtime_' + showtimeId);
                    window.location.href = "{{ route('movies.show', $showtime->movie_id) }}";
                }
            } else {
                window.location.href = "{{ route('movies.show', $showtime->movie_id) }}";
            }
        }

        // Save selected seats to sessionStorage
        function saveSelectedSeats() {
            const ids = Array.from(selectedSeats);
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
        }

        function toggleSeat(seatId, button) {
            if (button.disabled || button.classList.contains('booked') || button.classList.contains('broken')) {
                return;
            }

            if (selectedSeats.has(seatId)) {
                selectedSeats.delete(seatId);
                button.classList.remove('selected');
            } else {
                if (selectedSeats.size >= MAX_TICKETS_PER_BOOKING) {
                    window.showToast('Bạn chỉ được đặt tối đa ' + MAX_TICKETS_PER_BOOKING + ' ghế cho mỗi đơn hàng.', 'error');
                    return;
                }

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
            const ALLOW_BOUNDARY_ORPHAN = @json(config('booking.seat_hold.allow_boundary_orphan_seat', false));
            let result = { isValid: true };
            const seatRows = document.querySelectorAll('.row-seats');
            
            for (let row of seatRows) {
                const seats = Array.from(row.children).filter(el => el.classList.contains('seat'));
                const totalSeats = seats.length;
                
                let emptyBlocks = [];
                let currentEmptyBlock = [];
                
                for (let i = 0; i < totalSeats; i++) {
                    const el = seats[i];
                    const isTaken = el.classList.contains('booked') || el.classList.contains('holding') || el.classList.contains('broken');
                    const isSelected = el.classList.contains('selected');
                    
                    if (!isTaken && !isSelected) {
                        currentEmptyBlock.push(i);
                    } else {
                        if (currentEmptyBlock.length > 0) {
                            emptyBlocks.push(currentEmptyBlock);
                            currentEmptyBlock = [];
                        }
                    }
                }
                
                if (currentEmptyBlock.length > 0) {
                    emptyBlocks.push(currentEmptyBlock);
                }
                
                for (let block of emptyBlocks) {
                    if (block.length === 1) {
                        const emptyIndex = block[0];
                        const isLeftSelected = (emptyIndex > 0) && seats[emptyIndex - 1].classList.contains('selected');
                        const isRightSelected = (emptyIndex < totalSeats - 1) && seats[emptyIndex + 1].classList.contains('selected');
                        
                        if (isLeftSelected || isRightSelected) {
                            const isAbsoluteStart = (emptyIndex === 0);
                            const isAbsoluteEnd = (emptyIndex === totalSeats - 1);
                            
                            if (isAbsoluteStart) {
                                if (!ALLOW_BOUNDARY_ORPHAN) {
                                    return {
                                        isValid: false,
                                        errorCode: 'SINGLE_SEAT_AT_START',
                                        message: 'Bạn không thể bỏ trống 1 ghế ở đầu dãy.'
                                    };
                                } else {
                                    result.bypassRule = 'BOUNDARY_EXCEPTION';
                                }
                            } else if (isAbsoluteEnd) {
                                if (!ALLOW_BOUNDARY_ORPHAN) {
                                    return {
                                        isValid: false,
                                        errorCode: 'SINGLE_SEAT_AT_END',
                                        message: 'Bạn không thể bỏ trống 1 ghế ở cuối dãy.'
                                    };
                                } else {
                                    result.bypassRule = 'BOUNDARY_EXCEPTION';
                                }
                            } else {
                                return { 
                                    isValid: false, 
                                    errorCode: 'SINGLE_SEAT_IN_MIDDLE', 
                                    message: 'Bạn không thể bỏ trống 1 ghế ở giữa.' 
                                };
                            }
                        }
                    }
                }
            }
            
            return result;
        }

        let isProceedingToCheckout = false;

        // Step 1: BFCache listener
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                console.log('Restored from BFCache, re-syncing seats...');
                pollBookedSeats();
            }
        });

        // Step 2: Silent CSRF & Session Retry Handler
        async function fetchWithCsrf(url, options = {}) {
            options.headers = options.headers || {};
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken && !options.headers['X-CSRF-TOKEN']) {
                options.headers['X-CSRF-TOKEN'] = csrfToken;
            }
            let response = await fetch(url, options);
            if (response.status === 419) {
                console.warn('419 CSRF Mismatch. Initializing session silently...');
                const initRes = await fetch('/api/init-session');
                const initData = await initRes.json();
                if (initData.csrf_token) {
                    document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', initData.csrf_token);
                    options.headers['X-CSRF-TOKEN'] = initData.csrf_token;
                    response = await fetch(url, options);
                }
            }
            return response;
        }

        async function ensureSelectedSeatsAvailable() {
            if (selectedSeats.size === 0) return true;

            try {
                const response = await fetch(`/api/booking/showtime/${showtimeId}/booked-seats`);
                const data = await response.json();
                const bookedIds = new Set(data?.bookedSeats || []);
                const conflictIds = Array.from(selectedSeats).filter(id => bookedIds.has(id));

                if (conflictIds.length === 0) {
                    return true;
                }

                conflictIds.forEach(id => {
                    const button = document.querySelector(`[data-seat-id="${id}"]`);
                    if (button) {
                        button.classList.add('booked');
                        button.classList.remove('selected');
                        button.disabled = true;
                        button.title = 'Ghế đã có người đặt hoặc đang được giữ';
                    }
                    selectedSeats.delete(id);
                });

                const conflictCodes = conflictIds.map(id => {
                    const button = document.querySelector(`[data-seat-id="${id}"]`);
                    return button ? button.getAttribute('data-seat-code') : `ghế ${id}`;
                }).join(', ');

                window.showToast(`Ghế ${conflictCodes} đã được khách chọn và đã có người đặt/giữ. Vui lòng chọn ghế khác.`, 'error');
                updateSummary();
                return false;
            } catch (error) {
                console.error('Seat availability check failed:', error);
                return true;
            }
        }

        async function proceedToCheckout(e) {
            if (e) { e.preventDefault(); e.stopPropagation(); }
            if (selectedSeats.size === 0) return;

            const validation = validateSeatSelection();
            if (!validation.isValid) {
                window.showToast(validation.message || "Ghế không hợp lệ.", 'error');
                return;
            }

            const isAvailable = await ensureSelectedSeatsAvailable();
            if (!isAvailable) {
                return;
            }

            isProceedingToCheckout = true;
            saveSelectedSeats();

            const seatIds = Array.from(selectedSeats).join(',');
            document.getElementById('form_seat_ids').value = seatIds;

            const combosKey = 'selectedCombos_showtime_' + showtimeId;
            const storedCombos = sessionStorage.getItem(combosKey);
            if (storedCombos) {
                document.getElementById('form_combos').value = storedCombos;
            }

            const btn = document.getElementById('checkoutButton');
            if (btn) btn.disabled = true;

            document.getElementById('seat-selection-form').submit();
        }

        // --- Xử lý thông báo lỗi từ session ---
        @if(session('error'))
            window.showToast("{{ session('error') }}", 'error');
        @endif
        @if(session('success'))
            window.showToast("{{ session('success') }}", 'success');
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
                console.assert(resA1.isValid === false && resA1.errorCode === 'SINGLE_SEAT_AT_START', "Test A1 Failed");
                if (!resA1.isValid) console.log("Scenario A1 (Head Blocked) Passed");

                // Scenario A2 (Boundary Right): 5 seats [1,2,3,4,5], user selects [1,2,3,4] -> indices [0,1,2,3]
                mockRows = [createMockRow(5, [0, 1, 2, 3])];
                let resA2 = validateSeatSelection();
                console.assert(resA2.isValid === false && resA2.errorCode === 'SINGLE_SEAT_AT_END', "Test A2 Failed");
                if (!resA2.isValid) console.log("Scenario A2 (End Blocked) Passed");

                // Scenario B1 (Middle Gap): 5 seats [1,2,3,4,5], user selects [1,2,4,5] -> indices [0,1,3,4], empty [2]
                mockRows = [createMockRow(5, [0, 1, 3, 4])];
                let resB1 = validateSeatSelection();
                console.assert(resB1.isValid === false && resB1.errorCode === 'SINGLE_SEAT_IN_MIDDLE', "Test B1 Failed");
                if (!resB1.isValid) console.log("Scenario B1 (Middle Blocked) Passed");
                
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
                    if (data && (data.bookedSeats !== undefined || data.myPendingSeats !== undefined)) {
                        const bookedSeatIds = data.bookedSeats || [];
                        const myPendingSeatIds = data.myPendingSeats || [];
                        const conflictSeatCodes = [];
                        
                        document.querySelectorAll('.seat').forEach(button => {
                            const seatId = parseInt(button.getAttribute('data-seat-id'));
                            if (isNaN(seatId)) return;

                            const isCurrentlyBooked = button.classList.contains('booked');
                            const shouldBeBooked = bookedSeatIds.includes(seatId);
                            const isMyPendingOnServer = myPendingSeatIds.includes(seatId);

                            if (shouldBeBooked) {
                                if (!isCurrentlyBooked) {
                                    button.classList.add('booked');
                                    button.classList.remove('selected');
                                    button.disabled = true;
                                    button.title = "Ghế đã có người đặt hoặc đang được giữ";
                                }

                                if (selectedSeats.has(seatId) && !isMyPendingOnServer) {
                                    selectedSeats.delete(seatId);
                                    button.classList.remove('selected');
                                    button.classList.add('booked');
                                    button.disabled = true;
                                    const seatCode = button.getAttribute('data-seat-code') || seatId;
                                    conflictSeatCodes.push(seatCode);

                                    button.classList.add('seat-conflict-flash');
                                    setTimeout(() => {
                                        button.classList.remove('seat-conflict-flash');
                                    }, 1500);
                                }
                            } else if (!shouldBeBooked) {
                                if (isCurrentlyBooked) {
                                    button.classList.remove('booked', 'seat-conflict-flash');
                                    button.disabled = false;
                                    button.title = button.getAttribute('data-seat-code');
                                    const seatType = button.getAttribute('data-seat-type');
                                    if (seatType === 'VIP') {
                                        button.classList.add('vip');
                                    } else if (seatType === 'Sweetbox' || seatType === 'Double') {
                                        button.classList.add('sweetbox');
                                    } else {
                                        button.classList.add('regular');
                                    }
                                }

                                // Tự động xóa tích xanh (.selected) nếu vé đã hủy (Server báo không còn myPendingSeats và User không bấm chọn tay)
                                if (button.classList.contains('selected') && !isMyPendingOnServer && !selectedSeats.has(seatId)) {
                                    button.classList.remove('selected');
                                }
                            }
                        });

                        if (conflictSeatCodes.length > 0) {
                            const seatListStr = conflictSeatCodes.join(', ');
                            window.showToast(`⚠️ Ghế ${seatListStr} đã được người khác đặt/giữ. Hệ thống đã tự động bỏ chọn các ghế này, vui lòng chọn ghế khác.`, 'error');
                        }

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
                const resumeKey = 'resume_seats_showtime_' + showtimeId;
                const isResuming = sessionStorage.getItem(resumeKey) === '1' || new URLSearchParams(window.location.search).has('resume_seats');
                
                if (stored && (isResuming || selectedSeats.size > 0)) {
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

                    fetch("{{ route('api.booking.cancel-explicit') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ showtime_id: showtimeId })
                    }).catch(err => console.error("Auto-cancel expired hold failed:", err));
                }
            }

            tick();
            setInterval(tick, 1000);
        }

        // --- Xử lý Explicit Cancel ---
        function confirmCancelBooking() {
            const btn = document.getElementById('btnConfirmCancel');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang hủy...';
            
            fetch("{{ route('api.booking.cancel-explicit') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    showtime_id: showtimeId
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    sessionStorage.removeItem(STORAGE_KEY);
                    sessionStorage.removeItem('booking_expires_at');
                    sessionStorage.removeItem('resume_seats_showtime_' + showtimeId);
                    sessionStorage.removeItem('selectedCombos_showtime_' + showtimeId);
                    window.location.href = data.redirect_url || "{{ route('movies.show', $showtime->movie_id) }}";
                } else {
                    window.showToast(data.error || "Có lỗi xảy ra khi hủy vé.", 'error');
                    closeCancelModal();
                    btn.disabled = false;
                    btn.innerHTML = 'Hủy đặt vé';
                }
            })
            .catch(err => {
                console.error(err);
                window.showToast("Lỗi kết nối.", 'error');
                document.getElementById('cancelModal').style.display='none';
                btn.disabled = false;
                btn.innerHTML = 'Hủy đặt vé';
            });
        }

        function cancelAndStartOver() {
            const btn = document.getElementById('btnStartOver');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            
            fetch("{{ route('api.booking.cancel-explicit') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    showtime_id: showtimeId
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    sessionStorage.removeItem(STORAGE_KEY);
                    sessionStorage.removeItem('booking_expires_at');
                    window.location.reload();
                } else {
                    window.showToast(data.error || "Có lỗi xảy ra khi hủy vé.", 'error');
                    document.getElementById('resumeBookingModal').style.display='none';
                    btn.disabled = false;
                    btn.innerHTML = 'Đặt lại từ đầu';
                }
            })
            .catch(err => {
                console.error(err);
                window.showToast("Lỗi kết nối.", 'error');
                document.getElementById('resumeBookingModal').style.display='none';
                btn.disabled = false;
                btn.innerHTML = 'Đặt lại từ đầu';
            });
        }

        // Restore seats on page load, then start listening via WebSockets
        document.addEventListener('DOMContentLoaded', () => {
            restoreSelectedSeats();
            initSelectSeatsTimer();
            pollBookedSeats(); // Initial fetch to ensure up-to-date state

            const currentUserId = {{ auth()->id() ?? 0 }};

            if (typeof window.Echo !== 'undefined') {
                console.log('Connecting to Showtime Channel: showtime.' + showtimeId);
                
                // Public Channel (Always available for all clients/guests)
                window.Echo.channel(`showtime.${showtimeId}`)
                    .listen('.SeatStatusUpdated', (e) => {
                        console.log('SeatStatusUpdated received (public):', e);
                        handleRealtimeSeatUpdate(e);
                    })
                    .listen('SeatStatusUpdated', (e) => {
                        console.log('SeatStatusUpdated received (public):', e);
                        handleRealtimeSeatUpdate(e);
                    });

                // Presence Channel (User tracking)
                window.Echo.join(`showtime.${showtimeId}`)
                    .here((users) => {
                        console.log('Users currently viewing this showtime:', users);
                    })
                    .joining((user) => {
                        console.log('User joined seat map:', user.name);
                    })
                    .leaving((user) => {
                        console.log('User left seat map:', user.name);
                    })
                    .listen('.SeatStatusUpdated', (e) => {
                        console.log('SeatStatusUpdated received (presence):', e);
                        handleRealtimeSeatUpdate(e);
                    })
                    .listen('SeatStatusUpdated', (e) => {
                        console.log('SeatStatusUpdated received (presence):', e);
                        handleRealtimeSeatUpdate(e);
                    });

                // Auto Re-sync on reconnection
                if (window.Echo.connector && window.Echo.connector.pusher) {
                    window.Echo.connector.pusher.connection.bind('state_change', (states) => {
                        console.log('WebSocket state changed:', states.current);
                        if (states.current === 'connected') {
                            pollBookedSeats();
                        }
                    });
                }
            } else {
                console.warn("Laravel Echo is not initialized. Falling back to polling.");
            }

            // Always run background polling every 5 seconds as a safety net
            setInterval(pollBookedSeats, 5000);
        });

        function handleRealtimeSeatUpdate(e) {
            if (!e || !e.seatIds) return;
            const currentUserId = {{ auth()->id() ?? 0 }};
            const isOtherUser = !e.userId || !currentUserId || parseInt(e.userId) !== parseInt(currentUserId);
            const statusUpper = e.status ? String(e.status).toUpperCase() : '';
            const isLockedStatus = ['PAID', 'PENDING', 'HOLD', 'BOOKED'].includes(statusUpper);

            const conflictSeatCodes = [];

            e.seatIds.forEach(seatId => {
                const button = document.querySelector(`.seat[data-seat-id="${seatId}"]`);
                if (!button) return;

                if (isLockedStatus) {
                    const wasSelected = selectedSeats.has(seatId);
                    
                    if (wasSelected && isOtherUser) {
                        selectedSeats.delete(seatId);
                        const seatCode = button.getAttribute('data-seat-code') || seatId;
                        conflictSeatCodes.push(seatCode);

                        button.classList.add('seat-conflict-flash');
                        setTimeout(() => {
                            button.classList.remove('seat-conflict-flash');
                        }, 1500);
                    }

                    if (isOtherUser || !wasSelected) {
                        button.classList.add('booked');
                        button.classList.remove('selected');
                        button.disabled = true;
                        button.title = statusUpper === 'PAID' ? "Ghế đã bán" : "Ghế đang được người khác giữ";
                    }
                } else if (statusUpper === 'AVAILABLE') {
                    button.classList.remove('booked', 'seat-conflict-flash');
                    button.disabled = false;
                    button.title = button.getAttribute('data-seat-code');
                    const seatType = button.getAttribute('data-seat-type');
                    if (seatType === 'VIP') {
                        button.classList.add('vip');
                    } else if (seatType === 'Sweetbox' || seatType === 'Double') {
                        button.classList.add('sweetbox');
                    } else {
                        button.classList.add('regular');
                    }
                }
            });

            if (conflictSeatCodes.length > 0) {
                const seatListStr = conflictSeatCodes.join(', ');
                window.showToast(`⚠️ Ghế ${seatListStr} đã được người khác đặt/giữ. Hệ thống đã tự động bỏ chọn các ghế này, vui lòng chọn ghế khác.`, 'error');
            }

            updateSummary();
        }

        // Sync fresh seat state if user returns via browser Back button (BFCache)
        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                pollBookedSeats();
            }
        });
    </script>
@endpush
