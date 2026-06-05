@extends('admin.layouts.app')

@section('title', 'Quản Lý Đơn Hàng - Admin')
@section('page_title', 'Danh sách Đơn Hàng')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Bookings</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-shopping-cart"></i> Danh sách Đơn Hàng</h2>
        <p class="text-muted" style="margin-top: 5px; margin-bottom: 0;">Quản lý các đơn đặt vé trong hệ thống</p>
    </div>
    <div>
        <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Thêm Mới
        </a>
    </div>
</div>

<!-- Search & Filters Section -->
<div class="card mb-3">
    <div class="card-header">
        <i class="fas fa-filter"></i> Tìm Kiếm & Lọc
    </div>
    <div class="card-body">
        <form action="{{ route('admin.bookings.index') }}" method="GET" id="filterForm">
            <!-- Search Bar -->
            <div class="mb-3">
                <label for="search" class="form-label">Tìm Kiếm</label>
                <input type="text" class="form-control" id="search" name="search"
                       placeholder="Tìm mã đặt vé, tên khách hàng, email..."
                       value="{{ $filters['search'] ?? '' }}">
            </div>

            <!-- Status Filter Buttons -->
            <div class="mb-3">
                <label class="form-label d-block">Trạng Thái</label>
                <div class="btn-group" role="group">
                    <a href="{{ route('admin.bookings.index', array_merge(request()->except('status'), [])) }}"
                       class="btn btn-sm {{ !$filters['status'] ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="fas fa-list"></i> Tất Cả <span class="badge bg-secondary">{{ $statusCounts['all'] }}</span>
                    </a>
                    <a href="{{ route('admin.bookings.index', array_merge(request()->except('status'), ['status' => 'Paid'])) }}"
                       class="btn btn-sm {{ $filters['status'] === 'Paid' ? 'btn-success' : 'btn-outline-success' }}">
                        <i class="fas fa-check-circle"></i> Đã Thanh Toán <span class="badge bg-secondary">{{ $statusCounts['Paid'] }}</span>
                    </a>
                    <a href="{{ route('admin.bookings.index', array_merge(request()->except('status'), ['status' => 'Pending'])) }}"
                       class="btn btn-sm {{ $filters['status'] === 'Pending' ? 'btn-warning' : 'btn-outline-warning' }}">
                        <i class="fas fa-clock"></i> Chờ Xử Lý <span class="badge bg-secondary">{{ $statusCounts['Pending'] }}</span>
                    </a>
                    <a href="{{ route('admin.bookings.index', array_merge(request()->except('status'), ['status' => 'Used'])) }}"
                       class="btn btn-sm {{ $filters['status'] === 'Used' ? 'btn-info' : 'btn-outline-info' }}">
                        <i class="fas fa-check"></i> Đã Sử Dụng <span class="badge bg-secondary">{{ $statusCounts['Used'] }}</span>
                    </a>
                    <a href="{{ route('admin.bookings.index', array_merge(request()->except('status'), ['status' => 'Cancelled'])) }}"
                       class="btn btn-sm {{ $filters['status'] === 'Cancelled' ? 'btn-danger' : 'btn-outline-danger' }}">
                        <i class="fas fa-times-circle"></i> Đã Hủy <span class="badge bg-secondary">{{ $statusCounts['Cancelled'] }}</span>
                    </a>
                </div>
            </div>

            <!-- Advanced Filters (Collapsible) -->
            <div class="collapse mb-3" id="advancedFilters">
                <div class="card card-body border">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="from_date" class="form-label">Từ Ngày</label>
                                <input type="date" class="form-control" id="from_date" name="from_date"
                                       value="{{ $filters['from_date'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="to_date" class="form-label">Đến Ngày</label>
                                <input type="date" class="form-control" id="to_date" name="to_date"
                                       value="{{ $filters['to_date'] ?? '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Phương Thức Thanh Toán</label>
                                <select class="form-select" id="payment_method" name="payment_method">
                                    <option value="">-- Tất Cả --</option>
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method }}" {{ $filters['payment_method'] === $method ? 'selected' : '' }}>
                                            {{ $method }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="min_price" class="form-label">Giá Từ</label>
                                <input type="number" class="form-control" id="min_price" name="min_price"
                                       placeholder="0" value="{{ $filters['min_price'] ?? '' }}" step="1000">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="max_price" class="form-label">Giá Đến</label>
                                <input type="number" class="form-control" id="max_price" name="max_price"
                                       placeholder="{{ $priceStats->max_price ?? '0' }}" value="{{ $filters['max_price'] ?? '' }}" step="1000">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search"></i> Lọc
                        </button>
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-redo"></i> Đặt Lại
                        </a>
                    </div>
                </div>
            </div>

            <!-- Toggle Advanced Filters Button -->
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse"
                    data-bs-target="#advancedFilters" aria-expanded="false">
                <i class="fas fa-sliders-h"></i> Bộ Lọc Nâng Cao
            </button>

            <!-- Pagination Controls -->
            <div class="float-end">
                <label for="per_page" class="form-label d-inline me-2">Mỗi trang:</label>
                <select class="form-select d-inline w-auto" id="per_page" name="per_page" onchange="document.getElementById('filterForm').submit()">
                    <option value="10" {{ $filters['per_page'] == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $filters['per_page'] == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $filters['per_page'] == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $filters['per_page'] == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Bookings Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-table"></i> Danh sách Đơn Hàng
        </div>
        <div>
            <button class="btn btn-sm btn-outline-success" onclick="window.print()" title="In">
                <i class="fas fa-print"></i>
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>
                        <a href="{{ route('admin.bookings.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'booking_code', 'sort_order' => $filters['sort_order'] === 'asc' ? 'desc' : 'asc'])) }}"
                           class="text-decoration-none">
                            Mã Đặt Vé
                            @if($filters['sort_by'] === 'booking_code')
                                <i class="fas fa-arrow-{{ $filters['sort_order'] === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th>Khách Hàng</th>
                    <th>Suất Chiếu</th>
                    <th>Số Ghế</th>
                    <th>
                        <a href="{{ route('admin.bookings.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'total_price', 'sort_order' => $filters['sort_order'] === 'asc' ? 'desc' : 'asc'])) }}"
                           class="text-decoration-none">
                            Tổng Tiền
                            @if($filters['sort_by'] === 'total_price')
                                <i class="fas fa-arrow-{{ $filters['sort_order'] === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('admin.bookings.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'status', 'sort_order' => $filters['sort_order'] === 'asc' ? 'desc' : 'asc'])) }}"
                           class="text-decoration-none">
                            Trạng thái
                            @if($filters['sort_by'] === 'status')
                                <i class="fas fa-arrow-{{ $filters['sort_order'] === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th>Phương Thức Thanh Toán</th>
                    <th>
                        <a href="{{ route('admin.bookings.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'created_at', 'sort_order' => $filters['sort_order'] === 'asc' ? 'desc' : 'asc'])) }}"
                           class="text-decoration-none">
                            Thời Gian Đặt
                            @if($filters['sort_by'] === 'created_at')
                                <i class="fas fa-arrow-{{ $filters['sort_order'] === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings ?? [] as $booking)
                <tr class="booking-row-{{ strtolower($booking->status) }}">
                    <td><strong>#{{ $booking->id }}</strong></td>
                    <td>
                        <code>{{ $booking->booking_code }}</code>
                    </td>
                    <td>
                        {{ $booking->user->name ?? 'Khách lẻ' }}
                        @if($booking->user)
                            <br><small class="text-muted">{{ $booking->user->email }}</small>
                        @endif
                    </td>
                    <td>
                        <small>
                            {{ $booking->showtime->movie->title ?? 'N/A' }}<br>
                            <span class="text-muted">{{ $booking->showtime->start_time->format('d/m H:i') ?? 'N/A' }}</span>
                        </small>
                    </td>
                    <td>
                        <span class="badge bg-info">{{ $booking->bookedSeats->count() }} ghế</span>
                    </td>
                    <td>
                        <strong>{{ number_format($booking->total_price, 0, ',', '.') }}đ</strong>
                    </td>
                    <td>
                        @if($booking->status === 'Paid')
                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> Đã Thanh Toán</span>
                        @elseif($booking->status === 'Pending')
                            <span class="badge bg-warning"><i class="fas fa-clock"></i> Chờ Xử Lý</span>
                        @elseif($booking->status === 'Used')
                            <span class="badge bg-info"><i class="fas fa-check"></i> Đã Sử Dụng</span>
                        @elseif($booking->status === 'Cancelled')
                            <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Đã Hủy</span>
                        @endif
                    </td>
                    <td>
                        <small>{{ $booking->payment_method ?? 'N/A' }}</small>
                    </td>
                    <td>
                        <small class="text-muted">{{ $booking->created_at->format('d/m/Y H:i') }}</small>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="btn btn-sm btn-warning" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.bookings.destroy', $booking->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận xóa đơn hàng này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-4">
                        <i class="fas fa-inbox" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">Chưa có đơn hàng nào.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Info & Links -->
    <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            @if($bookings->count() > 0)
                Hiển thị {{ $bookings->firstItem() }}-{{ $bookings->lastItem() }} của {{ $bookings->total() }} đơn hàng
            @endif
        </div>
        <div>
            {{ $bookings->links() }}
        </div>
    </div>
</div>

<style>
/* Color-coded rows by status */
.booking-row-paid {
    background-color: #d4edda !important;
}

.booking-row-pending {
    background-color: #fff3cd !important;
}

.booking-row-used {
    background-color: #cfe2ff !important;
}

.booking-row-cancelled {
    background-color: #f8d7da !important;
}

/* Sortable column styling */
table thead th a {
    color: inherit;
    text-decoration: none;
    font-weight: inherit;
}

table thead th a:hover {
    color: #1e3c72;
    text-decoration: underline;
}

table thead th a i {
    margin-left: 5px;
    font-size: 0.85em;
}

/* Print styles */
@media print {
    .card-header, .card-body .mb-3, button, .btn-group, #advancedFilters {
        display: none;
    }

    table {
        font-size: 11px;
    }

    .booking-row-paid, .booking-row-pending, .booking-row-used, .booking-row-cancelled {
        background-color: white !important;
    }
}
</style>
@endsection
