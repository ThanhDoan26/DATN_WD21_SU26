@extends('admin.layouts.app')

@section('title', 'Room Details - Admin')
@section('page_title', 'Room Details')

@section('extra_css')
<style>
    .seat-map-wrapper { background: #ffffff; padding: 40px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; align-items: center; margin: 20px 0; border: 1px solid #e2e8f0; overflow-x: auto; }
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

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.rooms.index') }}">Rooms</a></li>
            <li class="breadcrumb-item active">Details</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-eye"></i> Chi tiết Phòng Chiếu: #{{ $room->id }}</h2>
        <p class="text-muted">Thông tin tổng quan về phòng chiếu</p>
    </div>
    <div>
        <a href="{{ route('admin.rooms.edit', $room->id) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Sửa Phòng
        </a>
        <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<div class="row">
    <!-- Cột trái: Thông tin cơ bản -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white border-0 rounded-top">
                <i class="fas fa-info-circle"></i> Thông Tin Cơ Bản
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th style="width: 35%;">Mã Phòng</th>
                            <td>#{{ $room->id }}</td>
                        </tr>
                        <tr>
                            <th>Tên Phòng</th>
                            <td><strong>{{ $room->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Thuộc Rạp</th>
                            <td>
                                @if($room->cinema)
                                    <span class="badge bg-secondary">{{ $room->cinema->name }}</span>
                                @else
                                    <span class="text-muted">Không xác định</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Định Dạng (Format)</th>
                            <td><span class="badge bg-info">{{ $room->format }}</span></td>
                        </tr>
                        <tr>
                            <th>Tổng Ghế (Khai báo)</th>
                            <td>{{ $room->total_seats ?? 0 }} ghế</td>
                        </tr>
                        <tr>
                            <th>Trạng Thái</th>
                            <td>
                                @if($room->status === 'ACTIVE')
                                    <span class="badge bg-success"><i class="fas fa-check-circle"></i> Active</span>
                                @elseif($room->status === 'INACTIVE')
                                    <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Inactive</span>
                                @elseif($room->status === 'MAINTENANCE')
                                    <span class="badge bg-warning text-dark"><i class="fas fa-tools"></i> Maintenance</span>
                                @else
                                    <span class="badge bg-secondary"><i class="fas fa-lock"></i> Closed</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Ngày Tạo</th>
                            <td>{{ $room->created_at ? $room->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Cập Nhật Lần Cuối</th>
                            <td>{{ $room->updated_at ? $room->updated_at->format('d/m/Y H:i') : 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Cột phải: Thống kê & Mở rộng -->
    <div class="col-md-6 mb-4">
        <div class="row">
            <!-- Box 1 -->
            <div class="col-6 mb-3">
                <div class="stat-box border h-100">
                    <div class="stat-number text-primary">{{ $room->seats->count() }}</div>
                    <div class="stat-label">Ghế đã thiết lập</div>
                    <a href="#seatMapSection" class="btn btn-sm btn-outline-primary mt-2">Xem Sơ đồ ghế</a>
                </div>
            </div>
            
            <!-- Box 2 -->
            <div class="col-6 mb-3">
                <div class="stat-box border h-100">
                    @php
                        $activeShowtimes = $room->getActiveShowtimesCount();
                    @endphp
                    <div class="stat-number text-warning">{{ $activeShowtimes }}</div>
                    <div class="stat-label">Suất Chiếu Hợp Lệ</div>
                    <!-- Assuming showtimes.index takes room_id in future implementation -->
                    <a href="{{ route('admin.showtimes.index') }}?room_id={{ $room->id }}" class="btn btn-sm btn-outline-warning mt-2">Xem Chi Tiết</a>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-light text-dark">
                <i class="fas fa-cogs"></i> Hành Động Nhanh
            </div>
            <div class="card-body">
                @php
                    $activeShowtimes = $room->getActiveShowtimesCount();
                @endphp

                @if($activeShowtimes > 0)
                    <div class="alert alert-warning mb-3" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Không thể xóa phòng</strong> - Phòng đang có <strong>{{ $activeShowtimes }} suất chiếu</strong> hợp lệ.
                        Vui lòng xóa hoặc hủy tất cả suất chiếu trước khi xóa phòng này.
                    </div>
                    <a href="{{ route('admin.showtimes.index') }}?room_id={{ $room->id }}" class="btn btn-warning w-100">
                        <i class="fas fa-film"></i> Quản Lý Suất Chiếu
                    </a>
                @else
                    <p class="text-muted mb-3">Phòng không có suất chiếu hợp lệ. Bạn có thể xóa phòng này.</p>
                    <form action="{{ route('admin.rooms.destroy', $room->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phòng chiếu này?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash"></i> Xóa Phòng Chiếu Này
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- ========== SƠ ĐỒ GHẾ ========== -->
<div class="card mt-4" id="seatMapSection">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-th"></i> Sơ Đồ Ghế Phòng Chiếu</span>
        <span class="badge bg-light text-dark">
            Tổng: <strong id="totalSeatCount">{{ $room->seats->count() }}</strong> ghế
        </span>
    </div>
    <div class="card-body">
        <!-- Thống kê ghế theo loại -->
        <div class="row mb-4">
            <div class="col-md-3 col-6 mb-2">
                <div class="stat-box border-start border-4 border-info py-3">
                    <div class="stat-number text-info" id="regularCount">0</div>
                    <div class="stat-label"><i class="fas fa-chair"></i> Regular</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="stat-box border-start border-4 border-warning py-3">
                    <div class="stat-number text-warning" id="vipCount">0</div>
                    <div class="stat-label"><i class="fas fa-crown"></i> VIP</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="stat-box border-start border-4 border-danger py-3">
                    <div class="stat-number text-danger" id="sweetboxCount">0</div>
                    <div class="stat-label"><i class="fas fa-heart"></i> Sweetbox</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="stat-box border-start border-4 border-success py-3">
                    <div class="stat-number text-success" id="availableCount">0</div>
                    <div class="stat-label"><i class="fas fa-check-circle"></i> Available</div>
                </div>
            </div>
        </div>

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
        <div class="seat-map-wrapper">
            <!-- Màn hình -->
            <div class="cinema-screen">
                <i class="fas fa-tv"></i> MÀN HÌNH
            </div>

            <!-- Grid ghế -->
            <div id="seatsGrid"></div>
        </div>
    </div>
</div>

<!-- ========== CHI TIẾT GHẾ KHI CLICK ========== -->
<div id="seatDetailContainer" class="card mt-3" style="display: none;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-info-circle"></i> Chi Tiết Ghế</span>
        <button type="button" class="btn btn-sm btn-light" onclick="closeSeatDetail()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-auto">
                <div id="detailSeatIcon" class="seat" style="width: 56px; height: 56px; font-size: 1rem; pointer-events: none;"></div>
            </div>
            <div class="col">
                <table class="table table-sm table-bordered mb-0">
                    <tr>
                        <th style="width: 120px;">ID</th>
                        <td id="detailSeatId">-</td>
                    </tr>
                    <tr>
                        <th>Vị Trí</th>
                        <td id="detailSeatPos">-</td>
                    </tr>
                    <tr>
                        <th>Loại Ghế</th>
                        <td><span id="detailSeatType" class="badge bg-secondary">-</span></td>
                    </tr>
                    <tr>
                        <th>Trạng Thái</th>
                        <td><span id="detailSeatStatus" class="badge bg-secondary">-</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_js')
<script>
    const seats = @json($room->seats);
    let selectedSeatElement = null;

    function renderSeatMap() {
        const grid = document.getElementById('seatsGrid');
        grid.innerHTML = '';
        
        if (seats.length === 0) {
            grid.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-inbox text-muted" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    <p class="text-muted mt-3 fw-semibold">Phòng chiếu này chưa được thiết lập ghế vật lý.</p>
                </div>
            `;
            return;
        }
        
        const rows = {};
        let rCount = 0, vCount = 0, sCount = 0, aCount = 0;

        seats.forEach(seat => {
            if (!rows[seat.row_name]) rows[seat.row_name] = [];
            rows[seat.row_name].push(seat);

            if (seat.seat_type === 'Regular') rCount++;
            else if (seat.seat_type === 'VIP') vCount++;
            else if (seat.seat_type === 'Sweetbox') sCount++;
            
            if (seat.status === 'AVAILABLE') aCount++;
        });

        document.getElementById('regularCount').textContent = rCount;
        document.getElementById('vipCount').textContent = vCount;
        document.getElementById('sweetboxCount').textContent = sCount;
        document.getElementById('availableCount').textContent = aCount;
        
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
                seatDiv.onclick = () => selectSeat(seatDiv, seat);
                
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
    }

    function selectSeat(element, seat) {
        if (selectedSeatElement) {
            selectedSeatElement.classList.remove('selected-active');
        }
        
        selectedSeatElement = element;
        element.classList.add('selected-active');
        
        document.getElementById('seatDetailContainer').style.display = 'block';
        document.getElementById('detailSeatId').textContent = `#${seat.id}`;
        document.getElementById('detailSeatPos').textContent = `${seat.row_name}${seat.seat_number}`;
        
        const typeBadge = document.getElementById('detailSeatType');
        typeBadge.textContent = seat.seat_type;
        typeBadge.className = 'badge';
        if (seat.seat_type === 'Regular') typeBadge.classList.add('bg-sky');
        else if (seat.seat_type === 'VIP') typeBadge.classList.add('bg-gold');
        else if (seat.seat_type === 'Sweetbox') typeBadge.classList.add('bg-pink');
        else typeBadge.classList.add('bg-secondary');
        
        const statusBadge = document.getElementById('detailSeatStatus');
        statusBadge.textContent = seat.status;
        statusBadge.className = 'badge';
        if (seat.status === 'AVAILABLE') {
            statusBadge.classList.add('bg-success');
        } else {
            statusBadge.classList.add('bg-danger');
        }
        
        const detailIcon = document.getElementById('detailSeatIcon');
        detailIcon.textContent = `${seat.row_name}${seat.seat_number}`;
        detailIcon.className = `seat ${seat.seat_type.toLowerCase()}`;
        if (seat.status === 'UNAVAILABLE') {
            detailIcon.classList.add('unavailable');
            detailIcon.innerHTML = `<i class="fas fa-wrench"></i>`;
        } else {
            detailIcon.innerHTML = `${seat.row_name}${seat.seat_number}`;
        }
    }

    function closeSeatDetail() {
        document.getElementById('seatDetailContainer').style.display = 'none';
        if (selectedSeatElement) {
            selectedSeatElement.classList.remove('selected-active');
            selectedSeatElement = null;
        }
    }

    document.addEventListener('DOMContentLoaded', renderSeatMap);
</script>
@endsection
