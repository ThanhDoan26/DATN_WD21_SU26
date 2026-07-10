@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-md-3">
        <div class="stat-box">
            <i class="fas fa-users" style="font-size: 2rem; color: #1e3c72;"></i>
            <div class="stat-number">{{ number_format($totalActiveUsers ?? 0) }}</div>
            <div class="stat-label">Users</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-box">
            <i class="fas fa-video" style="font-size: 2rem; color: #2a5298;"></i>
            <div class="stat-number">{{ number_format($totalMovies ?? 0) }}</div>
            <div class="stat-label">Movies</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-box">
            <i class="fas fa-building" style="font-size: 2rem; color: #1e3c72;"></i>
            <div class="stat-number">{{ number_format($totalCinemas ?? 0) }}</div>
            <div class="stat-label">Cinemas</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-box">
            <i class="fas fa-calendar-alt" style="font-size: 2rem; color: #2a5298;"></i>
            <div class="stat-number">{{ number_format($totalShowtimes ?? 0) }}</div>
            <div class="stat-label">Showtimes</div>
        </div>
    </div>

    <div class="col-md-3 mt-4">
        <div class="stat-box">
            <i class="fas fa-ticket-alt" style="font-size: 2rem; color: #1e3c72;"></i>
            <div class="stat-number">{{ number_format($totalTicketsSold ?? 0) }}</div>
            <div class="stat-label">Tickets Sold</div>
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

<!-- Top 5 Combos Widget -->
<div class="card mt-4">
    <div class="card-header bg-white">
        <h5 class="mb-0 text-primary"><i class="fas fa-star text-warning"></i> Top 5 Combo được yêu thích nhất</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="80" class="text-center">Hạng</th>
                        <th>Tên Combo</th>
                        <th width="150" class="text-center">Điểm TB</th>
                        <th width="150" class="text-center">Lượt ĐG</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topCombos ?? [] as $index => $combo)
                    <tr>
                        <td class="text-center">
                            @if($index == 0)
                                <i class="fas fa-medal text-warning fs-4"></i>
                            @elseif($index == 1)
                                <i class="fas fa-medal text-secondary fs-4"></i>
                            @elseif($index == 2)
                                <i class="fas fa-medal" style="color: #cd7f32; font-size: 1.5rem;"></i>
                            @else
                                <span class="fw-bold text-muted">{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td class="fw-bold">{{ $combo->name }}</td>
                        <td class="text-center text-warning fw-bold">
                            {{ number_format($combo->average_rating, 1) }} <i class="fas fa-star small"></i>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info text-dark">{{ $combo->total_reviews }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3">Chưa có đánh giá nào cho các Combo</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
