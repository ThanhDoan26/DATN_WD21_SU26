@extends('admin.layouts.app')

@section('title', 'Create Room - Admin')
@section('page_title', 'Create New Room')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.rooms.index') }}">Rooms</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title">
    <h2><i class="fas fa-door-open"></i> Thêm Phòng Mới</h2>
</div>

<!-- Form Card -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-plus-circle"></i> Room Information
    </div>
    <div class="card-body">
        <form action="{{ route('admin.rooms.store') }}" method="POST">
            @csrf

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
                                        {{ old('cinema_id') === (string)$cinema->id ? 'selected' : '' }}>
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
                               id="name" name="name" value="{{ old('name') }}" placeholder="vd: Cinema 1, IMAX 2" required>
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
                            <option value="2D" {{ old('format') == '2D' ? 'selected' : '' }}>2D</option>
                            <option value="3D" {{ old('format') == '3D' ? 'selected' : '' }}>3D</option>
                            <option value="IMAX" {{ old('format') == 'IMAX' ? 'selected' : '' }}>IMAX</option>
                            <option value="4DX" {{ old('format') == '4DX' ? 'selected' : '' }}>4DX</option>
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
                               id="total_seats" name="total_seats" value="{{ old('total_seats') }}" min="0">
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
                            <option value="ACTIVE" {{ old('status') === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                            <option value="INACTIVE" {{ old('status') === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                            <option value="MAINTENANCE" {{ old('status') === 'MAINTENANCE' ? 'selected' : '' }}>Maintenance</option>
                            <option value="CLOSED" {{ old('status') === 'CLOSED' ? 'selected' : '' }}>Closed</option>
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
                    <i class="fas fa-save"></i> Tạo Mới
                </button>
                <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showCinemaInfo() {
        const select = document.getElementById('cinema_id');
        const selectedOption = select.options[select.selectedIndex];
        const infoDiv = document.getElementById('cinema_info');
        
        if (selectedOption.value) {
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
    
    // Gọi hàm ngay khi load trang để xử lý trường hợp có old('cinema_id')
    document.addEventListener('DOMContentLoaded', function() {
        showCinemaInfo();
    });
</script>
@endpush
