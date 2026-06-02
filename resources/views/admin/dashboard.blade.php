@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-md-3">
        <div class="stat-box">
            <i class="fas fa-building" style="font-size: 2rem; color: #1e3c72;"></i>
            <div class="stat-number">{{ $totalCinemas ?? 0 }}</div>
            <div class="stat-label">Cinemas</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-box">
            <i class="fas fa-door-open" style="font-size: 2rem; color: #2a5298;"></i>
            <div class="stat-number">{{ $totalRooms ?? 0 }}</div>
            <div class="stat-label">Rooms</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-box">
            <i class="fas fa-video" style="font-size: 2rem; color: #1e3c72;"></i>
            <div class="stat-number">{{ $totalMovies ?? 0 }}</div>
            <div class="stat-label">Movies</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-box">
            <i class="fas fa-ticket-alt" style="font-size: 2rem; color: #2a5298;"></i>
            <div class="stat-number">{{ $totalBookings ?? 0 }}</div>
            <div class="stat-label">Bookings</div>
        </div>
    </div>
</div>

<!-- Welcome Message -->
<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title"><i class="fas fa-info-circle"></i> Welcome to Admin Panel</h5>
        <p class="card-text">
            Hệ thống Quản lý Đặt vé Xem phim. Sử dụng menu bên trái để quản lý:
        </p>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <strong><i class="fas fa-building"></i> Cinemas</strong> - Quản lý cụm rạp chiếu phim
            </li>
            <li class="list-group-item">
                <strong><i class="fas fa-door-open"></i> Rooms</strong> - Quản lý phòng chiếu
            </li>
            <li class="list-group-item">
                <strong><i class="fas fa-chair"></i> Seats</strong> - Quản lý sơ đồ ghế ngồi
            </li>
            <li class="list-group-item">
                <strong><i class="fas fa-video"></i> Movies</strong> - Quản lý danh sách phim
            </li>
            <li class="list-group-item">
                <strong><i class="fas fa-calendar-alt"></i> Showtimes</strong> - Quản lý lịch chiếu
            </li>
            <li class="list-group-item">
                <strong><i class="fas fa-ticket-alt"></i> Bookings</strong> - Quản lý đơn hàng
            </li>
            <li class="list-group-item">
                <strong><i class="fas fa-users"></i> Users</strong> - Quản lý người dùng
            </li>
        </ul>
    </div>
</div>
@endsection
