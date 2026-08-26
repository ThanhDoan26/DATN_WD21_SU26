@extends('layouts.manager')

@section('title', 'Quản Lý Phim')
@section('page_title', 'Danh Sách Phim Chiếu Rạp')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('manager.dashboard') }}" class="text-decoration-none text-muted fw-semibold">Dashboard</a></li>
            <li class="breadcrumb-item active text-emerald font-sora fw-bold">Danh Sách Phim</li>
        </ol>
    </nav>
</div>

<!-- Page Title & Header Actions -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="fw-extrabold text-ink font-sora mb-1 fs-3"><i class="fas fa-film text-emerald me-2"></i>Danh Sách Phim Chiếu Rạp</h2>
        <p class="text-muted small mb-0">Theo dõi thông tin và thời lượng tất cả các phim thuộc hệ thống</p>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--bg-surface);">
    <div class="card-body p-4">
        <form action="{{ route('manager.movies.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label font-sora fw-bold small text-muted">Tìm kiếm phim</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0 bg-transparent text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Nhập tên phim cần tìm..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label font-sora fw-bold small text-muted">Thể loại</label>
                <select name="category_id" class="form-select font-sora fw-semibold">
                    <option value="">-- Tất cả thể loại --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label font-sora fw-bold small text-muted">Trạng thái chiếu</label>
                <select name="status" class="form-select font-sora fw-semibold">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="COMING_SOON" {{ request('status') == 'COMING_SOON' ? 'selected' : '' }}>Sắp chiếu</option>
                    <option value="NOW_SHOWING" {{ request('status') == 'NOW_SHOWING' ? 'selected' : '' }}>Đang chiếu</option>
                    <option value="ENDED" {{ request('status') == 'ENDED' ? 'selected' : '' }}>Ngưng chiếu</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-emerald font-sora fw-bold w-100"><i class="fas fa-filter me-1"></i> Lọc kết quả</button>
            </div>
        </form>
    </div>
</div>

<!-- Movies Table Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--bg-surface);">
    <div class="card-header bg-transparent border-bottom border-light p-4 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-extrabold font-sora text-ink"><i class="fas fa-video me-2 text-emerald"></i>Danh Sách Phim</h5>
        <span class="badge bg-emerald-subtle text-emerald px-3 py-2 rounded-pill font-sora fw-extrabold" style="background-color: rgba(16, 185, 129, 0.1); color: #059669;">
            Tổng: {{ $movies->total() ?? count($movies) }} phim
        </span>
    </div>

    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th class="ps-4" style="width: 80px;">Poster</th>
                    <th>Tên Phim & Độ Tuổi</th>
                    <th>Thể Loại</th>
                    <th>Định Dạng</th>
                    <th>Thời Lượng</th>
                    <th>Trạng Thái</th>
                    <th class="pe-4 text-end" style="width: 100px;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movies as $movie)
                <tr>
                    <td class="ps-4">
                        @if($movie->poster_url)
                            <img src="{{ str_starts_with($movie->poster_url, 'http') ? $movie->poster_url : asset('storage/' . $movie->poster_url) }}" 
                                 alt="{{ $movie->title }}" 
                                 class="rounded-3 shadow-sm border border-light" 
                                 style="width: 60px; height: 85px; object-fit: cover;">
                        @else
                            <div class="bg-emerald-subtle text-emerald rounded-3 d-flex align-items-center justify-content-center fw-bold" style="width: 60px; height: 85px; background: rgba(16, 185, 129, 0.1);">
                                <i class="fas fa-film fa-lg"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="font-sora fw-extrabold text-ink fs-6 mb-1">{{ $movie->title }}</div>
                        <div class="d-flex align-items-center gap-2">
                            @if($movie->age_rating)
                                <span class="badge bg-danger px-2 py-1 font-sora fw-bold small">{{ $movie->age_rating }}</span>
                            @endif
                            <small class="text-muted font-sora">{{ $movie->language ?? 'Phụ đề Tiếng Việt' }}</small>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($movie->categories as $cat)
                                <span class="badge bg-light text-muted border px-2 py-1 font-sora fw-semibold">{{ $cat->name }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        @php
                            $fmtList = is_array($movie->format) ? $movie->format : (is_string($movie->format) ? explode(',', $movie->format) : ['2D']);
                        @endphp
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($fmtList as $fmt)
                                @php
                                    $fmtStr = trim(strtoupper($fmt));
                                    $badgeClass = str_contains($fmtStr, 'IMAX') ? 'badge-format-imax' : (str_contains($fmtStr, '3D') ? 'badge-format-3d' : 'badge-format-2d');
                                @endphp
                                <span class="{{ $badgeClass }}">{{ $fmtStr }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        <span class="font-sora fw-bold text-ink"><i class="fas fa-clock text-amber me-1"></i>{{ $movie->duration }}</span> <small class="text-muted">phút</small>
                    </td>
                    <td>
                        @if($movie->status == 'COMING_SOON')
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 rounded-pill font-sora fw-bold">
                                <i class="fas fa-hourglass-start me-1"></i>Sắp chiếu
                            </span>
                        @elseif($movie->status == 'NOW_SHOWING')
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill font-sora fw-bold">
                                <i class="fas fa-play-circle me-1"></i>Đang chiếu
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1 rounded-pill font-sora fw-bold">
                                <i class="fas fa-stop-circle me-1"></i>Ngưng chiếu
                            </span>
                        @endif
                    </td>
                    <td class="pe-4 text-end">
                        <div class="btn-action-group">
                            <a href="{{ route('manager.movies.show', $movie) }}" class="btn-action btn-action-view" title="Xem Chi Tiết Phim">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="fas fa-film text-muted opacity-50 mb-3" style="font-size: 3.5rem;"></i>
                        <p class="text-muted font-sora fs-6">Chưa tìm thấy phim nào phù hợp.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($movies->hasPages())
        <div class="card-footer bg-transparent border-top border-light p-3 d-flex justify-content-center">
            {{ $movies->withQueryString()->links('pagination::bootstrap-5') }}
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
