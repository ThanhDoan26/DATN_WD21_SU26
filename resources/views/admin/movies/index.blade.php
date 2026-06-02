@extends('admin.layouts.app')

@section('title', 'Movies - Admin')
@section('page_title', 'Movies Management')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Movies</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title">
    <div>
        <h2><i class="fas fa-video"></i> Danh sách Phim</h2>
        <p class="text-muted" style="margin-top: 5px;">Quản lý danh sách phim trong hệ thống</p>
    </div>
    <div class="btn-group">
        <a href="#" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm Phim Mới
        </a>
    </div>
</div>

<!-- Coming Soon -->
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-film" style="font-size: 3rem; color: #ccc;"></i>
        <h5 class="mt-3">Coming Soon</h5>
        <p class="text-muted">Tính năng quản lý phim sẽ sớm được cập nhật</p>
    </div>
</div>
@endsection
