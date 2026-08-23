@extends('admin.layouts.app')

@section('title', 'Edit Room - Admin')
@section('page_title', 'Edit Room')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Bảng điều khiển</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.rooms.index') }}">Phòng chiếu</a></li>
            <li class="breadcrumb-item active">Sửa phòng</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title">
    <h2><i class="fas fa-edit"></i> Sửa Phòng</h2>
</div>

<!-- Form Card -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-pencil"></i> Thông tin phòng chiếu
    </div>
    <div class="card-body">
        <form action="{{ route('admin.rooms.update', $room->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="cinema_id" class="form-label">Rạp *</label>
                        <select class="form-select @error('cinema_id') is-invalid @enderror"
                                id="cinema_id" name="cinema_id" required onchange="showCinemaInfo()">
                            <option value="">-- Chọn Rạp --</option>
                            @forelse($cinemas as $cinema)
                                <option value="{{ $cinema->id }}" 
                                        data-address="{{ $cinema->address }}"
                                        data-city="{{ $cinema->city }}"
                                        data-phone="{{ $cinema->phone }}"
                                        {{ old('cinema_id', $room->cinema_id) == $cinema->id ? 'selected' : '' }}>
                                    {{ $cinema->name }}
                                </option>
                            @empty
                                <option disabled>Không có rạp nào</option>
                            @endforelse
                        </select>
                        @error('cinema_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        
                        <!-- Hiển thị thông tin rạp tương ứng -->
                        <div id="cinema_info" class="mt-2 p-2 border rounded bg-light" style="display: none; font-size: 0.9em;">
                            <strong>Địa chỉ:</strong> <span id="c_address"></span>, <span id="c_city"></span><br>
                            <strong>SĐT:</strong> <span id="c_phone"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên Phòng *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $room->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="format" class="form-label">Phân loại phòng (Format) *</label>
                        <select class="form-select @error('format') is-invalid @enderror" id="format" name="format" required>
                            <option value="">-- Chọn phân loại --</option>
                            <option value="2D" {{ old('format', $room->format) == '2D' ? 'selected' : '' }}>2D</option>
                            <option value="3D" {{ old('format', $room->format) == '3D' ? 'selected' : '' }}>3D</option>
                            <option value="IMAX" {{ old('format', $room->format) == 'IMAX' ? 'selected' : '' }}>IMAX</option>
                            <option value="4DX" {{ old('format', $room->format) == '4DX' ? 'selected' : '' }}>4DX</option>
                        </select>
                        @error('format')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="total_rows" class="form-label">Số Hàng (Rows) *</label>
                                <input type="number" class="form-control @error('total_rows') is-invalid @enderror"
                                       id="total_rows" name="total_rows" value="{{ old('total_rows', $currentRows ?? 8) }}" min="1" max="26" required>
                                <div class="form-text">Từ 1 đến 26 hàng (A-Z)</div>
                                @error('total_rows')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="total_cols" class="form-label">Số Cột (Columns) *</label>
                                <input type="number" class="form-control @error('total_cols') is-invalid @enderror"
                                       id="total_cols" name="total_cols" value="{{ old('total_cols', $currentCols ?? 12) }}" min="1" max="30" required>
                                <div class="form-text">Từ 1 đến 30 ghế/hàng</div>
                                @error('total_cols')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <!-- Hidden total_seats - sẽ tự tính -->
                    <input type="hidden" name="total_seats" id="total_seats" value="{{ old('total_seats', $room->total_seats) }}">
                    <div class="form-text text-info fw-bold mt-1">
                        <i class="fas fa-info-circle"></i> Sơ đồ ghế sẽ được tạo lại nếu thay đổi kích thước. Tổng: <span id="calcTotal">{{ $room->total_seats }}</span> ghế.
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="status" class="form-label">Trạng thái *</label>
                        <select class="form-select @error('status') is-invalid @enderror"
                                id="status" name="status" required>
                            <option value="">-- Chọn trạng thái --</option>
                            <option value="ACTIVE" {{ old('status', $room->status) === 'ACTIVE' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="INACTIVE" {{ old('status', $room->status) === 'INACTIVE' ? 'selected' : '' }}>Không hoạt động</option>
                            <option value="MAINTENANCE" {{ old('status', $room->status) === 'MAINTENANCE' ? 'selected' : '' }}>Bảo trì</option>
                            <option value="CLOSED" {{ old('status', $room->status) === 'CLOSED' ? 'selected' : '' }}>Đóng</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <hr>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cập nhật
                </button>
                <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy
                </a>
            </div>
        </form>
    </div>
</div>

<!-- BẮT ĐẦU: Sơ đồ quản lý ghế -->
<div class="card mt-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <span><i class="fas fa-th"></i> Sơ đồ ghế - Click vào ghế để khóa/mở khóa hỏng</span>
    </div>
    <div class="card-body bg-light">
        <!-- Chú thích -->
        <div class="d-flex justify-content-center gap-4 mb-4 flex-wrap">
            <div class="d-flex align-items-center gap-2"><div class="border border-success bg-white" style="width:24px;height:24px; border-radius:4px;"></div> Trống</div>
            <div class="d-flex align-items-center gap-2"><div class="border border-danger bg-danger text-white d-flex align-items-center justify-content-center" style="width:24px;height:24px; border-radius:4px;"><i class="fas fa-times" style="font-size: 12px;"></i></div> Hỏng</div>
            <div class="d-flex align-items-center gap-2"><div class="border border-warning bg-warning text-dark d-flex align-items-center justify-content-center" style="width:24px;height:24px; border-radius:4px;"><i class="fas fa-user-clock" style="font-size: 12px;"></i></div> Đang giữ chỗ</div>
            <div class="d-flex align-items-center gap-2"><div class="border border-secondary bg-secondary text-white d-flex align-items-center justify-content-center" style="width:24px;height:24px; border-radius:4px;"><i class="fas fa-lock" style="font-size: 12px;"></i></div> Đã đặt</div>
        </div>

        <div class="seat-map-container" style="overflow-x: auto; min-width: 600px;">
            <!-- Màn hình chiếu -->
            <div class="mb-5 mx-auto" style="width: 60%; height: 10px; background: #ccc; box-shadow: 0 15px 10px -10px rgba(0,0,0,0.5); border-radius: 50% / 100% 100% 0 0; text-align: center; position: relative;">
                <span class="text-muted" style="position: absolute; top: -25px; left: 50%; transform: translateX(-50%); letter-spacing: 5px;">MÀN HÌNH</span>
            </div>
            
            <!-- Hiển thị ghế -->
            <div class="d-flex flex-column align-items-center gap-2">
                @if(isset($seatsByRow) && $seatsByRow->count() > 0)
                    @foreach($seatsByRow as $row => $rowSeats)
                        <div class="d-flex align-items-center gap-2">
                            <div class="row-label fw-bold text-muted" style="width: 30px; text-align: right;">{{ $row }}</div>
                            <div class="d-flex gap-2">
                                @foreach($rowSeats as $seat)
                                    @php
                                        $bgColor   = 'bg-white border-success text-dark';
                                        $cursor    = 'cursor-pointer';
                                        $icon      = '';
                                        $width     = (strtolower($seat->seat_type) === 'sweetbox') ? '70px' : '35px';
                                        $isHeld    = !empty($seat->is_held);
                                        $isBooked  = !empty($seat->is_booked);
                                        $canToggle = isset($seat->can_toggle) ? (bool)$seat->can_toggle : (!$isHeld && !$isBooked);
                                        $busStatus = $seat->business_status ?? ($isBooked ? 'BOOKED' : ($isHeld ? 'HELD' : $seat->status));

                                        if ($seat->status === 'BROKEN') {
                                            $bgColor = 'bg-danger text-white border-danger';
                                            $icon = '<i class="fas fa-times"></i>';
                                        } elseif ($isBooked || $seat->status === 'BOOKED') {
                                            $bgColor = 'bg-secondary text-white border-secondary';
                                            $cursor  = 'not-allowed';
                                            $icon    = '<i class="fas fa-lock"></i>';
                                        } elseif ($isHeld) {
                                            $bgColor = 'bg-warning text-dark border-warning';
                                            $cursor  = 'not-allowed';
                                            $icon    = '<i class="fas fa-user-clock"></i>';
                                        }
                                    @endphp
                                    <div class="seat-item border rounded d-flex align-items-center justify-content-center shadow-sm {{ $bgColor }}"
                                         style="width: {{ $width }}; height: 35px; font-size: 0.85rem; cursor: {{ $cursor === 'not-allowed' ? 'not-allowed' : 'pointer' }}; {{ !$canToggle ? 'opacity: 0.85;' : 'transition: all 0.2s;' }}"
                                         data-seat-id="{{ $seat->id }}"
                                         data-seat-number="{{ $seat->seat_number }}"
                                         data-status="{{ $seat->status }}"
                                         data-business-status="{{ $busStatus }}"
                                         data-can-toggle="{{ $canToggle ? 'true' : 'false' }}"
                                         title="{{ $isBooked ? 'Ghế đã có người đặt' : ($isHeld ? 'Ghế đang được khách giữ chỗ (10 phút)' : ($seat->status === 'BROKEN' ? 'Ghế hỏng' : 'Ghế trống')) }}"
                                         onclick="toggleSeatStatus(this)">
                                        {!! $icon ?: $seat->seat_number !!}
                                    </div>
                                @endforeach
                            </div>
                            <div class="row-label fw-bold text-muted" style="width: 30px; text-align: left;">{{ $row }}</div>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-warning">Chưa có dữ liệu ghế cho phòng này.</div>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- KẾT THÚC: Sơ đồ quản lý ghế -->
@endsection

@section('extra_js')
<script>
    function showCinemaInfo() {
        const select = document.getElementById('cinema_id');
        const selectedOption = select.options[select.selectedIndex];
        const infoDiv = document.getElementById('cinema_info');
        
        if (selectedOption && selectedOption.value) {
            const address = selectedOption.getAttribute('data-address');
            const city = selectedOption.getAttribute('data-city');
            const phone = selectedOption.getAttribute('data-phone');
            
            document.getElementById('c_address').innerText = address || 'N/A';
            document.getElementById('c_city').innerText = city || 'N/A';
            document.getElementById('c_phone').innerText = phone || 'N/A';
            
            infoDiv.style.display = 'block';
        } else {
            infoDiv.style.display = 'none';
        }
    }
    
    // Gọi hàm ngay khi load trang để xử lý trường hợp có old('cinema_id') hoặc dữ liệu cũ
    document.addEventListener('DOMContentLoaded', function() {
        showCinemaInfo();
        updateTotalSeats();
    });

    const rowsInput = document.getElementById('total_rows');
    const colsInput = document.getElementById('total_cols');
    const totalSeatsInput = document.getElementById('total_seats');
    const calcTotalSpan = document.getElementById('calcTotal');

    function updateTotalSeats() {
        const rows = parseInt(rowsInput.value) || 0;
        const cols = parseInt(colsInput.value) || 0;
        
        let rCount = 0, vCount = 0, sCount = 0;
        for (let r = 1; r <= rows; r++) {
            if (r === rows && rows > 1) {
                sCount += Math.floor(cols / 2);
            }
            else if (r <= 3) rCount += cols;
            else vCount += cols;
        }
        if (rows === 1) { rCount = cols; sCount = 0; }
        
        const actualTotal = rCount + vCount + sCount;
        if(totalSeatsInput) totalSeatsInput.value = actualTotal;
        if(calcTotalSpan) calcTotalSpan.textContent = actualTotal;
    }

    if (rowsInput && colsInput) {
        rowsInput.addEventListener('input', updateTotalSeats);
        colsInput.addEventListener('input', updateTotalSeats);
    }

    // Hàm xử lý Ajax khóa/mở ghế
    function toggleSeatStatus(element) {
        const seatId = element.getAttribute('data-seat-id');
        const businessStatus = element.getAttribute('data-business-status');
        const canToggle = element.getAttribute('data-can-toggle');
        
        // Chặn client-side nếu ghế đang bị giữ hoặc đã đặt
        if (canToggle === 'false' || businessStatus === 'BOOKED' || businessStatus === 'HELD') {
            if (businessStatus === 'HELD') {
                alert('Không thể khóa ghế đang được khách hàng giữ chỗ (đang trong thời gian thanh toán).');
            } else {
                alert('Không thể khóa ghế đã có người đặt trong suất chiếu hợp lệ.');
            }
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            alert('Lỗi: Không tìm thấy CSRF Token trên trang!');
            return;
        }

        // Hiệu ứng Loading
        element.style.pointerEvents = 'none';
        element.style.opacity = '0.5';

        fetch(`/admin/rooms/{{ $room->id }}/seats/${seatId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (body.success) {
                // Cập nhật lại UI dựa trên trạng thái mới
                if (body.new_status === 'BROKEN') {
                    element.className = 'seat-item border rounded d-flex align-items-center justify-content-center shadow-sm bg-danger text-white border-danger';
                    element.innerHTML = '<i class="fas fa-times"></i>';
                } else {
                    element.className = 'seat-item border rounded d-flex align-items-center justify-content-center shadow-sm bg-white text-dark border-success';
                    element.innerHTML = element.getAttribute('data-seat-number');
                }
                element.setAttribute('data-status', body.new_status);
                element.setAttribute('data-business-status', body.new_status);
                element.setAttribute('data-can-toggle', 'true');
            } else {
                alert(body.message || 'Có lỗi xảy ra!');
                // Nếu backend trả về trạng thái HELD hoặc BOOKED, cập nhật ngay UI
                if (body.business_status === 'HELD') {
                    element.className = 'seat-item border rounded d-flex align-items-center justify-content-center shadow-sm bg-warning text-dark border-warning';
                    element.innerHTML = '<i class="fas fa-user-clock"></i>';
                    element.setAttribute('data-business-status', 'HELD');
                    element.setAttribute('data-can-toggle', 'false');
                    element.style.cursor = 'not-allowed';
                } else if (body.business_status === 'BOOKED') {
                    element.className = 'seat-item border rounded d-flex align-items-center justify-content-center shadow-sm bg-secondary text-white border-secondary';
                    element.innerHTML = '<i class="fas fa-lock"></i>';
                    element.setAttribute('data-business-status', 'BOOKED');
                    element.setAttribute('data-can-toggle', 'false');
                    element.style.cursor = 'not-allowed';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Lỗi kết nối máy chủ!');
        })
        .finally(() => {
            element.style.pointerEvents = 'auto';
            element.style.opacity = '1';
        });
    }
</script>
@endsection
