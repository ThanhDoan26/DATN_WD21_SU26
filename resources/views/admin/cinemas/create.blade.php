@extends('admin.layouts.app')

@section('title', 'Create Cinema - Admin')
@section('page_title', 'Create New Cinema')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Bảng điều khiển</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.cinemas.index') }}">Rạp</a></li>
            <li class="breadcrumb-item active">Thêm mới</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title">
    <h2><i class="fas fa-building"></i> Thêm Rạp Mới</h2>
</div>

<!-- Form Card -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-plus-circle"></i> Thông tin rạp
    </div>
    <div class="card-body">
        <form action="{{ route('admin.cinemas.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên Rạp *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email') }}">
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
                               id="address" name="address" value="{{ old('address') }}" required>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="city" class="form-label">Tỉnh / Thành phố *</label>
                        <select class="form-select @error('city') is-invalid @enderror"
                                id="city" name="city" required>
                            <option value="">-- Chọn Tỉnh / Thành phố --</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province }}" {{ old('city') === $province ? 'selected' : '' }}>
                                    {{ $province }}
                                </option>
                            @endforeach
                        </select>
                        @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="phone" class="form-label">Hotline</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                id="phone" name="phone" value="{{ old('phone') }}">
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
                            <option value="ACTIVE" {{ old('status') === 'ACTIVE' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="INACTIVE" {{ old('status') === 'INACTIVE' ? 'selected' : '' }}>Không hoạt động</option>
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
                <a href="{{ route('admin.cinemas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
