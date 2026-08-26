@extends('layouts.manager')

@section('title', 'Quản Lý Phòng Chiếu')
@section('page_title', 'Danh Sách Phòng Chiếu')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('manager.dashboard') }}" class="text-decoration-none text-muted fw-semibold">Dashboard</a></li>
            <li class="breadcrumb-item active text-emerald font-sora fw-bold">Phòng Chiếu</li>
        </ol>
    </nav>
</div>

<!-- Page Title & Header Actions -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="fw-extrabold text-ink font-sora mb-1 fs-3"><i class="fas fa-door-open text-emerald me-2"></i>Danh sách Phòng Chiếu</h2>
        <p class="text-muted small mb-0">Quản lý và thiết kế sơ đồ các phòng chiếu thuộc rạp của bạn</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('manager.rooms.trashed') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 font-sora fw-bold fs-7 shadow-sm" title="Xem phòng đã xóa">
            <i class="fas fa-trash-alt me-1"></i> Đã Xóa
        </a>
        <a href="{{ route('manager.rooms.create') }}" class="btn btn-emerald rounded-pill px-4 py-2 font-sora fw-bold fs-7 shadow-sm">
            <i class="fas fa-plus-circle me-1"></i> Thêm Phòng Chiếu
        </a>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: var(--bg-surface);">
    <div class="card-body p-4">
        <div class="d-flex align-items-center mb-3 text-emerald font-sora fw-bold">
            <i class="fas fa-sliders-h me-2"></i>Bộ Lọc & Tìm Kiếm Phòng Chiếu
        </div>
        <form action="{{ route('manager.rooms.index') }}" method="GET" class="row align-items-center g-3">
            <div class="col-12 col-md-6">
                <div class="input-group shadow-sm rounded-3 overflow-hidden border border-light">
                    <span class="input-group-text border-0 bg-emerald-subtle text-emerald px-3" style="background: rgba(16, 185, 129, 0.1);"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control border-0 py-2 font-sora" placeholder="Nhập tên phòng chiếu để tìm kiếm..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select name="status" class="form-select border border-light py-2 font-sora fw-semibold shadow-sm rounded-3">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>Hoạt động (Active)</option>
                    <option value="INACTIVE" {{ request('status') == 'INACTIVE' ? 'selected' : '' }}>Không HĐ (Inactive)</option>
                    <option value="MAINTENANCE" {{ request('status') == 'MAINTENANCE' ? 'selected' : '' }}>Bảo Trì</option>
                    <option value="CLOSED" {{ request('status') == 'CLOSED' ? 'selected' : '' }}>Đóng Cửa</option>
                </select>
            </div>
            <div class="col-12 col-md-auto d-flex gap-2 ms-auto">
                <button type="submit" class="btn btn-emerald font-sora fw-bold px-4 py-2 rounded-3 shadow-sm"><i class="fas fa-filter me-1"></i> Áp dụng</button>
                @if((request()->has('search') && request('search') != '') || (request()->has('status') && request('status') != ''))
                    <a href="{{ route('manager.rooms.index') }}" class="btn btn-light border font-sora fw-semibold px-3 py-2 rounded-3">Đặt lại</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Rooms Table Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--bg-surface);">
    <div class="card-header bg-transparent border-bottom border-light p-4 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-extrabold font-sora text-ink"><i class="fas fa-th-list me-2 text-emerald"></i>Danh sách Phòng Chiếu</h5>
        <span class="badge bg-emerald-subtle text-emerald px-3 py-2 rounded-pill font-sora fw-extrabold" style="background-color: rgba(16, 185, 129, 0.1); color: #059669;">
            Tổng: {{ $rooms->count() ?? 0 }} phòng
        </span>
    </div>

    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th class="ps-4" style="width: 70px;">ID</th>
                    <th>Tên Phòng</th>
                    <th>Định Dạng</th>
                    <th>Tổng Ghế</th>
                    <th>Trạng Thái</th>
                    <th>Suất Chiếu</th>
                    <th>Ngày Tạo</th>
                    <th class="pe-4 text-end" style="width: 140px;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rooms ?? [] as $room)
                <tr>
                    <td class="ps-4 font-sora fw-bold text-muted">#{{ $room->id }}</td>
                    <td>
                        <div class="font-sora fw-bold text-ink fs-6 mb-0">{{ $room->name }}</div>
                    </td>
                    <td>
                        @php
                            $fmt = strtoupper($room->format ?? '2D');
                            $badgeClass = str_contains($fmt, 'IMAX') ? 'badge-format-imax' : (str_contains($fmt, '3D') ? 'badge-format-3d' : 'badge-format-2d');
                        @endphp
                        <span class="{{ $badgeClass }}">{{ $fmt }}</span>
                    </td>
                    <td>
                        <span class="font-sora fw-extrabold text-ink fs-6">{{ $room->total_seats ?? 0 }}</span> <small class="text-muted">ghế</small>
                    </td>
                    <td>
                        @if($room->status === 'ACTIVE')
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill font-sora fw-bold">
                                <i class="fas fa-check-circle me-1"></i>Hoạt động
                            </span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill font-sora fw-bold">
                                <i class="fas fa-times-circle me-1"></i>Tạm dừng
                            </span>
                        @endif
                    </td>
                    <td>
                        @php
                            $activeShowtimes = $room->getActiveShowtimesCount();
                        @endphp
                        @if($activeShowtimes > 0)
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 rounded-pill font-sora fw-bold">
                                <i class="fas fa-film me-1"></i>{{ $activeShowtimes }} suất
                            </span>
                        @else
                            <span class="badge bg-light text-muted border px-2 py-1 small">Không</span>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted font-sora">{{ $room->created_at ? $room->created_at->format('d/m/Y H:i') : 'N/A' }}</small>
                    </td>
                    <td class="pe-4 text-end">
                        <div class="btn-action-group">
                            <a href="{{ route('manager.rooms.show', $room->id) }}" class="btn-action btn-action-view" title="Xem Sơ Đồ Ghế">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('manager.rooms.edit', $room->id) }}" class="btn-action btn-action-edit" title="Chỉnh Sửa Thấu Động">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                            <form action="{{ route('manager.rooms.destroy', $room->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-action-delete"
                                        title="{{ $activeShowtimes > 0 ? 'Phòng đang có suất chiếu, không thể xóa' : 'Xóa Phòng' }}"
                                        @if($activeShowtimes > 0) disabled style="opacity: 0.4; cursor: not-allowed;" @endif
                                        @if($activeShowtimes == 0) onclick="return confirm('Xác nhận xóa phòng chiếu này?');" @endif>
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="fas fa-door-closed text-muted opacity-50 mb-3" style="font-size: 3.5rem;"></i>
                        <p class="text-muted font-sora fs-6">Chưa tìm thấy phòng chiếu nào phù hợp.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('extra_css')
<style>
    .btn-emerald {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff;
        border: none;
    }
    .btn-emerald:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: #ffffff;
    }
    .text-emerald {
        color: #10b981 !important;
    }
</style>
@endsection
