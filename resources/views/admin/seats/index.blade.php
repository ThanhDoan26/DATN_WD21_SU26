@extends('admin.layouts.app')

@section('title', 'Seats - Admin')
@section('page_title', 'Seats Management')

@section('extra_css')
<style>
    .seat-map {
        background: #f9f9f9;
        padding: 30px;
        border-radius: 10px;
        display: inline-block;
        margin: 20px 0;
    }

    .seats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(40px, 1fr));
        gap: 10px;
        max-width: 600px;
        margin: 20px 0;
    }

    .seat {
        width: 40px;
        height: 40px;
        border: 2px solid #ddd;
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .seat:hover {
        transform: scale(1.05);
    }

    .seat.available {
        background: #28a745;
        color: white;
        border-color: #28a745;
    }

    .seat.available:hover {
        background: #218838;
    }

    .seat.unavailable {
        background: #dc3545;
        color: white;
        border-color: #dc3545;
        cursor: not-allowed;
    }

    .seat.regular {
        background: #17a2b8;
    }

    .seat.vip {
        background: #ffc107;
        color: #333;
    }

    .seat.sweetbox {
        background: #e83e8c;
        color: white;
    }

    .seat-legend {
        display: flex;
        gap: 20px;
        margin: 20px 0;
        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .legend-box {
        width: 30px;
        height: 30px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .filter-section {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .filter-section select {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 0.9rem;
    }
</style>
@endsection

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Seats</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title">
    <div>
        <h2><i class="fas fa-chair"></i> Sơ đồ Ghế Ngồi</h2>
        <p class="text-muted" style="margin-top: 5px;">Xem sơ đồ ghế vật lý của các phòng chiếu</p>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <div class="row">
        <div class="col-md-4">
            <label for="cinemaFilter" class="form-label">Lọc theo Rạp:</label>
            <select id="cinemaFilter" class="form-select" onchange="filterByCinema(this.value)">
                <option value="">-- Chọn Rạp --</option>
                @forelse(($cinemas ?? []) as $cinema)
                    <option value="{{ $cinema->id }}">{{ $cinema->name }}</option>
                @empty
                    <option disabled>Không có rạp nào</option>
                @endforelse
            </select>
        </div>
        <div class="col-md-4">
            <label for="roomFilter" class="form-label">Lọc theo Phòng:</label>
            <select id="roomFilter" class="form-select" onchange="filterByRoom(this.value)">
                <option value="">-- Chọn Phòng --</option>
                @forelse(($rooms ?? []) as $room)
                    <option value="{{ $room->id }}">{{ $room->name }}</option>
                @empty
                    <option disabled>Không có phòng nào</option>
                @endforelse
            </select>
        </div>
    </div>
</div>

<!-- Seats Map -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-th"></i> Sơ đồ Ghế
    </div>
    <div class="card-body">
        <!-- Legend -->
        <div class="seat-legend">
            <div class="legend-item">
                <div class="legend-box" style="background: #17a2b8;">R</div>
                <span>Regular (Ghế thường)</span>
            </div>
            <div class="legend-item">
                <div class="legend-box" style="background: #ffc107; color: #333;">V</div>
                <span>VIP (Ghế VIP)</span>
            </div>
            <div class="legend-item">
                <div class="legend-box" style="background: #e83e8c;">S</div>
                <span>Sweetbox (Ghế sofa)</span>
            </div>
            <div class="legend-item">
                <div class="legend-box" style="background: #28a745;">✓</div>
                <span>Available (Trống)</span>
            </div>
            <div class="legend-item">
                <div class="legend-box" style="background: #dc3545;">✗</div>
                <span>Unavailable (Hỏng)</span>
            </div>
        </div>

        <!-- Seats Grid -->
        <div class="seat-map">
            <div style="text-align: center; margin-bottom: 20px; font-weight: bold; font-size: 1.1rem;">
                🎬 SCREEN 🎬
            </div>
            <div class="seats-grid" id="seatsGrid">
                @forelse(($seats ?? []) as $seat)
                <div class="seat {{ strtolower($seat->seat_type) }} {{ $seat->status === 'AVAILABLE' ? 'available' : 'unavailable' }}"
                     title="{{ $seat->row_name }}{{ $seat->seat_number }} - {{ $seat->seat_type }}"
                     onclick="selectSeat(this, {{ $seat->id }})">
                    {{ $seat->row_name }}{{ $seat->seat_number }}
                </div>
                @empty
                <p class="text-muted" style="grid-column: 1/-1; text-align: center;">Chưa có ghế nào. Vui lòng thêm ghế mới.</p>
                @endforelse
            </div>
        </div>

        <!-- Stats -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="stat-box">
                    <i class="fas fa-chair" style="font-size: 1.5rem; color: #17a2b8;"></i>
                    <div class="stat-number">{{ ($seats ?? [])->where('seat_type', 'Regular')->count() }}</div>
                    <div class="stat-label">Regular Seats</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <i class="fas fa-crown" style="font-size: 1.5rem; color: #ffc107;"></i>
                    <div class="stat-number">{{ ($seats ?? [])->where('seat_type', 'VIP')->count() }}</div>
                    <div class="stat-label">VIP Seats</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <i class="fas fa-heart" style="font-size: 1.5rem; color: #e83e8c;"></i>
                    <div class="stat-number">{{ ($seats ?? [])->where('seat_type', 'Sweetbox')->count() }}</div>
                    <div class="stat-label">Sweetbox Seats</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <i class="fas fa-check-circle" style="font-size: 1.5rem; color: #28a745;"></i>
                    <div class="stat-number">{{ ($seats ?? [])->where('status', 'AVAILABLE')->count() }}</div>
                    <div class="stat-label">Available Seats</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Seats Table -->
<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-list"></i> Danh sách Ghế Chi tiết
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Phòng</th>
                    <th>Vị trí</th>
                    <th>Loại</th>
                    <th>Trạng thái</th>
                    <th>Tạo lúc</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($seats ?? []) as $seat)
                <tr>
                    <td><strong>#{{ $seat->id }}</strong></td>
                    <td>{{ $seat->room->name ?? 'N/A' }}</td>
                    <td>
                        <span class="badge bg-info">{{ $seat->row_name }}{{ $seat->seat_number }}</span>
                    </td>
                    <td>
                        <span class="badge bg-secondary">{{ $seat->seat_type }}</span>
                    </td>
                    <td>
                        @if($seat->status === 'AVAILABLE')
                            <span class="badge bg-success">Available</span>
                        @else
                            <span class="badge bg-danger">Unavailable</span>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted">{{ $seat->created_at->format('d/m/Y H:i') }}</small>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="fas fa-inbox" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">Chưa có ghế nào.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('extra_js')
<script>
    function filterByCinema(cinemaId) {
        // TODO: Implement cinema filter
        console.log('Filter by cinema:', cinemaId);
    }

    function filterByRoom(roomId) {
        // TODO: Implement room filter
        console.log('Filter by room:', roomId);
    }

    function selectSeat(element, seatId) {
        // TODO: Implement seat selection logic
        console.log('Selected seat:', seatId);
    }
</script>
@endsection
