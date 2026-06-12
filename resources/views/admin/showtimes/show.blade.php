@extends('admin.layouts.app')

@section('title', 'Showtime Details - Admin')
@section('page_title', 'Chi tiết Suất Chiếu')

@section('content')
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.showtimes.index') }}">Showtimes</a></li>
            <li class="breadcrumb-item active">Chi tiết</li>
        </ol>
    </nav>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h3>{{ $showtime->movie->title }}</h3>
        <p class="text-muted mb-0">Rạp: {{ $showtime->room->cinema->name }}</p>
        <p class="text-muted">Phòng: {{ $showtime->room->name }}</p>

        <hr>

        <div class="row g-3">
            <div class="col-md-4">
                <strong>Ngày giờ</strong>
                <p>{{ $showtime->start_time->format('d/m/Y H:i') }} - {{ $showtime->end_time->format('d/m/Y H:i') }}</p>
            </div>
            <div class="col-md-4">
                <strong>Trạng thái</strong>
                <p>{{ ucfirst(strtolower($showtime->status)) }}</p>
            </div>
            <div class="col-md-4">
                <strong>Thời lượng</strong>
                <p>{{ $showtime->movie->duration ?? 'N/A' }} phút</p>
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ route('admin.showtimes.edit', $showtime->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Sửa
            </a>
            <a href="{{ route('admin.showtimes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
</div>
@endsection
