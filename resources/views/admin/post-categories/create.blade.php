@extends('admin.layouts.app')

@section('title', 'Thêm mới Danh mục Tin tức - Admin')
@section('page_title', 'Thêm mới Danh mục Tin tức')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.post-categories.index') }}">Danh mục Tin tức</a></li>
            <li class="breadcrumb-item active">Thêm mới</li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Thông tin Danh mục</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.post-categories.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="name" class="form-label fw-bold">Tên danh mục <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Ví dụ: Khuyến mãi, Tin điện ảnh..." required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label fw-bold">Mô tả danh mục</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Nhập mô tả ngắn gọn về chủ đề danh mục này...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="text-end mt-4">
                <a href="{{ route('admin.post-categories.index') }}" class="btn btn-secondary me-2"><i class="fas fa-times"></i> Hủy</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Tạo mới</button>
            </div>
        </form>
    </div>
</div>
@endsection
