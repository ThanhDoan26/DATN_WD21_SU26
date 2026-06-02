@extends('admin.layouts.app')

@section('title', 'Users - Admin')
@section('page_title', 'Users Management')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Users</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title">
    <div>
        <h2><i class="fas fa-users"></i> Danh sách Người Dùng</h2>
        <p class="text-muted" style="margin-top: 5px;">Quản lý tài khoản người dùng</p>
    </div>
</div>

<!-- Coming Soon -->
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-user-circle" style="font-size: 3rem; color: #ccc;"></i>
        <h5 class="mt-3">Coming Soon</h5>
        <p class="text-muted">Tính năng quản lý người dùng sẽ sớm được cập nhật</p>
    </div>
</div>
@endsection
