@extends('admin.layouts.app')

@section('title', 'Edit Cinema - Admin')
@section('page_title', 'Edit Cinema')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Bảng điều khiển</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.cinemas.index') }}">Rạp</a></li>
            <li class="breadcrumb-item active">Sửa</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title">
    <h2><i class="fas fa-edit"></i> Sửa Rạp</h2>
</div>

<!-- Form Card -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-pencil"></i> Thông tin rạp
    </div>
    <div class="card-body">
        <form action="{{ route('admin.cinemas.update', $cinema->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên Rạp *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $cinema->name) }}" 
                               placeholder="Ví dụ: CGV Sư Vạn Hạnh - TP. Hồ Chí Minh" required>
                        <div class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Quy tắc đặt tên chuẩn: <code>[Tên Thương Hiệu] + [Tên TTTM/Địa danh] + [Tỉnh/Thành]</code> (Ví dụ: <em>CGV Sư Vạn Hạnh - TP. Hồ Chí Minh</em>).
                        </div>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email', $cinema->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label for="address" class="form-label">Địa chỉ *</label>
                        <input type="text" class="form-control @error('address') is-invalid @enderror"
                               id="address" name="address" value="{{ old('address', $cinema->address) }}" required>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-province-select 
                        :provinces="$provinces" 
                        :selected="old('city', $cinema->city)" 
                        name="city" 
                        label="Tỉnh / Thành phố" 
                        id="cinema-edit-city" />
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="phone" class="form-label">Hotline</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                id="phone" name="phone" value="{{ old('phone', $cinema->phone) }}">
                        @error('phone')
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
                            <option value="ACTIVE" {{ old('status', $cinema->status) === 'ACTIVE' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="INACTIVE" {{ old('status', $cinema->status) === 'INACTIVE' ? 'selected' : '' }}>Không hoạt động</option>
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
                <a href="{{ route('admin.cinemas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
