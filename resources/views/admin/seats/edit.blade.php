@extends('admin.layouts.app')

@section('title', 'Edit Seat - Admin')
@section('page_title', 'Edit Seat')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.seats.index') }}">Seats</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title">
    <h2><i class="fas fa-edit"></i> Sửa Ghế</h2>
</div>

<!-- Form Card -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-pencil"></i> Seat Information
    </div>
    <div class="card-body">
        <form action="{{ route('admin.seats.update', $seat->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="room" class="form-label">Phòng</label>
                        <input type="text" class="form-control" id="room"
                               value="{{ $seat->room?->cinema?->name ?? 'N/A' }} - {{ $seat->room?->name ?? 'N/A' }}" disabled>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="position" class="form-label">Vị trí</label>
                        <input type="text" class="form-control" id="position"
                               value="{{ $seat->row_name }}{{ $seat->seat_number }}" disabled>
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
                            <option value="Regular" {{ old('seat_type', $seat->seat_type) === 'Regular' ? 'selected' : '' }}>Regular (Ghế thường)</option>
                            <option value="VIP" {{ old('seat_type', $seat->seat_type) === 'VIP' ? 'selected' : '' }}>VIP (Ghế VIP)</option>
                            <option value="Sweetbox" {{ old('seat_type', $seat->seat_type) === 'Sweetbox' ? 'selected' : '' }}>Sweetbox (Ghế sofa)</option>
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
                            <option value="AVAILABLE" {{ old('status', $seat->status) === 'AVAILABLE' ? 'selected' : '' }}>Available (Trống)</option>
                            <option value="UNAVAILABLE" {{ old('status', $seat->status) === 'UNAVAILABLE' ? 'selected' : '' }}>Unavailable (Hỏng)</option>
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
                <a href="{{ route('admin.seats.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
