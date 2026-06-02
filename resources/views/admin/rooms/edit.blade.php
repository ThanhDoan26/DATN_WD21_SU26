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
                                id="cinema_id" name="cinema_id" required>
                            <option value="">-- Chọn Rạp --</option>
                            @forelse($cinemas as $cinema)
                                <option value="{{ $cinema->id }}" {{ old('cinema_id', $room->cinema_id) === (string)$cinema->id ? 'selected' : '' }}>
                                    {{ $cinema->name }}
                                </option>
                            @empty
                                <option disabled>Không có rạp nào</option>
                            @endforelse
                        </select>
                        @error('cinema_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                        <label for="format" class="form-label">Format *</label>
                        <input type="text" class="form-control @error('format') is-invalid @enderror"
                               id="format" name="format" value="{{ old('format', $room->format) }}" required>
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
@endsection
