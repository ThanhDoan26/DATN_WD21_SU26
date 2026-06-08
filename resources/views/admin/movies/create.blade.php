@extends('admin.layouts.app')

@section('title', 'Create Movie - Admin')
@section('page_title', 'Create New Movie')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.movies.index') }}">Movies</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title">
    <h2><i class="fas fa-plus-circle"></i> Thêm Phim Mới</h2>
</div>

<!-- Form Card -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-film"></i> Thông Tin Phim
    </div>
    <div class="card-body">
        <form action="{{ route('admin.movies.store') }}" method="POST">
            @csrf

            <!-- Title -->
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label">Tên Phim *</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="duration" class="form-label">Thời Lượng (phút) *</label>
                        <input type="number" class="form-control @error('duration') is-invalid @enderror"
                               id="duration" name="duration" value="{{ old('duration') }}" 
                               min="30" max="300" required>
                        @error('duration')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Director and Cast -->
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="director" class="form-label">Đạo Diễn</label>
                        <input type="text" class="form-control @error('director') is-invalid @enderror"
                               id="director" name="director" value="{{ old('director') }}">
                        @error('director')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="cast" class="form-label">Diễn Viên</label>
                        <input type="text" class="form-control @error('cast') is-invalid @enderror"
                               id="cast" name="cast" value="{{ old('cast') }}" placeholder="Ngăn cách bằng dấu phẩy">
                        @error('cast')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Language, Country, Age Rating -->
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="language" class="form-label">Ngôn Ngữ</label>
                        <input type="text" class="form-control @error('language') is-invalid @enderror"
                               id="language" name="language" value="{{ old('language') }}" placeholder="Ví dụ: Tiếng Việt">
                        @error('language')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="country" class="form-label">Nước Sản Xuất</label>
                        <input type="text" class="form-control @error('country') is-invalid @enderror"
                               id="country" name="country" value="{{ old('country') }}" placeholder="Ví dụ: Việt Nam">
                        @error('country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="age_rating" class="form-label">Phân Loại Độ Tuổi</label>
                        <input type="text" class="form-control @error('age_rating') is-invalid @enderror"
                               id="age_rating" name="age_rating" value="{{ old('age_rating') }}" placeholder="Ví dụ: PG-13">
                        @error('age_rating')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label for="description" class="form-label">Mô Tả</label>
                <textarea class="form-control @error('description') is-invalid @enderror"
                           id="description" name="description" rows="4" placeholder="Nhập nội dung phim...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- URLs -->
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="poster_url" class="form-label">URL Poster</label>
                        <input type="url" class="form-control @error('poster_url') is-invalid @enderror"
                               id="poster_url" name="poster_url" value="{{ old('poster_url') }}" 
                               placeholder="https://example.com/poster.jpg">
                        @error('poster_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="trailer_url" class="form-label">URL Trailer</label>
                        <input type="url" class="form-control @error('trailer_url') is-invalid @enderror"
                               id="trailer_url" name="trailer_url" value="{{ old('trailer_url') }}" 
                               placeholder="https://youtube.com/watch?v=...">
                        @error('trailer_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="status" class="form-label">Trạng Thái *</label>
                        <select class="form-select @error('status') is-invalid @enderror"
                                id="status" name="status" required>
                            <option value="">-- Chọn trạng thái --</option>
                            <option value="ACTIVE" {{ old('status') === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                            <option value="COMING_SOON" {{ old('status') === 'COMING_SOON' ? 'selected' : '' }}>Coming Soon</option>
                            <option value="INACTIVE" {{ old('status') === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <hr>

            <!-- Submit Buttons -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Thêm Phim
                </button>
                <a href="{{ route('admin.movies.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
