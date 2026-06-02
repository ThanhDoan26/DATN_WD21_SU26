@extends('admin.layouts.app')

@section('title', 'Create Seats - Admin')
@section('page_title', 'Create New Seats')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.seats.index') }}">Seats</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title">
    <h2><i class="fas fa-chair"></i> Thêm Ghế Mới</h2>
</div>

<!-- Info Alert -->
<div class="alert alert-info" role="alert">
    <i class="fas fa-info-circle"></i>
    <strong>Hướng dẫn:</strong> Bạn có thể tạo nhiều ghế cùng lúc bằng cách chỉ định từ ghế và đến ghế trong cùng một dòng.
</div>

<!-- Form Card -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-plus-circle"></i> Seat Information
    </div>
    <div class="card-body">
        <form action="{{ route('admin.seats.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="room_id" class="form-label">Phòng *</label>
                        <select class="form-select @error('room_id') is-invalid @enderror"
                                id="room_id" name="room_id" required>
                            <option value="">-- Chọn Phòng --</option>
                            @forelse($rooms as $room)
                                <option value="{{ $room->id }}" {{ old('room_id') === (string)$room->id ? 'selected' : '' }}>
                                    {{ $room->cinema->name }} - {{ $room->name }}
                                </option>
                            @empty
                                <option disabled>Không có phòng nào</option>
                            @endforelse
                        </select>
                        @error('room_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="row_name" class="form-label">Dòng Ghế (Row) *</label>
                        <input type="text" class="form-control @error('row_name') is-invalid @enderror"
                               id="row_name" name="row_name" value="{{ old('row_name') }}"
                               placeholder="vd: A, B, C" maxlength="1" required>
                        @error('row_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="seat_number_start" class="form-label">Ghế Bắt Đầu *</label>
                        <input type="number" class="form-control @error('seat_number_start') is-invalid @enderror"
                               id="seat_number_start" name="seat_number_start" value="{{ old('seat_number_start') }}"
                               min="1" placeholder="vd: 1" required>
                        @error('seat_number_start')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="seat_number_end" class="form-label">Ghế Kết Thúc *</label>
                        <input type="number" class="form-control @error('seat_number_end') is-invalid @enderror"
                               id="seat_number_end" name="seat_number_end" value="{{ old('seat_number_end') }}"
                               min="1" placeholder="vd: 10" required>
                        @error('seat_number_end')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="seat_type" class="form-label">Loại Ghế *</label>
                        <select class="form-select @error('seat_type') is-invalid @enderror"
                                id="seat_type" name="seat_type" required>
                            <option value="">-- Chọn Loại --</option>
                            <option value="Regular" {{ old('seat_type') === 'Regular' ? 'selected' : '' }}>Regular (Ghế thường)</option>
                            <option value="VIP" {{ old('seat_type') === 'VIP' ? 'selected' : '' }}>VIP (Ghế VIP)</option>
                            <option value="Sweetbox" {{ old('seat_type') === 'Sweetbox' ? 'selected' : '' }}>Sweetbox (Ghế sofa)</option>
                        </select>
                        @error('seat_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="status" class="form-label">Trạng thái *</label>
                        <select class="form-select @error('status') is-invalid @enderror"
                                id="status" name="status" required>
                            <option value="">-- Chọn trạng thái --</option>
                            <option value="AVAILABLE" {{ old('status') === 'AVAILABLE' ? 'selected' : '' }}>Available (Trống)</option>
                            <option value="UNAVAILABLE" {{ old('status') === 'UNAVAILABLE' ? 'selected' : '' }}>Unavailable (Hỏng)</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="alert alert-warning" role="alert">
                <i class="fas fa-lightbulb"></i>
                <strong>Ví dụ:</strong> Nếu chọn dòng A, từ ghế 1 đến 10, loại Regular → sẽ tạo 10 ghế: A1, A2, A3, ..., A10
            </div>

            <hr>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Tạo Mới
                </button>
                <a href="{{ route('admin.seats.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
