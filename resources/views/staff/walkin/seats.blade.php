@extends('layouts.staff')

@section('title', 'Chọn Ghế - ' . ($showtime->movie->title ?? 'Walk-in Booking'))
@section('page_title', 'Chọn Ghế (POS)')

@section('extra_css')
<style>
    .pos-seat-container {
        background: var(--bg-surface, #ffffff);
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.06);
        border: 1px solid var(--border-light, #e2e8f0);
    }
    
    /* Màn chiếu Cinema 3D phát sáng */
    .cinema-screen-3d {
        width: 80%;
        max-width: 620px;
        margin: 10px auto 40px auto;
        padding: 12px 0;
        text-align: center;
        background: linear-gradient(180deg, rgba(245, 158, 11, 0.15) 0%, rgba(245, 158, 11, 0.02) 100%);
        border-top: 5px solid #f59e0b;
        border-radius: 12px 12px 120px 120px;
        font-family: 'Sora', sans-serif;
        font-weight: 800;
        font-size: 13px;
        letter-spacing: 8px;
        color: #f59e0b;
        box-shadow: 0 15px 30px -10px rgba(245, 158, 11, 0.25);
        position: relative;
    }
    .cinema-screen-3d::after {
        content: '';
        position: absolute;
        bottom: -20px;
        left: 10%;
        right: 10%;
        height: 20px;
        background: radial-gradient(ellipse at center, rgba(245, 158, 11, 0.2) 0%, transparent 70%);
        filter: blur(8px);
    }

    .seat-map-wrapper {
        overflow-x: auto;
        padding: 10px 0 20px 0;
    }
    .seat-row {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 9px;
        gap: 7px;
    }
    .row-label {
        width: 28px;
        text-align: center;
        font-weight: 800;
        color: #94a3b8;
        font-size: 13px;
        user-select: none;
    }

    /* Kiểu dáng ghế chuẩn POS Cinema */
    .seat-btn {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 800;
        border-radius: 8px;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        user-select: none;
        position: relative;
    }
    .seat-btn:hover:not(.seat-booked) {
        transform: translateY(-3px) scale(1.08);
        box-shadow: 0 6px 14px rgba(0,0,0,0.15);
        z-index: 2;
    }
    .seat-btn:active:not(.seat-booked) {
        transform: scale(0.95);
    }

    .seat-regular { 
        background: #0ea5e9; 
        color: #ffffff; 
        border-color: #0284c7; 
    }
    .seat-vip { 
        background: #f59e0b; 
        color: #000000; 
        border-color: #d97706; 
    }
    .seat-sweetbox { 
        background: #ec4899; 
        color: #ffffff; 
        border-color: #db2777; 
        width: 79px; 
    }
    .seat-booked { 
        background: #e2e8f0 !important; 
        color: #94a3b8 !important; 
        cursor: not-allowed !important; 
        border-color: #cbd5e1 !important; 
        opacity: 0.65;
    }
    .dark-theme .seat-booked {
        background: #334155 !important;
        color: #64748b !important;
        border-color: #475569 !important;
    }
    .seat-selected { 
        background: #10b981 !important; 
        color: #ffffff !important; 
        border-color: #059669 !important; 
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.4), 0 6px 16px rgba(16, 185, 129, 0.3) !important;
        transform: translateY(-2px) scale(1.05);
        z-index: 3;
    }

    /* POS Cart Sidebar */
    .pos-cart-panel {
        background: var(--bg-surface, #ffffff);
        border-radius: 20px;
        border: 1px solid var(--border-light, #e2e8f0);
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08);
        position: sticky;
        top: 20px;
    }
    .pos-cart-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #ffffff;
        padding: 16px 20px;
    }
    .selected-seat-chip {
        background: rgba(16, 185, 129, 0.12);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.25);
        font-size: 13px;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-4">
    <!-- Header Navigation -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <a href="javascript:history.back()" class="btn btn-outline-secondary px-3 py-2 rounded-3 fw-bold">
            <i class="fas fa-arrow-left me-2"></i>Đổi Suất Chiếu
        </a>
        <div class="d-flex align-items-center gap-2 text-muted small">
            <span class="badge bg-warning text-dark px-3 py-2 rounded-3 fw-bold">
                <i class="fas fa-door-open me-1"></i> {{ $showtime->room->name }} ({{ $showtime->room->format }})
            </span>
            <span class="badge bg-secondary px-3 py-2 rounded-3">
                <i class="fas fa-clock me-1"></i> {{ $showtime->start_time->format('H:i') }} - {{ $showtime->end_time ? $showtime->end_time->format('H:i') : '' }}
            </span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Seat Map Area -->
        <div class="col-xl-8 col-lg-7">
            <div class="pos-seat-container">
                <!-- Screen -->
                <div class="cinema-screen-3d">
                    <i class="fas fa-tv me-2 opacity-75"></i> MÀN HÌNH CHIẾU
                </div>

                <!-- Seat Grid -->
                <div class="seat-map-wrapper text-center">
                    <div class="d-inline-block text-start">
                        @php
                            $groupedSeats = $room->seats->groupBy('row_name')->sortKeys();
                        @endphp

                        @foreach($groupedSeats as $row => $seats)
                            <div class="seat-row">
                                <span class="row-label">{{ $row }}</span>
                                @foreach($seats->sortBy(fn($s) => (int)$s->seat_number) as $seat)
                                    @php
                                        $isBooked = in_array($seat->id, $bookedSeats);
                                        $isVip = $seat->seat_type === 'VIP';
                                        $isSweetbox = $seat->seat_type === 'Sweetbox' || $seat->seat_type === 'Double';
                                        
                                        if ($isBooked) {
                                            $seatClass = 'seat-booked';
                                        } elseif ($isSweetbox) {
                                            $seatClass = 'seat-sweetbox';
                                        } elseif ($isVip) {
                                            $seatClass = 'seat-vip';
                                        } else {
                                            $seatClass = 'seat-regular';
                                        }
                                    @endphp
                                    <div onclick="toggleSeat({{ $seat->id }}, this)" 
                                         class="seat-btn {{ $seatClass }}" 
                                         data-id="{{ $seat->id }}" 
                                         data-code="{{ $seat->getSeatCode() }}" 
                                         data-type="{{ $seat->seat_type }}"
                                         title="Ghế {{ $seat->getSeatCode() }} ({{ $seat->seat_type }})">
                                        {{ $seat->seat_number }}
                                    </div>
                                @endforeach
                                <span class="row-label">{{ $row }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Legend & Tools -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pt-3 border-top mt-3">
                    <div class="d-flex flex-wrap align-items-center gap-4 text-muted small">
                        <div class="d-flex align-items-center"><div class="seat-btn seat-regular me-2" style="width:24px;height:24px"></div> Thường</div>
                        <div class="d-flex align-items-center"><div class="seat-btn seat-vip me-2" style="width:24px;height:24px"></div> VIP</div>
                        <div class="d-flex align-items-center"><div class="seat-btn seat-sweetbox me-2" style="width:36px;height:24px"></div> Đôi (Sweetbox)</div>
                        <div class="d-flex align-items-center"><div class="seat-btn seat-selected me-2" style="width:24px;height:24px"></div> Đang chọn</div>
                        <div class="d-flex align-items-center"><div class="seat-btn seat-booked me-2" style="width:24px;height:24px"></div> Đã bán</div>
                    </div>

                    <button type="button" class="btn btn-outline-danger btn-sm rounded-3 fw-bold" onclick="clearAllSelectedSeats()">
                        <i class="fas fa-trash-alt me-1"></i> Bỏ chọn tất cả
                    </button>
                </div>
            </div>
        </div>

        <!-- POS Sidebar -->
        <div class="col-xl-4 col-lg-5">
            <div class="pos-cart-panel">
                <div class="pos-cart-header">
                    <h5 class="fw-bold mb-1 font-sora d-flex align-items-center justify-content-between">
                        <span><i class="fas fa-receipt me-2 text-warning"></i>Thông Tin Đơn Vé</span>
                        <span class="badge bg-warning text-dark fs-6">{{ $showtime->room->format }}</span>
                    </h5>
                    <p class="mb-0 text-slate-300 small opacity-80">{{ $showtime->room->cinema->name ?? 'Beta Cinemas' }}</p>
                </div>

                <div class="p-4">
                    <!-- Movie Details -->
                    <div class="mb-3 pb-3 border-bottom">
                        <span class="text-muted small text-uppercase fw-bold">Phim đang chọn</span>
                        <h6 class="fw-bold text-dark font-sora mt-1 mb-1">{{ $showtime->movie->title }}</h6>
                        <div class="text-muted small">
                            <i class="fas fa-calendar-alt text-primary me-1"></i> {{ $showtime->start_time->format('H:i - d/m/Y') }} &bull; {{ $showtime->room->name }}
                        </div>
                    </div>

                    <!-- Selected Seats -->
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small text-uppercase fw-bold">Ghế đã chọn</span>
                            <span id="seatCountBadge" class="badge bg-success text-white">0 ghế</span>
                        </div>
                        
                        <div id="selectedSeatsList" class="d-flex flex-wrap gap-2 min-h-40">
                            <div class="text-center w-100 py-3 text-muted small fst-italic">
                                Vui lòng click chọn ghế trên sơ đồ
                            </div>
                        </div>
                    </div>

                    <!-- Price Breakdown -->
                    <div class="mb-4">
                        @if($showtime->surcharge > 0)
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span>Phụ thu suất chiếu:</span>
                            <span>+{{ number_format($showtime->surcharge) }}₫ / ghế</span>
                        </div>
                        @endif
                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <span class="fs-5 fw-bold text-dark">Tạm Tính:</span>
                            <span class="fs-3 fw-bold text-danger font-sora" id="totalPriceDisplay">0₫</span>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <button class="btn btn-success w-100 py-3 fw-bold fs-5 rounded-3 shadow" id="btnContinue" disabled onclick="proceedToCheckout()">
                        <span>Tiếp Tục Thanh Toán</span>
                        <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_js')
<script>
    const showtimeId = {{ $showtime->id }};
    const surcharge = {{ $showtime->surcharge ?? 0 }};
    const ticketPrices = @json($ticketPrices->mapWithKeys(fn($price) => [$price->seat_type => (float) $price->price]));
    const currentStaffId = {{ auth()->id() ?? 0 }};
    
    let selectedSeats = new Set();
    
    function toggleSeat(seatId, el) {
        if (el.classList.contains('seat-booked')) return;
        
        if (selectedSeats.has(seatId)) {
            selectedSeats.delete(seatId);
            el.classList.remove('seat-selected');
        } else {
            selectedSeats.add(seatId);
            el.classList.add('seat-selected');
        }
        
        updateCart();
    }

    function clearAllSelectedSeats() {
        selectedSeats.forEach(id => {
            const el = document.querySelector(`[data-id="${id}"]`);
            if (el) el.classList.remove('seat-selected');
        });
        selectedSeats.clear();
        updateCart();
    }
    
    function updateCart() {
        const btnContinue = document.getElementById('btnContinue');
        const listDiv = document.getElementById('selectedSeatsList');
        const totalDiv = document.getElementById('totalPriceDisplay');
        const countBadge = document.getElementById('seatCountBadge');
        
        if (selectedSeats.size === 0) {
            btnContinue.disabled = true;
            listDiv.innerHTML = `<div class="text-center w-100 py-3 text-muted small fst-italic">Vui lòng click chọn ghế trên sơ đồ</div>`;
            totalDiv.textContent = '0₫';
            countBadge.textContent = '0 ghế';
            countBadge.className = 'badge bg-secondary';
            return;
        }
        
        btnContinue.disabled = false;
        countBadge.textContent = `${selectedSeats.size} ghế`;
        countBadge.className = 'badge bg-success';
        
        let total = 0;
        let chipsHtml = '';
        
        selectedSeats.forEach(id => {
            const el = document.querySelector(`[data-id="${id}"]`);
            if (el) {
                const code = el.dataset.code;
                const type = el.dataset.type;
                const basePrice = ticketPrices[type] || 0;
                const itemTotal = basePrice + surcharge;
                total += itemTotal;
                
                chipsHtml += `
                    <div class="selected-seat-chip">
                        <span>${code} (${type})</span>
                        <span class="text-dark small">${new Intl.NumberFormat('vi-VN').format(itemTotal)}₫</span>
                    </div>
                `;
            }
        });
        
        listDiv.innerHTML = chipsHtml;
        totalDiv.textContent = new Intl.NumberFormat('vi-VN').format(total) + '₫';
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
                const button = document.querySelector(`[data-id="${id}"]`);
                if (button) {
                    button.classList.add('seat-booked');
                    button.classList.remove('seat-selected');
                    button.disabled = true;
                }
                selectedSeats.delete(id);
            });

            const conflictCodes = conflictIds.map(id => {
                const button = document.querySelector(`[data-id="${id}"]`);
                return button ? button.dataset.code : `ghế ${id}`;
            }).join(', ');

            alert(`Ghế ${conflictCodes} đã được khách khác đặt hoặc đang giữ. Vui lòng chọn ghế khác.`);
            updateCart();
            return false;
        } catch (error) {
            console.error('Seat availability check failed:', error);
            return true;
        }
    }

    async function proceedToCheckout() {
        if(selectedSeats.size === 0) return;

        const isAvailable = await ensureSelectedSeatsAvailable();
        if (!isAvailable) {
            return;
        }

        const seatIds = Array.from(selectedSeats).join(',');
        window.location.href = `/staff/walk-in/checkout?showtime_id=${showtimeId}&seat_ids=${seatIds}`;
    }

    // --- POS Realtime Sync with Online Bookings ---
    function fetchFreshSeatState() {
        fetch(`/api/booking/showtime/${showtimeId}/booked-seats`)
            .then(res => res.json())
            .then(data => {
                if (data && data.bookedSeats) {
                    const bookedIds = data.bookedSeats || [];
                    document.querySelectorAll('.seat-btn').forEach(btn => {
                        const sid = parseInt(btn.getAttribute('data-id'));
                        if (isNaN(sid)) return;
                        if (bookedIds.includes(sid)) {
                            btn.classList.add('seat-booked');
                            btn.classList.remove('seat-selected');
                            if (selectedSeats.has(sid)) {
                                selectedSeats.delete(sid);
                            }
                        } else if (!btn.classList.contains('seat-selected')) {
                            btn.classList.remove('seat-booked');
                        }
                    });
                    updateCart();
                }
            })
            .catch(err => console.error('Error syncing POS seats:', err));
    }

    @if(session('error'))
        alert("{{ session('error') }}");
    @endif

    document.addEventListener('DOMContentLoaded', () => {
        fetchFreshSeatState();

        if (typeof window.Echo !== 'undefined') {
            window.Echo.join(`showtime.${showtimeId}`)
                .listen('.SeatStatusUpdated', handlePosSeatUpdate)
                .listen('SeatStatusUpdated', handlePosSeatUpdate);

            if (window.Echo.connector && window.Echo.connector.pusher) {
                window.Echo.connector.pusher.connection.bind('state_change', (states) => {
                    if (states.current === 'connected') {
                        fetchFreshSeatState();
                    }
                });
            }
        } else {
            setInterval(fetchFreshSeatState, 5000);
        }
    });

    function handlePosSeatUpdate(e) {
        if (!e || !e.seatIds) return;
        const isMe = e.userId && parseInt(e.userId) === currentStaffId;

        e.seatIds.forEach(seatId => {
            const btn = document.querySelector(`[data-id="${seatId}"]`);
            if (!btn) return;

            if (e.status === 'PAID' || e.status === 'PENDING' || e.status === 'HOLD') {
                if (!isMe) {
                    btn.classList.add('seat-booked');
                    btn.classList.remove('seat-selected');
                    if (selectedSeats.has(seatId)) {
                        selectedSeats.delete(seatId);
                    }
                }
            } else if (e.status === 'AVAILABLE') {
                btn.classList.remove('seat-booked');
            }
        });

        updateCart();
    }
</script>
@endsection
