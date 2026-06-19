@extends('admin.layouts.app')

@section('title', 'Rooms - Admin')
@section('page_title', 'Rooms Management')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Rooms</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-door-open"></i> Danh sách Phòng Chiếu</h2>
        <p class="text-muted" style="margin-top: 5px; margin-bottom: 0;">Xem danh sách tất cả các phòng chiếu trong hệ thống</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.rooms.trashed') }}" class="btn btn-secondary" title="Xem phòng đã xóa">
            <i class="fas fa-trash"></i> Đã Xóa
        </a>
        <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Thêm Mới
        </a>
    </div>
</div>

<!-- Alert Messages -->
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Search Form -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body p-3">
        <form action="{{ route('admin.rooms.index') }}" method="GET" class="row align-items-center g-3">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Nhập tên phòng hoặc tên rạp để tìm kiếm..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>Hoạt động (Active)</option>
                    <option value="INACTIVE" {{ request('status') == 'INACTIVE' ? 'selected' : '' }}>Không HĐ (Inactive)</option>
                    <option value="MAINTENANCE" {{ request('status') == 'MAINTENANCE' ? 'selected' : '' }}>Bảo Trì</option>
                    <option value="CLOSED" {{ request('status') == 'CLOSED' ? 'selected' : '' }}>Đóng Cửa</option>
                </select>
            </div>
            <div class="col-12 col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Lọc</button>
                @if((request()->has('search') && request('search') != '') || (request()->has('status') && request('status') != ''))
                    <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-secondary">Xóa bộ lọc</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Rooms Table -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-table"></i> Danh sách Phòng Chiếu
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên Phòng</th>
                    <th>Rạp</th>
                    <th>Format</th>
                    <th>Tổng Ghế</th>
                    <th>Trạng thái</th>
                    <th>Suất Chiếu</th>
                    <th>Tạo lúc</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rooms ?? [] as $room)
                <tr>
                    <td><strong>#{{ $room->id }}</strong></td>
                    <td>
                        <strong>{{ $room->name }}</strong>
                    </td>
                    <td>
                        <span class="badge bg-secondary">{{ $room->cinema->name ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <span class="badge bg-info">{{ $room->format }}</span>
                    </td>
                    <td>
                        <strong>{{ $room->total_seats ?? 0 }}</strong> ghế
                    </td>
                    <td>
                        @if($room->status === 'ACTIVE')
                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> Active</span>
                        @else
                            <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Inactive</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $activeShowtimes = $room->getActiveShowtimesCount();
                        @endphp
                        @if($activeShowtimes > 0)
                            <span class="badge bg-warning"><i class="fas fa-film"></i> {{ $activeShowtimes }} suất</span>
                        @else
                            <span class="badge bg-secondary">Không</span>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted">{{ $room->created_at->format('d/m/Y H:i') }}</small>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.rooms.show', $room->id) }}" class="btn btn-sm btn-info text-white" title="Chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.rooms.edit', $room->id) }}" class="btn btn-sm btn-warning" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.rooms.destroy', $room->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                        title="{{ $activeShowtimes > 0 ? 'Phòng đang có suất chiếu, không thể xóa' : 'Xóa' }}"
                                        @if($activeShowtimes > 0) disabled @endif
                                        @if($activeShowtimes == 0) onclick="return confirm('Xác nhận xóa phòng này?');" @endif>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <i class="{{ request('search') ? 'fas fa-search-minus' : 'fas fa-inbox' }}" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">{{ request('search') ? 'Không tìm thấy phòng/rạp nào phù hợp với từ khóa đo.' : 'Chưa có phòng chiếu nào trong hệ thống.' }}</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
