@extends('admin.layouts.app')

@section('title', 'Edit Showtime - Admin')
@section('page_title', 'Chỉnh Sửa Suất Chiếu')

@section('content')
@php
    $prices = [];
    foreach($showtime->ticketPrices as $tp) {
        $prices[$tp->seat_type] = floatval($tp->price);
    }
@endphp

<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.showtimes.index') }}">Showtimes</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<div class="page-title">
    <h2><i class="fas fa-edit"></i> Chỉnh Sửa Suất Chiếu</h2>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-clock"></i> Thông Tin Suất Chiếu
    </div>
    <div class="card-body">
        <form action="{{ route('admin.showtimes.update', $showtime->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="movie_id" class="form-label">Phim *</label>
                        <select id="movie_id" name="movie_id" class="form-select @error('movie_id') is-invalid @enderror" required>
                            <option value="">-- Chọn phim --</option>
                            @foreach($movies as $movie)
                                <option value="{{ $movie->id }}" data-duration="{{ $movie->duration }}" {{ old('movie_id', $showtime->movie_id) == $movie->id ? 'selected' : '' }}>
                                    {{ $movie->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('movie_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="room_id" class="form-label">Phòng Chiếu *</label>
                        <select id="room_id" name="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
                            <option value="">-- Chọn phòng --</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" {{ old('room_id', $showtime->room_id) == $room->id ? 'selected' : '' }}>
                                    {{ $room->cinema->name }} / {{ $room->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('room_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="start_time" class="form-label">Thời Gian Bắt Đầu *</label>
                        <input type="datetime-local" id="start_time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time', $showtime->start_time->format('Y-m-d\TH:i')) }}" required>
                        @error('start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="end_time" class="form-label">Thời Gian Kết Thúc *</label>
                        <input type="datetime-local" id="end_time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time', $showtime->end_time->format('Y-m-d\TH:i')) }}" required>
                        @error('end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="status" class="form-label">Trạng Thái *</label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="">-- Chọn trạng thái --</option>
                            @foreach(\App\Models\Showtime::STATUSES as $status)
                                <option value="{{ $status }}" {{ old('status', $showtime->status) == $status ? 'selected' : '' }}>
                                    {{ ucfirst(strtolower($status)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="surcharge" class="form-label">Phụ thu suất chiếu (VNĐ / ghế)</label>
                        <input type="number" step="0.01" min="0" id="surcharge" name="surcharge" class="form-control @error('surcharge') is-invalid @enderror" value="{{ old('surcharge', $showtime->surcharge) }}">
                        @error('surcharge')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="my-4">
            <h5 class="mb-3"><i class="fas fa-ticket-alt"></i> Cấu Hình Giá Vé & Sơ Đồ Ghế</h5>
            
            <div class="row mb-4" id="ticket-prices-section" style="display: none;">
                <div class="col-md-4">
                    <label class="form-label text-success fw-bold">Giá Ghế Regular (VNĐ) *</label>
                    <input type="text" id="price_Regular" name="ticket_prices[Regular]" class="form-control price-input @error('ticket_prices.Regular') is-invalid @enderror" value="{{ old('ticket_prices.Regular', $prices['Regular'] ?? '') }}" placeholder="VD: 80.000">
                    @error('ticket_prices.Regular')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label text-warning fw-bold">Giá Ghế VIP (VNĐ) *</label>
                    <input type="text" id="price_VIP" name="ticket_prices[VIP]" class="form-control price-input @error('ticket_prices.VIP') is-invalid @enderror" value="{{ old('ticket_prices.VIP', $prices['VIP'] ?? '') }}" placeholder="VD: 100.000">
                    @error('ticket_prices.VIP')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label text-danger fw-bold">Giá Ghế Sweetbox (VNĐ) *</label>
                    <input type="text" id="price_Sweetbox" name="ticket_prices[Sweetbox]" class="form-control price-input @error('ticket_prices.Sweetbox') is-invalid @enderror" value="{{ old('ticket_prices.Sweetbox', $prices['Sweetbox'] ?? '') }}" placeholder="VD: 150.000">
                    @error('ticket_prices.Sweetbox')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-4" id="seat-map-wrapper" style="display: none;">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <!-- Legend -->
                            <div class="seat-legend">
                                <div class="legend-item">
                                    <div class="legend-box bg-sky">R</div>
                                    <span>Regular</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-box bg-gold">V</div>
                                    <span>VIP</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-box bg-pink">S</div>
                                    <span>Sweetbox</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-box" style="background-color: #cbd5e1; color: #64748b;">
                                        <i class="fas fa-wrench" style="font-size: 0.55rem;"></i>
                                    </div>
                                    <span>Unavailable</span>
                                </div>
                            </div>

                            <!-- Seat Map -->
                            <div class="seat-map-wrapper-inner">
                                <!-- Màn hình -->
                                <div class="cinema-screen">
                                    <i class="fas fa-tv"></i> MÀN HÌNH
                                </div>
                                <!-- Grid ghế -->
                                <div id="seatsGrid"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-info-circle"></i> Chi tiết ghế chọn
                        </div>
                        <div class="card-body" id="seat-detail-card">
                            <p class="text-muted">Vui lòng click vào một ghế trên sơ đồ để xem chi tiết.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu Thay Đổi
                </button>
                <a href="{{ route('admin.showtimes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay Lại
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('extra_css')
<style>
    .seat-map-wrapper-inner { background: #ffffff; padding: 40px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; align-items: center; margin: 20px 0; border: 1px solid #e2e8f0; overflow-x: auto; }
    .cinema-screen { width: 80%; max-width: 600px; margin: 0 auto 40px auto; padding: 12px 0; text-align: center; background: linear-gradient(180deg, rgba(30, 60, 114, 0.12) 0%, rgba(30, 60, 114, 0.02) 100%); border-top: 6px solid #1e3c72; border-radius: 8px 8px 120px 120px; font-size: 0.85rem; font-weight: 700; letter-spacing: 8px; color: #1e3c72; box-shadow: 0 8px 25px -8px rgba(30, 60, 114, 0.25); text-transform: uppercase; }
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
    .seat.unavailable { background-color: #cbd5e1 !important; border-color: #94a3b8 !important; color: #64748b !important; cursor: not-allowed; box-shadow: none; opacity: 0.75; }
    .seat.selected-active { outline: 3px solid #1e3c72; outline-offset: 2px; animation: pulseSelection 1.5s infinite; }
    @keyframes pulseSelection { 0% { outline-color: rgba(30, 60, 114, 0.8); } 50% { outline-color: rgba(30, 60, 114, 0.1); } 100% { outline-color: rgba(30, 60, 114, 0.8); } }
    .seat-legend { display: flex; gap: 20px; margin: 10px 0 30px 0; flex-wrap: wrap; justify-content: center; background-color: #f8fafc; padding: 15px 25px; border-radius: 12px; border: 1px solid #e2e8f0; }
    .legend-item { display: flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 500; color: #475569; }
    .legend-box { width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.65rem; font-weight: 700; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); }
    .bg-sky { background-color: #0ea5e9 !important; color: #ffffff; }
    .bg-pink { background-color: #ec4899 !important; color: #ffffff; }
    .bg-gold { background-color: #f59e0b !important; color: #1e293b; }
</style>
@endsection

@section('extra_js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roomSelect = document.getElementById('room_id');
        const seatMapWrapper = document.getElementById('seat-map-wrapper');
        const ticketPricesSection = document.getElementById('ticket-prices-section');
        const seatDetailCard = document.getElementById('seat-detail-card');
        let selectedSeatElement = null;
        
        const priceInputs = document.querySelectorAll('.price-input');
        
        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
        }
        
        function formatInputPrice(value) {
            if (!value) return '';
            let val = value.toString().replace(/\D/g, "");
            return val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        priceInputs.forEach(input => {
            if (input.value) {
                input.value = formatInputPrice(input.value);
            }
            
            input.addEventListener('input', function() {
                this.value = formatInputPrice(this.value);
            });
        });

        const forms = document.querySelectorAll('.card-body form');
        forms.forEach(form => {
            form.addEventListener('submit', function() {
                priceInputs.forEach(input => {
                    input.value = input.value.replace(/\./g, '');
                });
            });
        });

        function loadSeatMap(roomId) {
            if (!roomId) {
                seatMapWrapper.style.display = 'none';
                ticketPricesSection.style.display = 'none';
                return;
            }

            fetch(`/admin/seats/by-room/${roomId}`)
                .then(response => response.json())
                .then(seats => {
                    const grid = document.getElementById('seatsGrid');
                    grid.innerHTML = '';
                    
                    if (seats.length === 0) {
                        grid.innerHTML = '<p class="text-danger text-center">Phòng chiếu này chưa có cấu hình ghế.</p>';
                        seatMapWrapper.style.display = 'flex';
                        return;
                    }

                    seatMapWrapper.style.display = 'flex';
                    ticketPricesSection.style.display = 'flex';
                    
                    const typesInRoom = [...new Set(seats.map(s => s.seat_type))];
                    ['Regular', 'VIP', 'Sweetbox'].forEach(type => {
                        const input = document.getElementById(`price_${type}`);
                        const inputDiv = input.parentElement;
                        if (typesInRoom.includes(type)) {
                            inputDiv.style.display = 'block';
                            input.setAttribute('required', 'required');
                        } else {
                            inputDiv.style.display = 'none';
                            input.removeAttribute('required');
                            // No clear value on edit
                        }
                    });

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
                            seatDiv.className = `seat ${seat.seat_type.toLowerCase()} ${seat.status.toLowerCase()}`;
                            
                            if (seat.status === 'UNAVAILABLE') {
                                seatDiv.innerHTML = `<i class="fas fa-wrench" title="Ghế Hỏng"></i>`;
                            } else {
                                seatDiv.textContent = `${seat.row_name}${seat.seat_number}`;
                            }
                            
                            seatDiv.title = `Ghế ${seat.row_name}${seat.seat_number} - Loại: ${seat.seat_type} - Trạng thái: ${seat.status}`;
                            seatDiv.onclick = () => {
                                if (selectedSeatElement) {
                                    selectedSeatElement.classList.remove('selected-active');
                                }
                                selectedSeatElement = seatDiv;
                                seatDiv.classList.add('selected-active');

                                const priceInput = document.getElementById(`price_${seat.seat_type}`);
                                const currentPrice = priceInput && priceInput.value ? priceInput.value.replace(/\./g, '') : 0;
                                
                                const typeClass = seat.seat_type === 'Regular' ? 'bg-sky' : (seat.seat_type === 'VIP' ? 'bg-gold' : 'bg-pink');

                                seatDetailCard.innerHTML = `
                                    <h4 class="text-primary mb-3">Ghế: ${seat.row_name}${seat.seat_number}</h4>
                                    <p><strong>Loại ghế:</strong> <span class="badge ${typeClass}">${seat.seat_type}</span></p>
                                    <p><strong>Trạng thái:</strong> <span class="badge bg-${seat.status === 'AVAILABLE' ? 'success' : 'danger'}">${seat.status}</span></p>
                                    <hr>
                                    <h5 class="text-success">Giá vé: ${currentPrice > 0 ? formatCurrency(currentPrice) : '<em>Chưa nhập giá</em>'}</h5>
                                    <small class="text-muted">(Giá hiển thị dựa trên cấu hình bạn đang nhập bên trên)</small>
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
                .catch(error => console.error('Error fetching seats:', error));
        }

        const movieSelect = document.getElementById('movie_id');
        const startInput = document.getElementById('start_time');
        const endInput = document.getElementById('end_time');

        function formatDatetimeLocal(date) {
            const pad = (value) => String(value).padStart(2, '0');
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
        }

        function getSelectedMovieDuration() {
            const selectedOption = movieSelect.options[movieSelect.selectedIndex];
            return selectedOption ? Number(selectedOption.dataset.duration || 0) : 0;
        }

        function updateEndTime() {
            if (!startInput.value || !movieSelect.value) {
                return;
            }

            const durationMinutes = getSelectedMovieDuration();
            if (!durationMinutes || Number.isNaN(durationMinutes)) {
                return;
            }

            const startDate = new Date(startInput.value);
            if (Number.isNaN(startDate.getTime())) {
                return;
            }

            const calculatedEnd = new Date(startDate.getTime() + (durationMinutes + 15) * 60 * 1000);
            const formattedEnd = formatDatetimeLocal(calculatedEnd);

            if (endInput.dataset.autoComputed !== 'false') {
                endInput.value = formattedEnd;
                endInput.dataset.autoComputed = 'true';
            }
        }

        endInput.addEventListener('input', function() {
            this.dataset.autoComputed = 'false';
        });

        movieSelect.addEventListener('change', updateEndTime);
        startInput.addEventListener('change', updateEndTime);

        roomSelect.addEventListener('change', function() {
            loadSeatMap(this.value);
            seatDetailCard.innerHTML = '<p class="text-muted">Vui lòng click vào một ghế trên sơ đồ để xem chi tiết.</p>';
            selectedSeatElement = null;
        });

        if (roomSelect.value) {
            loadSeatMap(roomSelect.value);
        }

        updateEndTime();
    });
</script>
@endsection
