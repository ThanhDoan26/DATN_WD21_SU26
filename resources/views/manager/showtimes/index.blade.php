@extends('layouts.manager')

@section('title', 'Quản Lý Suất Chiếu')
@section('page_title', 'Danh Sách Suất Chiếu')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('manager.dashboard') }}" class="text-decoration-none text-muted fw-semibold">Dashboard</a></li>
            <li class="breadcrumb-item active text-emerald font-sora fw-bold">Suất Chiếu</li>
        </ol>
    </nav>
</div>

<!-- Page Title & Actions -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="fw-extrabold text-ink font-sora mb-1 fs-3"><i class="fas fa-calendar-alt text-emerald me-2"></i>Quản Lý Lịch Chiếu</h2>
        <p class="text-muted small mb-0">Lên lịch chiếu phim và theo dõi suất chiếu theo từng phòng</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('manager.showtimes.trashed') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 font-sora fw-bold fs-7 shadow-sm" title="Xem lịch chiếu đã xóa">
            <i class="fas fa-trash-alt me-1"></i> Thùng Rác
        </a>
        <a href="{{ route('manager.showtimes.create') }}" class="btn btn-emerald rounded-pill px-4 py-2 font-sora fw-bold fs-7 shadow-sm">
            <i class="fas fa-plus-circle me-1"></i> Thêm Lịch Chiếu
        </a>
    </div>
</div>

<!-- Search & Filter Form -->
<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: var(--bg-surface);">
    <div class="card-body p-4">
        <div class="d-flex align-items-center mb-3 text-emerald font-sora fw-bold">
            <i class="fas fa-sliders-h me-2"></i>Bộ Lọc Lịch Chiếu Theo Tiêu Chí
        </div>
        <form method="GET" action="{{ route('manager.showtimes.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label font-sora fw-bold small text-muted">Phim Chiếu</label>
                    <select name="movie_id" class="form-select font-sora fw-semibold border-light rounded-3 py-2 shadow-sm">
                        <option value="">-- Tất cả phim --</option>
                        @foreach($movies as $movie)
                            <option value="{{ $movie->id }}" {{ request('movie_id') == $movie->id ? 'selected' : '' }}>{{ $movie->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label font-sora fw-bold small text-muted">Phòng Chiếu</label>
                    <select name="room_id" class="form-select font-sora fw-semibold border-light rounded-3 py-2 shadow-sm">
                        <option value="">-- Tất cả phòng --</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                {{ $room->name }} ({{ $room->format ?? '2D' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label font-sora fw-bold small text-muted">Trạng Thái</label>
                    <select name="status" class="form-select font-sora fw-semibold border-light rounded-3 py-2 shadow-sm">
                        <option value="">-- Tất cả trạng thái --</option>
                        @foreach(\App\Models\Showtime::STATUSES as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ \App\Models\Showtime::STATUS_LABELS[$status] ?? ucfirst(strtolower($status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-emerald font-sora fw-bold w-100 py-2 rounded-3 shadow-sm"><i class="fas fa-filter me-1"></i>Lọc</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--bg-surface);">
    <div class="card-header bg-transparent border-bottom border-light p-4 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-extrabold font-sora text-ink"><i class="fas fa-list text-emerald me-2"></i>Danh Sách Lịch Chiếu</h5>
        <span class="badge bg-emerald-subtle text-emerald px-3 py-2 rounded-pill font-sora fw-extrabold" style="background-color: rgba(16, 185, 129, 0.1); color: #059669;">
            Tổng: {{ $showtimes->total() ?? 0 }} suất
        </span>
    </div>
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th class="ps-4" style="width: 70px;">STT</th>
                    <th>Phim Chiếu</th>
                    <th>Phòng Chiếu</th>
                    <th>Thời Gian Chiếu</th>
                    <th>Trạng Thái</th>
                    <th class="pe-4 text-end" style="width: 120px;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($showtimes as $showtime)
                    <tr>
                        <td class="ps-4 font-sora fw-bold text-muted">#{{ $showtimes->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="font-sora fw-extrabold text-ink fs-6 mb-1">{{ $showtime->movie->title }}</div>
                            <small class="text-muted font-sora"><i class="fas fa-clock text-amber me-1"></i>{{ $showtime->movie->duration }} phút</small>
                        </td>
                        <td>
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1 rounded-pill font-sora fw-bold">
                                <i class="fas fa-door-open me-1"></i>{{ $showtime->room?->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <div class="font-sora fw-extrabold text-ink fs-6 mb-1">{{ $showtime->start_time->format('H:i') }} - {{ $showtime->end_time->format('H:i') }}</div>
                            <small class="text-muted font-sora"><i class="fas fa-calendar me-1"></i>{{ $showtime->start_time->format('d/m/Y') }}</small>
                        </td>
                        <td>
                            @if($showtime->status === \App\Models\Showtime::STATUS_SCHEDULED)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill font-sora fw-bold">
                                    <i class="fas fa-calendar-check me-1"></i>Sắp chiếu
                                </span>
                            @elseif($showtime->status === \App\Models\Showtime::STATUS_ONGOING)
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 rounded-pill font-sora fw-bold">
                                    <i class="fas fa-play-circle me-1"></i>Đang chiếu
                                </span>
                            @elseif($showtime->status === \App\Models\Showtime::STATUS_COMPLETED)
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1 rounded-pill font-sora fw-bold">
                                    <i class="fas fa-check-double me-1"></i>Đã kết thúc
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill font-sora fw-bold">
                                    <i class="fas fa-ban me-1"></i>Đã hủy
                                </span>
                            @endif
                        </td>
                        <td class="pe-4 text-end">
                            <div class="btn-action-group">
                                <a href="{{ route('manager.showtimes.edit', $showtime) }}" class="btn-action btn-action-edit" title="Chỉnh Sửa Suất Chiếu">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <form action="{{ route('manager.showtimes.destroy', $showtime) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa suất chiếu này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-delete" title="Xóa Suất Chiếu">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-calendar-times text-muted opacity-50 mb-3" style="font-size: 3.5rem;"></i>
                            <p class="text-muted font-sora fs-6">Chưa tìm thấy suất chiếu nào phù hợp.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($showtimes->hasPages())
        <div class="card-footer bg-transparent border-top border-light p-3 d-flex justify-content-center">
            {{ $showtimes->links('pagination::bootstrap-5') }}
        </div>
    @endif
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
