@extends('admin.layouts.app')

@section('title', 'Booking Detail - Admin')
@section('page_title', 'Chi Tiết Đơn Hàng')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
            <li class="breadcrumb-item active">{{ $booking->booking_code }}</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-receipt"></i> Chi Tiết Đơn Hàng</h2>
        <p class="text-muted" style="margin-top: 5px; margin-bottom: 0;">{{ $booking->booking_code }}</p>
    </div>
    <div>
        <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit"></i> Sửa
        </a>
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Quay Lại
        </a>
    </div>
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Booking Info -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Thông Tin Đơn Hàng
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Mã Đặt Vé</label>
                        <p><strong>{{ $booking->booking_code }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Trạng thái</label>
                        <p>
                            @if($booking->status === 'Paid')
                                <span class="badge bg-success"><i class="fas fa-check-circle"></i> Đã Thanh Toán</span>
                            @elseif($booking->status === 'Pending')
                                <span class="badge bg-warning"><i class="fas fa-clock"></i> Chờ Xử Lý</span>
                            @elseif($booking->status === 'Used')
                                <span class="badge bg-info"><i class="fas fa-check"></i> Đã Sử Dụng</span>
                            @elseif($booking->status === 'Cancelled')
                                <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Đã Hủy</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Thời Gian Đặt</label>
                        <p><strong>{{ $booking->created_at->format('d/m/Y H:i') }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Ghi Chú</label>
                        <p>{{ $booking->notes ?? 'Không có' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Movie & Showtime Info -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="fas fa-film"></i> Thông Tin Suất Chiếu
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="text-muted small">Phim</label>
                        <p><strong>{{ $booking->showtime->movie->title }}</strong></p>
                        <small class="text-muted">{{ $booking->showtime->movie->description }}</small>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Thời Lượng</label>
                        <p><strong>{{ $booking->showtime->movie->duration }} phút</strong></p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <label class="text-muted small">Thời Gian Chiếu</label>
                        <p><strong>{{ $booking->showtime->start_time->format('d/m/Y H:i') }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Phòng Chiếu</label>
                        <p><strong>{{ $booking->showtime->room->name }}</strong> ({{ $booking->showtime->room->format }})</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seat Map Section -->
        @include('admin.bookings.partials.seat-map-legend')
        @include('admin.bookings.partials.seat-map-grid')

        <!-- Booked Seats Table -->
        @include('admin.bookings.partials.booked-seats-table')
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Customer Info -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="fas fa-user"></i> Thông Tin Khách Hàng
            </div>
            <div class="card-body">
                @if($booking->user)
                    <p><strong>{{ $booking->user->name }}</strong></p>
                    <p class="small text-muted">{{ $booking->user->email }}</p>
                    <p class="small text-muted">{{ $booking->user->phone }}</p>
                @else
                    <p class="text-muted"><em>Khách hàng lẻ (không có tài khoản)</em></p>
                @endif
            </div>
        </div>

        <!-- Payment Info -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="fas fa-credit-card"></i> Thông Tin Thanh Toán
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Phương Thức</label>
                    <p><strong>{{ $booking->payment_method ?? 'Chưa xác định' }}</strong></p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Tổng Tiền</label>
                    <p><strong class="h5">{{ number_format($booking->total_price, 0, ',', '.') }}đ</strong></p>
                </div>

                @if($booking->payment_time)
                <div class="mb-3">
                    <label class="text-muted small">Thời Gian Thanh Toán</label>
                    <p>{{ $booking->payment_time->format('d/m/Y H:i') }}</p>
                </div>
                @endif

                @if($booking->cancelled_at)
                <div class="mb-3">
                    <label class="text-muted small">Thời Gian Hủy</label>
                    <p>{{ $booking->cancelled_at->format('d/m/Y H:i') }}</p>
                </div>
                @endif

                @if($booking->cancellation_reason)
                <div class="mb-3">
                    <label class="text-muted small">Lý Do Hủy</label>
                    <p class="alert alert-warning mb-0">{{ $booking->cancellation_reason }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-cog"></i> Hành Động
            </div>
            <div class="card-body">
                <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="btn btn-warning btn-sm w-100 mb-2">
                    <i class="fas fa-edit"></i> Chỉnh Sửa
                </a>
                <form action="{{ route('admin.bookings.destroy', $booking->id) }}" method="POST" onsubmit="return confirm('Xác nhận xóa đơn hàng này?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
