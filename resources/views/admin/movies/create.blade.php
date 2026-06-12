@extends('admin.layouts.app')

@section('title', 'Thêm phim - Admin')
@section('page_title', 'Thêm phim mới')

@section('content')
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.movies.index') }}">Movies</a></li>
            <li class="breadcrumb-item active">Thêm</li>
        </ol>
    </nav>
</div>

<div class="page-title">
    <h2><i class="fas fa-plus-circle"></i> Thêm phim mới</h2>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-film"></i> Thông tin phim
    </div>
    <div class="card-body">
        <form action="{{ route('admin.movies.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tên phim *</label>
                        <input type="text" name="title" value="{{ old('title') }}" class="form-control  @error('title') is-invalid @enderror" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Đạo diễn</label>
                        <input type="text" name="director" value="{{ old('director') }}" class="form-control @error('director') is-invalid @enderror">
                        @error('director')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Mô tả</label>
                <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Diễn Viên</label>
                        <textarea name="cast" rows="3" class="form-control @error('cast') is-invalid @enderror">{{ old('cast') }}</textarea>
                        @error('cast')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Poster URL</label>
                        <input type="url" name="poster_url" value="{{ old('poster_url') }}" class="form-control @error('poster_url') is-invalid @enderror">
                        @error('poster_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Trailer URL</label>
                        <input type="url" name="trailer_url" value="{{ old('trailer_url') }}" class="form-control @error('trailer_url') is-invalid @enderror">
                        @error('trailer_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Thời lượng (phút) *</label>
                        <input type="number" name="duration" min="1" value="{{ old('duration') }}" class="form-control @error('duration') is-invalid @enderror" required>
                        @error('duration')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Xếp hạng tuổi</label>
                        <input type="text" name="age_rating" value="{{ old('age_rating') }}" class="form-control @error('age_rating') is-invalid @enderror">
                        @error('age_rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Trạng thái *</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="">-- Chọn --</option>
                            @foreach(App\Models\Movie::STATUSES as $status)
                                <option value="{{ $status }}" {{ old('status') === $status ? 'selected' : '' }}>{{ ucfirst(strtolower(str_replace('_', ' ', $status))) }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Ngôn ngữ</label>
                        <input type="text" name="language" value="{{ old('language') }}" class="form-control @error('language') is-invalid @enderror">
                        @error('language')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Quốc gia</label>
                        <input type="text" name="country" value="{{ old('country') }}" class="form-control @error('country') is-invalid @enderror">
                        @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu phim</button>
                <a href="{{ route('admin.movies.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection
