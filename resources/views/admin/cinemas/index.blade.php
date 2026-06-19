@extends('admin.layouts.app')

@section('title', 'Cinemas - Admin')
@section('page_title', 'Cinemas Management')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Cinemas</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="fas fa-building"></i> Danh sách Rạp Chiếu Phim</h2>
        <p class="text-muted" style="margin-top: 5px; margin-bottom: 0;">Xem danh sách tất cả các cụm rạp trong hệ thống</p>
    </div>
    <div>
        <a href="{{ route('admin.cinemas.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm Rạp Mới</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div id="app">
    <cinema-manager></cinema-manager>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/app.js'])
@endpush
