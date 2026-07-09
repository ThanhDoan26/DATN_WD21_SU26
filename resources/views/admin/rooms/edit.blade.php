@extends('admin.layouts.app')

@section('title', 'Edit Room - Admin')
@section('page_title', 'Edit Room')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.rooms.index') }}">Rooms</a></li>
            <li class="breadcrumb-item active">Edit</li>
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
        <i class="fas fa-pencil"></i> Room Information
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
                    <div class="mb-3">
                        <label for="total_seats" class="form-label">Tổng Ghế</label>
                        <input type="number" class="form-control @error('total_seats') is-invalid @enderror"
                               id="total_seats" name="total_seats" value="{{ old('total_seats', $room->total_seats) }}" min="0">
                        @error('total_seats')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                            <option value="ACTIVE" {{ old('status', $room->status) === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                            <option value="INACTIVE" {{ old('status', $room->status) === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                            <option value="MAINTENANCE" {{ old('status', $room->status) === 'MAINTENANCE' ? 'selected' : '' }}>Maintenance</option>
                            <option value="CLOSED" {{ old('status', $room->status) === 'CLOSED' ? 'selected' : '' }}>Closed</option>
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
        <div class="d-flex justify-content-center gap-4 mb-4">
            <div class="d-flex align-items-center gap-2"><div class="border border-success bg-white" style="width:24px;height:24px; border-radius:4px;"></div> Trống</div>
            <div class="d-flex align-items-center gap-2"><div class="border border-danger bg-danger text-white d-flex align-items-center justify-content-center" style="width:24px;height:24px; border-radius:4px;"><i class="fas fa-times" style="font-size: 12px;"></i></div> Hỏng</div>
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
                                        $bgColor = 'bg-white border-success text-dark';
                                        $cursor  = 'cursor-pointer';
                                        $icon    = '';
                                        // Sweetbox chiếm 2 ghế, nên CSS rộng hơn
                                        $width   = (strtolower($seat->seat_type) === 'sweetbox') ? '70px' : '35px';

                                        if ($seat->status === 'BROKEN') {
                                            $bgColor = 'bg-danger text-white border-danger';
                                            $icon = '<i class="fas fa-times"></i>';
                                        } elseif ($seat->status === 'BOOKED') {
                                            $bgColor = 'bg-secondary text-white border-secondary';
                                            $cursor = 'not-allowed';
                                            $icon = '<i class="fas fa-lock"></i>';
                                        }
                                    @endphp
                                    <div class="seat-item border rounded d-flex align-items-center justify-content-center shadow-sm {{ $bgColor }}"
                                         style="width: {{ $width }}; height: 35px; font-size: 0.85rem; cursor: {{ $cursor === 'not-allowed' ? 'not-allowed' : 'pointer' }}; {{ $seat->status === 'BOOKED' ? 'opacity: 0.7; pointer-events: none;' : 'transition: all 0.2s;' }}"
                                         data-seat-id="{{ $seat->id }}"
                                         data-seat-number="{{ $seat->seat_number }}"
                                         data-status="{{ $seat->status }}"
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
    });

    // Hàm xử lý Ajax khóa/mở ghế
    function toggleSeatStatus(element) {
        const seatId = element.getAttribute('data-seat-id');
        const currentStatus = element.getAttribute('data-status');
        
        // Bỏ qua nếu ghế đã được mua
        if (currentStatus === 'BOOKED') return;

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
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Cập nhật lại UI dựa trên trạng thái mới
                if (data.new_status === 'BROKEN') {
                    element.className = 'seat-item border rounded d-flex align-items-center justify-content-center shadow-sm bg-danger text-white border-danger';
                    element.innerHTML = '<i class="fas fa-times"></i>';
                } else {
                    element.className = 'seat-item border rounded d-flex align-items-center justify-content-center shadow-sm bg-white text-dark border-success';
                    element.innerHTML = element.getAttribute('data-seat-number');
                }
                element.setAttribute('data-status', data.new_status);
            } else {
                alert(data.message || 'Có lỗi xảy ra!');
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
