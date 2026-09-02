@extends('layouts.manager')

@section('title', 'Chi tiết Suất Chiếu - Quản lý Rạp')
@section('page_title', 'Chi tiết suất chiếu')

@section('content')
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('manager.dashboard') }}">Bảng điều khiển</a></li>
            <li class="breadcrumb-item"><a href="{{ route('manager.showtimes.index') }}">Lịch chiếu</a></li>
            <li class="breadcrumb-item active">Chi tiết</li>
        </ol>
    </nav>
</div>

<!-- Header Card: Movie & Showtime Overview -->
<div class="card shadow-sm border-0 mb-4 rounded-3">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge {{ $showtime->status_badge_class }} px-3 py-1.5 fs-6">
                        {{ $showtime->status_label }}
                    </span>
                    @if($hasBookings)
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1">
                            <i class="fas fa-ticket-alt me-1"></i> Đã phát sinh vé đặt
                        </span>
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">
                            <i class="fas fa-check-circle me-1"></i> Chưa có vé đặt
                        </span>
                    @endif
                </div>
                <h2 class="fw-bold text-dark mb-1">{{ $showtime->movie->title }}</h2>
                <p class="text-muted mb-2">
                    <i class="fas fa-film me-1 text-primary"></i> Định dạng: <strong>{{ $showtime->movie->format ?? '2D' }}</strong> |
                    <i class="fas fa-clock me-1 text-primary"></i> Thời lượng: <strong>{{ $showtime->movie->duration ?? 'N/A' }} phút</strong>
                </p>
                <div class="d-flex flex-wrap gap-3 text-secondary small">
                    <div><i class="fas fa-map-marker-alt text-danger me-1"></i> Rạp: <strong>{{ $showtime->room?->cinema?->name ?? 'N/A' }}</strong></div>
                    <div><i class="fas fa-door-open text-info me-1"></i> Phòng chiếu: <strong>{{ $showtime->room->name }}</strong></div>
                    <div><i class="fas fa-calendar-alt text-success me-1"></i> Thời gian: <strong>{{ $showtime->start_time->format('d/m/Y H:i') }} - {{ $showtime->end_time ? $showtime->end_time->format('H:i') : 'N/A' }}</strong></div>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('manager.showtimes.edit', $showtime->id) }}" class="btn btn-primary px-3">
                    <i class="fas fa-edit me-1"></i> Chỉnh Sửa Suất Chiếu
                </a>
                <a href="{{ route('manager.showtimes.index') }}" class="btn btn-outline-secondary px-3 ms-1">
                    <i class="fas fa-arrow-left me-1"></i> Quay Lại
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-3 bg-light">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">TỔNG SỐ GHẾ</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $showtime->room->total_seats ?? $showtime->room->seats()->count() }}</div>
                    </div>
                    <div class="bg-primary text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-couch"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-3 bg-light">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">GHẾ ĐÃ BÁN</div>
                        <div class="fs-4 fw-bold text-danger mt-1">{{ $bookedSeatsCount }}</div>
                    </div>
                    <div class="bg-danger text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-3 bg-light">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">GHẾ CÒN TRỐNG</div>
                        @php
                            $totalSeats = $showtime->room->total_seats ?? $showtime->room->seats()->count();
                            $availSeats = max(0, $totalSeats - $bookedSeatsCount);
                        @endphp
                        <div class="fs-4 fw-bold text-success mt-1">{{ $availSeats }}</div>
                    </div>
                    <div class="bg-success text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-chair"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-3 bg-light">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">TỶ LỆ LẤP ĐẦY</div>
                        <div class="fs-4 fw-bold text-primary mt-1">{{ $occupancyRate }}%</div>
                    </div>
                    <div class="bg-info text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pricing Breakdown Cards -->
<div class="card shadow-sm border-0 mb-4 rounded-3">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold"><i class="fas fa-money-bill-wave text-success me-2"></i>Bảng Giá Vé & Phụ Thu Suất Chiếu</h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="p-3 border rounded-3 bg-light">
                    <div class="text-muted small">Phụ Thu Suất Chiếu</div>
                    <div class="fs-5 fw-bold text-dark mt-1">{{ number_format($showtime->surcharge, 0, ',', '.') }} VNĐ / ghế</div>
                    <small class="text-muted">(Cộng vào từng loại ghế)</small>
                </div>
            </div>
            @php
                $prices = $showtime->ticketPrices->pluck('price', 'seat_type')->toArray();
            @endphp
            <div class="col-md-3">
                <div class="p-3 border rounded-3" style="border-left: 4px solid #0ea5e9 !important;">
                    <div class="text-muted small">Ghế Regular (Thường)</div>
                    <div class="fs-5 fw-bold" style="color: #0284c7;">
                        {{ isset($prices['Regular']) ? number_format($prices['Regular'] + $showtime->surcharge, 0, ',', '.') . ' VNĐ' : 'Chưa cấu hình' }}
                    </div>
                    <small class="text-muted">Gốc: {{ isset($prices['Regular']) ? number_format($prices['Regular'], 0, ',', '.') . ' đ' : '0' }}</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 border rounded-3" style="border-left: 4px solid #f59e0b !important;">
                    <div class="text-muted small">Ghế VIP</div>
                    <div class="fs-5 fw-bold" style="color: #d97706;">
                        {{ isset($prices['VIP']) ? number_format($prices['VIP'] + $showtime->surcharge, 0, ',', '.') . ' VNĐ' : 'Chưa cấu hình' }}
                    </div>
                    <small class="text-muted">Gốc: {{ isset($prices['VIP']) ? number_format($prices['VIP'], 0, ',', '.') . ' đ' : '0' }}</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 border rounded-3" style="border-left: 4px solid #ec4899 !important;">
                    <div class="text-muted small">Ghế Sweetbox (Đôi)</div>
                    <div class="fs-5 fw-bold" style="color: #db2777;">
                        {{ isset($prices['Sweetbox']) ? number_format($prices['Sweetbox'] + $showtime->surcharge, 0, ',', '.') . ' VNĐ' : 'Chưa cấu hình' }}
                    </div>
                    <small class="text-muted">Gốc: {{ isset($prices['Sweetbox']) ? number_format($prices['Sweetbox'], 0, ',', '.') . ' đ' : '0' }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Seat Map Section -->
<div class="row mb-4" id="seat-map-wrapper">
    <div class="col-lg-8">
        <div class="seat-map-wrapper-inner">
            <div class="seat-map-header text-center mb-3">
                <h5 class="fw-bold mb-1">Sơ Đồ Ghế Thực Tế Phòng Chiếu</h5>
                <span class="badge bg-primary font-weight-normal" id="selected-room-info">{{ $showtime->room->name }}</span>
            </div>

            <div class="seat-legend">
                <div class="legend-item"><div class="legend-box bg-sky"></div> Regular</div>
                <div class="legend-item"><div class="legend-box bg-gold"></div> VIP</div>
                <div class="legend-item"><div class="legend-box bg-pink"></div> Sweetbox</div>
                <div class="legend-item"><div class="legend-box bg-booked"></div> Đã Đặt</div>
                <div class="legend-item"><div class="legend-box bg-secondary"></div> Ghế Hỏng</div>
            </div>

            <div class="cinema-screen"><i class="fas fa-tv me-1"></i> MÀN HÌNH CHIẾU</div>

            <div id="seatsGrid">
                <!-- Seat grid will be rendered dynamically here -->
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card bg-light border-0 shadow-sm sticky-top" style="top: 20px;">
            <div class="card-header bg-white font-weight-bold">
                <i class="fas fa-info-circle text-primary me-2"></i> Chi Tiết Ghế
            </div>
            <div class="card-body" id="seat-detail-card">
                <p class="text-muted">Vui lòng click vào một ghế trên sơ đồ để xem trạng thái đặt vé chi tiết.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_css')
<style>
    .seat-map-wrapper-inner { background: #ffffff; padding: 40px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; align-items: center; margin: 0; border: 1px solid #e2e8f0; overflow-x: auto; }
    .cinema-screen { width: 80%; max-width: 600px; margin: 0 auto 40px auto; padding: 12px 0; text-align: center; background: linear-gradient(180deg, rgba(13, 148, 136, 0.12) 0%, rgba(13, 148, 136, 0.02) 100%); border-top: 6px solid #0d9488; border-radius: 8px 8px 120px 120px; font-size: 0.85rem; font-weight: 700; letter-spacing: 8px; color: #0d9488; box-shadow: 0 8px 25px -8px rgba(13, 148, 136, 0.25); text-transform: uppercase; font-family: 'Sora', sans-serif; }
    .seat-layout-container { display: flex; flex-direction: column; align-items: center; gap: 12px; width: 100%; min-width: 580px; padding: 10px 0; }
    .seat-row { display: flex; align-items: center; justify-content: center; width: 100%; gap: 8px; }
    .row-label { font-size: 0.85rem; font-weight: 700; color: #94a3b8; width: 30px; user-select: none; }
    .row-label.left { text-align: right; margin-right: 15px; }
    .row-label.right { text-align: left; margin-left: 15px; }
    .seat-row-seats { display: flex; align-items: center; gap: 8px; }
    .seat { width: 42px; height: 42px; border: 2px solid transparent; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700; cursor: pointer; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); position: relative; color: #ffffff; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04); user-select: none; }
    .seat:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15); filter: brightness(1.1); }
    .seat.regular { background-color: #0ea5e9; border-color: #0284c7; }
    .seat.vip { background-color: #f59e0b; color: #1e293b; border-color: #d97706; }
    .seat.sweetbox { background-color: #ec4899; width: 90px; border-color: #db2777; }
    .seat.booked { background-color: #dc2626 !important; border-color: #b91c1c !important; color: #ffffff !important; }
    .seat.unavailable { background-color: #cbd5e1 !important; border-color: #94a3b8 !important; color: #64748b !important; cursor: not-allowed; box-shadow: none; opacity: 0.75; }
    .seat.selected-active { background-color: #22c55e !important; border-color: #16a34a !important; color: #ffffff !important; outline: none !important; box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.4); animation: pulseSelection 1.5s infinite; }
    @keyframes pulseSelection { 0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); } 70% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); } 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); } }
    .seat-legend { display: flex; gap: 20px; margin: 10px 0 30px 0; flex-wrap: wrap; justify-content: center; background-color: #f8fafc; padding: 15px 25px; border-radius: 12px; border: 1px solid #e2e8f0; }
    .legend-item { display: flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 500; color: #475569; }
    .legend-box { width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.65rem; font-weight: 700; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); }
    .bg-sky { background-color: #0ea5e9 !important; color: #ffffff; }
    .bg-pink { background-color: #ec4899 !important; color: #ffffff; }
    .bg-gold { background-color: #f59e0b !important; color: #1e293b; }
    .bg-booked { background-color: #dc2626 !important; color: #ffffff; }
</style>
@endsection

@section('extra_js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const seatDetailCard = document.getElementById('seat-detail-card');
        let selectedSeatElement = null;
        
        const priceMap = @json($prices);
        const surchargeVal = parseFloat('{{ $showtime->surcharge ?? 0 }}') || 0;

        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
        }

        function loadSeatMap() {
            const roomId = '{{ $showtime->room_id }}';
            const showtimeId = '{{ $showtime->id }}';
            const fetchUrl = `/manager/seats/by-room/${roomId}?showtime_id=${showtimeId}`;

            fetch(fetchUrl)
                .then(response => response.json())
                .then(seats => {
                    const grid = document.getElementById('seatsGrid');
                    if (!grid) return;
                    grid.innerHTML = '';
                    
                    if (seats.length === 0) {
                        grid.innerHTML = '<p class="text-danger text-center py-4">Phòng chiếu này chưa có cấu hình ghế.</p>';
                        return;
                    }

                    const rows = {};
                    seats.forEach(seat => {
                        if (!rows[seat.row_name]) rows[seat.row_name] = [];
                        rows[seat.row_name].push(seat);
                    });

                    const sortedRowNames = Object.keys(rows).sort();
                    const container = document.createElement('div');
                    container.className = 'seat-layout-container';
                    
                    sortedRowNames.forEach(rowName => {
                        const rowDiv = document.createElement('div');
                        rowDiv.className = 'seat-row';
                        
                        const leftLabel = document.createElement('div');
                        leftLabel.className = 'row-label left';
                        leftLabel.textContent = rowName;
                        rowDiv.appendChild(leftLabel);
                        
                        const seatsWrapper = document.createElement('div');
                        seatsWrapper.className = 'seat-row-seats';
                        
                        const sortedSeats = rows[rowName].sort((a, b) => parseInt(a.seat_number) - parseInt(b.seat_number));
                        
                        sortedSeats.forEach(seat => {
                            const seatDiv = document.createElement('div');
                            const isBooked = seat.is_booked_in_showtime;
                            const isUnavailable = seat.status === 'UNAVAILABLE';
                            
                            let seatClass = `seat ${seat.seat_type.toLowerCase()}`;
                            if (isBooked) {
                                seatClass += ' booked';
                            } else if (isUnavailable) {
                                seatClass += ' unavailable';
                            }
                            seatDiv.className = seatClass;
                            
                            if (isBooked) {
                                seatDiv.innerHTML = `<span style="font-size:0.65rem;">${seat.row_name}${seat.seat_number}</span>`;
                            } else if (isUnavailable) {
                                seatDiv.innerHTML = `<i class="fas fa-wrench" title="Ghế Hỏng"></i>`;
                            } else {
                                seatDiv.textContent = `${seat.row_name}${seat.seat_number}`;
                            }
                            
                            seatDiv.title = `Ghế ${seat.row_name}${seat.seat_number} - Loại: ${seat.seat_type}` + (isBooked ? ' (Đã Có Khách Đặt)' : (isUnavailable ? ' (Ghế Hỏng)' : ' (Còn Trống)'));
                            seatDiv.onclick = () => {
                                if (selectedSeatElement) {
                                    selectedSeatElement.classList.remove('selected-active');
                                }
                                selectedSeatElement = seatDiv;
                                seatDiv.classList.add('selected-active');

                                const basePrice = priceMap[seat.seat_type] ? parseFloat(priceMap[seat.seat_type]) : 0;
                                const totalPrice = basePrice + surchargeVal;
                                
                                const typeClass = seat.seat_type === 'Regular' ? 'bg-sky' : (seat.seat_type === 'VIP' ? 'bg-gold' : 'bg-pink');
                                
                                let bookingStatusBadge = '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Còn trống</span>';
                                if (isBooked) {
                                    bookingStatusBadge = '<span class="badge bg-danger"><i class="fas fa-user-check me-1"></i>Đã có khách đặt</span>';
                                } else if (isUnavailable) {
                                    bookingStatusBadge = '<span class="badge bg-secondary"><i class="fas fa-wrench me-1"></i>Ghế hỏng</span>';
                                }

                                seatDetailCard.innerHTML = `
                                    <h4 class="text-primary mb-3">Ghế: ${seat.row_name}${seat.seat_number}</h4>
                                    <p class="mb-2"><strong>Loại ghế:</strong> <span class="badge ${typeClass}">${seat.seat_type}</span></p>
                                    <p class="mb-2"><strong>Tình trạng đặt:</strong> ${bookingStatusBadge}</p>
                                    <p class="mb-2"><strong>Trạng thái vật lý:</strong> <span class="badge bg-${seat.status === 'AVAILABLE' ? 'success' : 'danger'}">${seat.status}</span></p>
                                    <hr>
                                    <p class="mb-1 text-muted">Giá vé cơ bản: <strong class="text-dark">${basePrice > 0 ? formatCurrency(basePrice) : 'Chưa cấu hình'}</strong></p>
                                    <p class="mb-2 text-muted">Phụ thu suất chiếu: <strong class="text-dark">+${formatCurrency(surchargeVal)}</strong></p>
                                    <h5 class="text-success mt-2">Tổng giá vé: ${totalPrice > 0 ? formatCurrency(totalPrice) : '<em>0 đ</em>'}</h5>
                                `;
                            };
                            
                            seatsWrapper.appendChild(seatDiv);
                        });
                        
                        rowDiv.appendChild(seatsWrapper);
                        
                        const rightLabel = document.createElement('div');
                        rightLabel.className = 'row-label right';
                        rightLabel.textContent = rowName;
                        rowDiv.appendChild(rightLabel);
                        
                        container.appendChild(rowDiv);
                    });
                    
                    grid.appendChild(container);
                })
                .catch(error => {
                    console.error('Error fetching seats:', error);
                    const grid = document.getElementById('seatsGrid');
                    if (grid) grid.innerHTML = '<p class="text-danger text-center py-4">Lỗi tải sơ đồ ghế. Vui lòng thử lại.</p>';
                });
        }

        loadSeatMap();
    });
</script>
@endsection
