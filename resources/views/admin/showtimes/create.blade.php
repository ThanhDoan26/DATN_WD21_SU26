@extends('admin.layouts.app')

@section('title', 'Add Showtime - Admin')
@section('page_title', 'Thêm Suất Chiếu Mới')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.showtimes.index') }}">Showtimes</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<div class="page-title">
    <h2><i class="fas fa-plus-circle"></i> Thêm Suất Chiếu Mới</h2>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-clock"></i> Thông Tin Suất Chiếu
    </div>
    <div class="card-body">
        <form action="{{ route('admin.showtimes.store') }}" method="POST">
            @csrf

            {{-- Tổng hợp lỗi --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" id="validation-alert">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-shrink-0"><i class="fas fa-exclamation-triangle fa-lg mt-1"></i></div>
                        <div>
                            <h6 class="alert-heading mb-2 fw-bold">Vui lòng kiểm tra lại các thông tin sau:</h6>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="movie_id" class="form-label">Phim *</label>
                        <select id="movie_id" name="movie_id" class="form-select @error('movie_id') is-invalid @enderror" required>
                            <option value="">-- Chọn phim --</option>
                            @foreach($movies as $movie)
                                <option value="{{ $movie->id }}" data-duration="{{ $movie->duration }}" {{ old('movie_id') == $movie->id ? 'selected' : '' }}>
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
                                <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                    {{ $room->cinema?->name ?? 'N/A' }} / {{ $room->name }}
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
                        <label class="form-label">Thời Gian Bắt Đầu *</label>
                        <div class="row g-2 align-items-center">
                            <div class="col-md-5">
                                <input type="date" id="start_date" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time') ? \Carbon\Carbon::parse(old('start_time'))->format('Y-m-d') : '' }}" required>
                            </div>
                            <div class="col-md-3">
                                <select id="start_hour" class="form-select" required>
                                    <option value="">Giờ</option>
                                    @for ($hour = 1; $hour <= 24; $hour++)
                                        <option value="{{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="start_minute" class="form-select" required>
                                    @for ($minute = 0; $minute < 60; $minute++)
                                        <option value="{{ str_pad($minute, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($minute, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-1">
                                <span id="start_period" class="form-text text-muted">&nbsp;</span>
                            </div>
                        </div>
                        <input type="hidden" id="start_time" name="start_time" value="{{ old('start_time') }}">
                        <div class="small text-muted">Chọn giờ:.</div>
                        @error('start_time')
                            <div class="invalid-feedback d-block">{{ $message }}</div>

                            <div class="text-danger small mt-1 d-flex align-items-center gap-1">
                                <i class="fas fa-circle-exclamation"></i> {{ $message }}
                            </div>

                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Thời Gian Kết Thúc *</label>
                        <div class="row g-2 align-items-center">
                            <div class="col-md-5">
                                <input type="date" id="end_date" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time') ? \Carbon\Carbon::parse(old('end_time'))->format('Y-m-d') : '' }}" required>
                            </div>
                            <div class="col-md-3">
                                <select id="end_hour" class="form-select" required>
                                    <option value="">Giờ</option>
                                    @for ($hour = 1; $hour <= 24; $hour++)
                                        <option value="{{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="end_minute" class="form-select" required>
                                    @for ($minute = 0; $minute < 60; $minute++)
                                        <option value="{{ str_pad($minute, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($minute, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-1">
                                <span id="end_period" class="form-text text-muted">&nbsp;</span>
                            </div>
                        </div>
                        <input type="hidden" id="end_time" name="end_time" value="{{ old('end_time') }}">
                        <div class="small text-muted">Chọn giờ .</div>
                        @error('end_time')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            <div class="text-danger small mt-1 d-flex align-items-center gap-1">
                                <i class="fas fa-circle-exclamation"></i> {{ $message }}
                            </div>
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
                                <option value="{{ $status }}" {{ old('status') == $status ? 'selected' : '' }}>
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
                        <input type="number" step="0.01" min="0" id="surcharge" name="surcharge" class="form-control @error('surcharge') is-invalid @enderror" value="{{ old('surcharge', 0) }}">
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
                    <input type="text" id="price_Regular" name="ticket_prices[Regular]" class="form-control price-input @error('ticket_prices.Regular') is-invalid @enderror" value="{{ old('ticket_prices.Regular') }}" placeholder="VD: 80.000">
                    @error('ticket_prices.Regular')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label text-warning fw-bold">Giá Ghế VIP (VNĐ) *</label>
                    <input type="text" id="price_VIP" name="ticket_prices[VIP]" class="form-control price-input @error('ticket_prices.VIP') is-invalid @enderror" value="{{ old('ticket_prices.VIP') }}" placeholder="VD: 100.000">
                    @error('ticket_prices.VIP')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label text-danger fw-bold">Giá Ghế Sweetbox (VNĐ) *</label>
                    <input type="text" id="price_Sweetbox" name="ticket_prices[Sweetbox]" class="form-control price-input @error('ticket_prices.Sweetbox') is-invalid @enderror" value="{{ old('ticket_prices.Sweetbox') }}" placeholder="VD: 150.000">
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
                    <i class="fas fa-save"></i> Lưu Suất Chiếu
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
    .seat-map-wrapper-inner { background: var(--bg-surface); padding: 40px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; align-items: center; margin: 20px 0; border: 1px solid var(--border-light); overflow-x: auto; }
    .cinema-screen { width: 80%; max-width: 600px; margin: 0 auto 40px auto; padding: 12px 0; text-align: center; background: linear-gradient(180deg, rgba(147, 51, 234, 0.12) 0%, rgba(147, 51, 234, 0.02) 100%); border-top: 6px solid var(--primary-color); border-radius: 8px 8px 120px 120px; font-size: 0.85rem; font-weight: 700; letter-spacing: 8px; color: var(--primary-color); box-shadow: 0 8px 25px -8px rgba(147, 51, 234, 0.25); text-transform: uppercase; font-family: 'Sora', sans-serif; }
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
    .seat.selected-active { background-color: #22c55e !important; border-color: #16a34a !important; color: #ffffff !important; outline: none !important; box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.4); animation: pulseSelection 1.5s infinite; }
    @keyframes pulseSelection { 0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); } 70% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); } 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); } }
    .seat-legend { display: flex; gap: 20px; margin: 10px 0 30px 0; flex-wrap: wrap; justify-content: center; background-color: #f8fafc; padding: 15px 25px; border-radius: 12px; border: 1px solid #e2e8f0; }
    .seat.selected-active { outline: 3px solid var(--primary-color); outline-offset: 2px; animation: pulseSelection 1.5s infinite; }
    @keyframes pulseSelection { 0% { outline-color: rgba(147, 51, 234, 0.8); } 50% { outline-color: rgba(147, 51, 234, 0.1); } 100% { outline-color: rgba(147, 51, 234, 0.8); } }
    .seat-legend { display: flex; gap: 20px; margin: 10px 0 30px 0; flex-wrap: wrap; justify-content: center; background-color: var(--bg-base); padding: 15px 25px; border-radius: 12px; border: 1px solid var(--border-light); }
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
                            input.value = '';
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
        const startDateInput = document.getElementById('start_date');
        const startHourInput = document.getElementById('start_hour');
        const startMinuteInput = document.getElementById('start_minute');
        const startPeriodText = document.getElementById('start_period');
        const endDateInput = document.getElementById('end_date');
        const endHourInput = document.getElementById('end_hour');
        const endMinuteInput = document.getElementById('end_minute');
        const endPeriodText = document.getElementById('end_period');
        const hiddenStartInput = document.getElementById('start_time');
        const hiddenEndInput = document.getElementById('end_time');

        let endAutoComputed = true;

        function pad(value) {
            return String(value).padStart(2, '0');
        }

        function formatDatetimeLocal(date) {
            const year = date.getFullYear();
            const month = pad(date.getMonth() + 1);
            const day = pad(date.getDate());
            const hours = pad(date.getHours());
            const minutes = pad(date.getMinutes());
            const seconds = pad(date.getSeconds());
            return `${year}-${month}-${day}T${hours}:${minutes}:${seconds}`;
        }

        function parseDatetimeLocal(value) {
            if (!value || typeof value !== 'string') {
                return null;
            }

            const parts = value.split('T');
            if (parts.length !== 2) {
                return null;
            }

            const [datePart, timePart] = parts;
            const [year, month, day] = datePart.split('-').map(Number);
            const timeParts = timePart.split(':').map(Number);
            const hour = timeParts[0];
            const minute = timeParts[1];
            const second = timeParts.length > 2 ? timeParts[2] : 0;

            if ([year, month, day, hour, minute, second].some(v => Number.isNaN(v))) {
                return null;
            }

            return new Date(year, month - 1, day, hour, minute, second, 0);
        }

        function getSelectedMovieDuration() {
            const selectedOption = movieSelect.options[movieSelect.selectedIndex];
            return selectedOption ? Number(selectedOption.dataset.duration || 0) : 0;
        }

        function updatePeriodText(hourValue, element) {
            const hour = Number(hourValue);
            if (!hourValue) {
                element.textContent = '';
                return;
            }
            element.textContent = hour >= 13 ? 'CH' : 'SA';
        }

        function enforce24OnlyZeroMinute(hourInput, minuteInput) {
            const is24 = Number(hourInput.value) === 24;
            minuteInput.querySelectorAll('option').forEach(option => {
                option.disabled = is24 && option.value !== '00';
            });
            if (is24 && minuteInput.value !== '00') {
                minuteInput.value = '00';
            }
        }

        function buildHiddenDatetime(dateInput, hourInput, minuteInput) {
            if (!dateInput.value || !hourInput.value || !minuteInput.value) {
                return '';
            }
            let date = new Date(`${dateInput.value}T00:00`);
            const hour = Number(hourInput.value);
            const minute = Number(minuteInput.value);

            if (hour === 24) {
                date.setDate(date.getDate() + 1);
                date.setHours(0, 0, 0, 0);
            } else {
                date.setHours(hour, minute, 0, 0);
            }

            return formatDatetimeLocal(date);
        }

        function setSelectorsFromHidden(dateInput, hourInput, minuteInput, periodText, hiddenInput) {
            const value = hiddenInput.value;
            const parsed = parseDatetimeLocal(value);
            if (!parsed) {
                return;
            }

            let displayDate = parsed;
            let displayHour = parsed.getHours();
            let displayMinute = parsed.getMinutes();

            if (displayHour === 0 && displayMinute === 0) {
                displayDate = new Date(parsed.getTime());
                displayDate.setDate(displayDate.getDate() - 1);
                displayHour = 24;
            }

            dateInput.value = `${displayDate.getFullYear()}-${pad(displayDate.getMonth() + 1)}-${pad(displayDate.getDate())}`;
            hourInput.value = pad(displayHour);
            minuteInput.value = pad(displayMinute);
            enforce24OnlyZeroMinute(hourInput, minuteInput);
            updatePeriodText(hourInput.value, periodText);
        }

        function computeExpectedEnd() {
            if (!hiddenStartInput.value || !movieSelect.value) {
                return '';
            }
            const startDateObject = parseDatetimeLocal(hiddenStartInput.value);
            const durationMinutes = getSelectedMovieDuration();
            if (!startDateObject || !durationMinutes || Number.isNaN(durationMinutes)) {
                return '';
            }
            const calculatedEnd = new Date(startDateObject.getTime() + (durationMinutes + 15) * 60 * 1000);
            return formatDatetimeLocal(calculatedEnd);
        }

        function updateStartHidden() {
            hiddenStartInput.value = buildHiddenDatetime(startDateInput, startHourInput, startMinuteInput);
            updatePeriodText(startHourInput.value, startPeriodText);
        }

        function updateEndHidden(forceManual = false) {
            hiddenEndInput.value = buildHiddenDatetime(endDateInput, endHourInput, endMinuteInput);
            updatePeriodText(endHourInput.value, endPeriodText);
            if (forceManual) {
                endAutoComputed = false;
            }
        }

        function updateEndFromStart() {
            const expectedEnd = computeExpectedEnd();
            if (!expectedEnd) {
                return;
            }
            hiddenEndInput.value = expectedEnd;
            setSelectorsFromHidden(endDateInput, endHourInput, endMinuteInput, endPeriodText, hiddenEndInput);
            endAutoComputed = true;
        }

        function syncAllTimeFields() {
            setSelectorsFromHidden(startDateInput, startHourInput, startMinuteInput, startPeriodText, hiddenStartInput);
            setSelectorsFromHidden(endDateInput, endHourInput, endMinuteInput, endPeriodText, hiddenEndInput);

            const expectedEnd = computeExpectedEnd();
            if (!hiddenEndInput.value && expectedEnd) {
                updateEndFromStart();
                endAutoComputed = true;
                return;
            }

            endAutoComputed = expectedEnd && expectedEnd === hiddenEndInput.value;
        }

        [startHourInput, startMinuteInput].forEach(input => {
            input.addEventListener('change', function () {
                enforce24OnlyZeroMinute(startHourInput, startMinuteInput);
                updateStartHidden();
                if (endAutoComputed || !hiddenEndInput.value) {
                    updateEndFromStart();
                }
            });
        });

        startDateInput.addEventListener('change', function () {
            updateStartHidden();
            if (endAutoComputed || !hiddenEndInput.value) {
                updateEndFromStart();
            }
        });

        [endHourInput, endMinuteInput, endDateInput].forEach(input => {
            input.addEventListener('change', function () {
                enforce24OnlyZeroMinute(endHourInput, endMinuteInput);
                updateEndHidden(true);
            });
        });

        movieSelect.addEventListener('change', function () {
            updateStartHidden();
            if (endAutoComputed) {
                updateEndFromStart();
            }
        });

        syncAllTimeFields();

        const showtimeForm = document.querySelector('form');
        if (showtimeForm) {
            showtimeForm.addEventListener('submit', function () {
                updateStartHidden();
                if (endAutoComputed || !hiddenEndInput.value) {
                    updateEndFromStart();
                } else {
                    updateEndHidden(true);
                }
            });
        }

        roomSelect.addEventListener('change', function() {
            loadSeatMap(this.value);
            seatDetailCard.innerHTML = '<p class="text-muted">Vui lòng click vào một ghế trên sơ đồ để xem chi tiết.</p>';
            selectedSeatElement = null;
        });

        if (roomSelect.value) {
            loadSeatMap(roomSelect.value);
        }
    });
</script>
@endsection
