@extends('admin.layouts.app')

@section('title', 'Edit Cinema - Admin')
@section('page_title', 'Edit Cinema')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.cinemas.index') }}">Cinemas</a></li>
            <li class="breadcrumb-item active">Edit</li>
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
        <i class="fas fa-pencil"></i> Cinema Information
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
                               id="name" name="name" value="{{ old('name', $cinema->name) }}" required>
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
                    <div class="mb-3">
                        <label for="city" class="form-label">Thành phố *</label>
                        <input type="text" class="form-control @error('city') is-invalid @enderror"
                               id="city" name="city" value="{{ old('city', $cinema->city) }}" required>
                        @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="phone" class="form-label">Điện thoại</label>
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
                            <option value="ACTIVE" {{ old('status', $cinema->status) === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                            <option value="INACTIVE" {{ old('status', $cinema->status) === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
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
