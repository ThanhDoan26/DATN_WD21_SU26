@extends('admin.layouts.app')

@section('title', 'Chi tiết phim - Admin')
@section('page_title', 'Chi tiết phim')

@section('content')
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.movies.index') }}">Movies</a></li>
            <li class="breadcrumb-item active">Chi tiết</li>
        </ol>
    </nav>
</div>

<div class="page-title">
    <h2><i class="fas fa-eye"></i> {{ $movie->title }}</h2>
</div>

<div class="card mb-4">
    <div class="row g-0 align-items-center">
        @if($movie->poster_url)
            <div class="col-md-4">
                <img src="{{ $movie->poster_url }}" alt="Poster {{ $movie->title }}" class="img-fluid rounded-start">
            </div>
        @endif
        <div class="col-md-8">
            <div class="card-body">
                <h5 class="card-title">{{ $movie->title }}</h5>
                <p class="card-text"><strong>Đạo diễn:</strong> {{ $movie->director ?: 'Không có' }}</p>
                <p class="card-text"><strong>Thời lượng:</strong> {{ intdiv($movie->duration, 60) }}h {{ $movie->duration % 60 }}m</p>
                <p class="card-text"><strong>Trạng thái:</strong> {{ ucfirst(strtolower(str_replace('_', ' ', $movie->status))) }}</p>
                <p class="card-text"><strong>Ngôn ngữ:</strong> {{ $movie->language ?: 'Không rõ' }}</p>
                <p class="card-text"><strong>Quốc gia:</strong> {{ $movie->country ?: 'Không rõ' }}</p>
                <p class="card-text"><strong>Cast:</strong> {{ $movie->cast ?: 'Không có thông tin' }}</p>
                <p class="card-text"><strong>Mô tả:</strong></p>
                <p class="card-text">{{ $movie->description ?: 'Không có mô tả.' }}</p>
                @if($movie->trailer_url)
                    <p class="card-text"><strong>Trailer:</strong> <a href="{{ $movie->trailer_url }}" target="_blank">Xem trailer</a></p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2">
    <a href="{{ route('admin.movies.edit', $movie->id) }}" class="btn btn-warning"><i class="fas fa-edit"></i> Chỉnh sửa</a>
    <a href="{{ route('admin.movies.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
</div>
@endsection
