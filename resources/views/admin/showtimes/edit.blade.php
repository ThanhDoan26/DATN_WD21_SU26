@extends('admin.layouts.app')

@section('title', 'Edit Showtime - Admin')
@section('page_title', 'Chỉnh Sửa Suất Chiếu')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.showtimes.index') }}">Showtimes</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<div class="page-title">
    <h2><i class="fas fa-edit"></i> Chỉnh Sửa Suất Chiếu</h2>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-clock"></i> Thông Tin Suất Chiếu
    </div>
    <div class="card-body">
        <form action="{{ route('admin.showtimes.update', $showtime->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="movie_id" class="form-label">Phim *</label>
                        <select id="movie_id" name="movie_id" class="form-select @error('movie_id') is-invalid @enderror" required>
                            <option value="">-- Chọn phim --</option>
                            @foreach($movies as $movie)
                                <option value="{{ $movie->id }}" {{ old('movie_id', $showtime->movie_id) == $movie->id ? 'selected' : '' }}>
                                    {{ $movie->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('movie_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="room_id" class="form-label">Phòng Chiếu *</label>
                        <select id="room_id" name="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
                            <option value="">-- Chọn phòng --</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" {{ old('room_id', $showtime->room_id) == $room->id ? 'selected' : '' }}>
                                    {{ $room->cinema->name }} / {{ $room->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('room_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="start_time" class="form-label">Thời Gian Bắt Đầu *</label>
                        <input type="datetime-local" id="start_time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time', $showtime->start_time->format('Y-m-d\TH:i')) }}" required>
                        @error('start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="end_time" class="form-label">Thời Gian Kết Thúc *</label>
                        <input type="datetime-local" id="end_time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time', $showtime->end_time->format('Y-m-d\TH:i')) }}" required>
                        @error('end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="status" class="form-label">Trạng Thái *</label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="">-- Chọn trạng thái --</option>
                            @foreach(\App\Models\Showtime::STATUSES as $status)
                                <option value="{{ $status }}" {{ old('status', $showtime->status) == $status ? 'selected' : '' }}>
                                    {{ ucfirst(strtolower($status)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu Thay Đổi
                </button>
                <a href="{{ route('admin.showtimes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay Lại
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
