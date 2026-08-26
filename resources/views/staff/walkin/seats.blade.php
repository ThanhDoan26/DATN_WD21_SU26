@extends('layouts.staff')

@section('page_title', 'Tạo Vé Tại Quầy - Sơ Đồ Ghế')

@section('extra_css')
<style>
    .pos-seat-container {
        background: var(--bg-surface);
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        border: 1px solid var(--border-light);
    }

    .cinema-screen-curved {
        width: 80%;
        max-width: 550px;
        margin: 0 auto 35px;
        padding: 12px 0;
        text-align: center;
        background: linear-gradient(180deg, rgba(245, 158, 11, 0.15) 0%, rgba(245, 158, 11, 0.02) 100%);
        border-top: 4px solid #f59e0b;
        border-radius: 8px 8px 60px 60px;
        font-family: 'Sora', sans-serif;
        font-weight: 800;
        letter-spacing: 6px;
        color: #d97706;
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.15);
    }

    .seat-row {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 10px;
        gap: 8px;
    }

    .row-label {
        width: 30px;
        text-align: center;
        font-family: 'Sora', sans-serif;
        font-weight: 700;
        color: var(--text-muted);
        font-size: 0.85rem;
    }

    .seat-btn {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Sora', sans-serif;
        font-size: 0.8rem;
        font-weight: 700;
        border-radius: 8px;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        user-select: none;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .seat-btn:hover:not(.seat-booked) {
        transform: translateY(-3px) scale(1.08);
        box-shadow: 0 5px 12px rgba(0,0,0,0.15);
    }

    .seat-btn:active:not(.seat-booked) {
        transform: scale(0.92);
    }

    .seat-regular {
        background-color: #38bdf8;
        color: #ffffff;
        border-color: #0284c7;
    }

    .seat-vip {
        background-color: #f59e0b;
        color: #ffffff;
        border-color: #d97706;
    }

    .seat-sweetbox {
        background-color: #ec4899;
        color: #ffffff;
        border-color: #db2777;
        width: 84px; /* Double seat width */
    }

    .seat-booked {
        background-color: #94a3b8 !important;
        color: #e2e8f0 !important;
        cursor: not-allowed !important;
        border-color: #64748b !important;
        opacity: 0.5;
        box-shadow: none !important;
    }

    .seat-selected {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        color: #ffffff !important;
        border-color: #047857 !important;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.3) !important;
        transform: scale(1.08);
    }

    .legend-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        background: var(--bg-base);
        border: 1px solid var(--border-light);
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    /* POS Terminal Right Sidebar Card */
    .pos-terminal-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-light);
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.06);
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-0">
    <!-- Action Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 bg-surface p-3 rounded-4 shadow-sm border border-light">
        <div class="d-flex align-items-center gap-3">
            <a href="javascript:history.back()" class="btn btn-outline-secondary rounded-pill px-4 fw-bold shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Trở Lại Suất Chiếu
            </a>
            <div>
                <h4 class="mb-0 fw-extrabold text-ink font-sora"><i class="fas fa-th text-amber me-2"></i>Sơ Đồ Ghế - POS Counter</h4>
                <small class="text-muted">Chọn ghế theo yêu cầu của khách hàng tại quầy</small>
            </div>
        </div>
        <span class="badge bg-amber text-dark px-3 py-2 rounded-pill fw-bold">
            <i class="fas fa-door-open me-1"></i> {{ $showtime->room->name }}
        </span>
    </div>

    <div class="row g-4">
        <!-- Seat Map Interactive Area -->
        <div class="col-lg-8">
            <div class="pos-seat-container text-center">
                <!-- Curved Screen Bar -->
                <div class="cinema-screen-curved mb-4">
                    <i class="fas fa-tv me-2"></i>MÀN CHIẾU / SCREEN
                </div>

                <!-- Seat Matrix -->
                <div class="d-inline-block text-start overflow-auto w-100 pb-3">
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
                                     title="{{ $isBooked ? 'Ghế đã bán' : $seat->getSeatCode() }}">
                                    {{ $seat->seat_number }}
                                </div>
                            @endforeach
                            <span class="row-label">{{ $row }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Legend Bar -->
                <div class="d-flex flex-wrap justify-content-center gap-3 mt-4 pt-3 border-top border-light text-ink">
                    <div class="legend-chip"><span class="seat-btn seat-regular" style="width:22px;height:22px;pointer-events:none;"></span> Thường</div>
                    <div class="legend-chip"><span class="seat-btn seat-vip" style="width:22px;height:22px;pointer-events:none;"></span> VIP</div>
                    <div class="legend-chip"><span class="seat-btn seat-sweetbox" style="width:36px;height:22px;pointer-events:none;"></span> Đôi (Sweetbox)</div>
                    <div class="legend-chip"><span class="seat-btn seat-selected" style="width:22px;height:22px;pointer-events:none;"></span> Đang chọn</div>
                    <div class="legend-chip"><span class="seat-btn seat-booked" style="width:22px;height:22px;pointer-events:none;"></span> Đã bán / Đang giữ</div>
                </div>
            </div>
        </div>

        <!-- POS Terminal Right Sidebar Card -->
        <div class="col-lg-4">
            <div class="pos-terminal-card p-4">
                <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom border-light">
                    <h5 class="fw-extrabold font-sora text-ink mb-0"><i class="fas fa-shopping-cart text-amber me-2"></i>Giỏ Hàng POS</h5>
                    <span class="badge bg-light text-muted border px-2 py-1 small">Quầy Bán Vé</span>
                </div>

                <!-- Movie & Showtime Info -->
                <div class="mb-3 p-3 rounded-3 bg-light border border-light">
                    <div class="fw-bold text-ink mb-1 fs-6">{{ $showtime->movie->title }}</div>
                    <div class="small text-muted mb-1"><i class="fas fa-door-open me-1 text-amber"></i> {{ $showtime->room->name }}</div>
                    <div class="small text-muted"><i class="fas fa-clock me-1 text-amber"></i> {{ $showtime->start_time->format('H:i') }} - {{ $showtime->start_time->format('d/m/Y') }}</div>
                </div>

                <!-- Selected Seats Tags List -->
                <label class="form-label font-sora fw-bold small text-muted">DANH SÁCH GHẾ ĐÃ CHỌN:</label>
                <div id="selectedSeatsChips" class="mb-3 p-3 rounded-3 border border-light text-center" style="min-height: 70px; background: var(--bg-base);">
                    <span class="text-muted small"><i class="fas fa-mouse-pointer me-1"></i>Chưa chọn ghế nào</span>
                </div>

                <!-- Price Summary -->
                <div class="space-y-2 mb-4">
                    <div class="d-flex justify-content-between align-items-center text-muted small">
                        <span>Giá vé cơ bản:</span>
                        <span id="subtotalDisplay" class="fw-bold text-ink">0 ₫</span>
                    </div>
                    @if(($showtime->surcharge ?? 0) > 0)
                        <div class="d-flex justify-content-between align-items-center text-muted small">
                            <span>Phụ thu suất chiếu:</span>
                            <span class="fw-bold text-amber">+{{ number_format($showtime->surcharge) }} ₫</span>
                        </div>
                    @endif
                    <hr class="my-2 border-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-extrabold text-ink font-sora fs-6">TỔNG THÀNH TIỀN:</span>
                        <span id="totalPriceDisplay" class="fw-extrabold text-danger fs-3 font-sora">0 ₫</span>
                    </div>
                </div>

                <!-- Submit Action Button -->
                <button type="button" class="btn btn-amber w-100 py-3 fw-extrabold font-sora fs-5 rounded-3 shadow-lg" id="btnContinue" disabled onclick="proceedToCheckout()">
                    XÁC NHẬN VÀ CHUYỂN BƯỚC <i class="fas fa-arrow-right ms-2"></i>
                </button>
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
    
    function updateCart() {
        const btnContinue = document.getElementById('btnContinue');
        const chipsDiv = document.getElementById('selectedSeatsChips');
        const subtotalDiv = document.getElementById('subtotalDisplay');
        const totalDiv = document.getElementById('totalPriceDisplay');
        
        if (selectedSeats.size === 0) {
            btnContinue.disabled = true;
            chipsDiv.innerHTML = '<span class="text-muted small"><i class="fas fa-mouse-pointer me-1"></i>Chưa chọn ghế nào</span>';
            subtotalDiv.textContent = '0 ₫';
            totalDiv.textContent = '0 ₫';
            return;
        }
        
        btnContinue.disabled = false;
        
        let total = 0;
        let chipsHtml = '';
        
        selectedSeats.forEach(id => {
            const el = document.querySelector(`[data-id="${id}"]`);
            if (el) {
                const code = el.dataset.code;
                const type = el.dataset.type;
                const basePrice = ticketPrices[type] || 0;
                total += basePrice + surcharge;

                chipsHtml += `<span class="badge bg-amber text-dark font-sora fw-bold px-2 py-1 me-1 mb-1 shadow-sm">${code} (${type})</span>`;
            }
        });
        
        chipsDiv.innerHTML = chipsHtml;
        subtotalDiv.textContent = new Intl.NumberFormat('vi-VN').format(total) + ' ₫';
        totalDiv.textContent = new Intl.NumberFormat('vi-VN').format(total) + ' ₫';
    }
    
    function proceedToCheckout() {
        if (selectedSeats.size === 0) return;
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
                    btn.title = "Ghế đang được giữ hoặc đã bán online";
                    if (selectedSeats.has(seatId)) {
                        selectedSeats.delete(seatId);
                    }
                }
            } else if (e.status === 'AVAILABLE') {
                btn.classList.remove('seat-booked');
                btn.title = btn.dataset.code;
            }
        });

        updateCart();
    }
</script>

<style>
    .btn-amber {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #ffffff;
        border: none;
    }
    .btn-amber:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        color: #ffffff;
    }
</style>
@endsection
