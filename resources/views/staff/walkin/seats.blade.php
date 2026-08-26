@extends('layouts.staff')

@section('title', 'Chọn Ghế - ' . ($showtime->movie->title ?? 'Walk-in Booking'))
@section('page_title', 'Chọn Ghế')

@section('extra_css')
<style>
    .pos-seat-map {
        background-color: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        box-shadow: inset 0 2px 10px rgba(0,0,0,0.05);
        overflow-x: auto;
    }
    .cinema-screen {
        width: 100%;
        max-width: 500px;
        margin: 0 auto 30px;
        padding: 10px 0;
        text-align: center;
        background: linear-gradient(180deg, #e9ecef 0%, #f8f9fa 100%);
        border-top: 5px solid #0dcaf0;
        border-radius: 4px 4px 50px 50px;
        font-weight: bold;
        letter-spacing: 5px;
        color: #6c757d;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .seat-row {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 8px;
        gap: 6px;
    }
    .row-label {
        width: 25px;
        text-align: center;
        font-weight: bold;
        color: #adb5bd;
    }
    .seat-btn {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: bold;
        border-radius: 6px;
        cursor: pointer;
        border: 2px solid transparent;
        transition: transform 0.1s;
        user-select: none;
    }
    .seat-btn:active:not(:disabled) {
        transform: scale(0.95);
    }
    .seat-regular { background-color: #0dcaf0; color: white; border-color: #0bacce; }
    .seat-vip { background-color: #ffc107; color: #000; border-color: #e0a800; font-weight: 800; }
    .seat-sweetbox { background-color: #ec4899; color: white; border-color: #db2777; font-weight: 800; width: 76px; } /* 35*2 + 6 gap */
    .seat-booked { background-color: #dee2e6; color: #6c757d; cursor: not-allowed; border-color: #ced4da; }
    .seat-selected { 
        background-color: #198754 !important; 
        color: white !important;
        border-color: #146c43 !important;
        box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.3);
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-4 bg-white rounded-3 shadow-sm">
    <div class="d-flex align-items-center mb-4 border-bottom pb-3">
        <a href="javascript:history.back()" class="btn btn-outline-secondary me-3">
            <i class="fas fa-arrow-left"></i> Trở Lại
        </a>
        <h2 class="mb-0 text-primary fw-bold"><i class="fas fa-chair me-2"></i>Chọn Ghế</h2>
    </div>

    <div class="row">
        <!-- Seat Map Area -->
        <div class="col-lg-8 mb-4">
            <div class="pos-seat-map text-center">
                <div class="cinema-screen mb-4">MÀN CẢNH</div>

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
                                     data-type="{{ $seat->seat_type }}">
                                    {{ $seat->seat_number }}
                                </div>
                            @endforeach
                            <span class="row-label">{{ $row }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Legend -->
            <div class="d-flex justify-content-center gap-4 mt-4 text-muted">
                <div class="d-flex align-items-center"><div class="seat-btn seat-regular me-2" style="width:25px;height:25px"></div> Thường</div>
                <div class="d-flex align-items-center"><div class="seat-btn seat-vip me-2" style="width:25px;height:25px"></div> VIP</div>
                <div class="d-flex align-items-center"><div class="seat-btn seat-sweetbox me-2" style="width:40px;height:25px"></div> Đôi</div>
                <div class="d-flex align-items-center"><div class="seat-btn seat-selected me-2" style="width:25px;height:25px"></div> Đang chọn</div>
                <div class="d-flex align-items-center"><div class="seat-btn seat-booked me-2" style="width:25px;height:25px"></div> Đã bán</div>
            </div>
        </div>

        <!-- POS Sidebar -->
        <div class="col-lg-4">
            <div class="card border-primary mb-3">
                <div class="card-header bg-primary text-white fw-bold">
                    Thông tin GD ({{ $showtime->room->name }})
                </div>
                <div class="card-body">
                    <p class="mb-1 text-muted small">Phim</p>
                    <h6 class="fw-bold text-dark">{{ $showtime->movie->title }}</h6>
                    <hr>
                    <p class="mb-1 text-muted small">Suất chiếu</p>
                    <h6 class="fw-bold">{{ $showtime->start_time->format('H:i | d/m/Y') }}</h6>
                    <hr>
                    <div id="selectedSeatsList" class="mb-3 p-2 bg-light rounded text-center text-muted" style="min-height: 40px;">
                        Chưa chọn ghế
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">Tổng cộng:</span>
                        <span class="fs-4 fw-bold text-danger" id="totalPriceDisplay">0₫</span>
                    </div>

                    <button class="btn btn-success w-100 py-3 fw-bold fs-5 shadow-sm" id="btnContinue" disabled onclick="proceedToCheckout()">
                        Thanh Toán <i class="fas fa-arrow-right ms-2"></i>
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
    
    function updateCart() {
        const btnContinue = document.getElementById('btnContinue');
        const listDiv = document.getElementById('selectedSeatsList');
        const totalDiv = document.getElementById('totalPriceDisplay');
        
        if (selectedSeats.size === 0) {
            btnContinue.disabled = true;
            listDiv.innerHTML = 'Chưa chọn ghế';
            listDiv.classList.add('text-muted');
            listDiv.classList.remove('text-success', 'fw-bold');
            totalDiv.textContent = '0₫';
            return;
        }
        
        btnContinue.disabled = false;
        
        let total = 0;
        let codes = [];
        
        selectedSeats.forEach(id => {
            const el = document.querySelector(`[data-id="${id}"]`);
            if (el) {
                codes.push(el.dataset.code);
                const basePrice = ticketPrices[el.dataset.type] || 0;
                total += basePrice + surcharge;
            }
        });
        
        listDiv.innerHTML = codes.join(', ');
        listDiv.classList.remove('text-muted');
        listDiv.classList.add('text-success', 'fw-bold');
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
                    button.title = 'Ghế đã có người đặt hoặc đang được giữ';
                }
                selectedSeats.delete(id);
            });

            const conflictCodes = conflictIds.map(id => {
                const button = document.querySelector(`[data-id="${id}"]`);
                return button ? button.dataset.code : `ghế ${id}`;
            }).join(', ');

            alert(`Ghế ${conflictCodes} đã được khách chọn và đã có người đặt/giữ. Vui lòng chọn ghế khác.`);
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
            console.log('POS connecting to Showtime Channel: showtime.' + showtimeId);

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
            console.warn('Laravel Echo not available on POS. Polling every 5s.');
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
                            btn.title = "Ghế đã có người đặt hoặc đang được giữ";
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
@endsection
