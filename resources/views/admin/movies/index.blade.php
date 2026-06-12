@extends('admin.layouts.app')

@section('title', 'Quản lý Phim')
@section('page_title', 'Danh sách Phim')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.movies.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label text-muted fw-semibold mb-1">Tìm kiếm</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Nhập tên phim..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted fw-semibold mb-1">Danh mục</label>
                        <select name="category_id" class="form-select bg-light">
                            <option value="">Tất cả danh mục</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted fw-semibold mb-1">Trạng thái</label>
                        <select name="status" class="form-select bg-light">
                            <option value="">Tất cả trạng thái</option>
                            <option value="COMING_SOON" {{ request('status') == 'COMING_SOON' ? 'selected' : '' }}>Sắp chiếu</option>
                            <option value="NOW_SHOWING" {{ request('status') == 'NOW_SHOWING' ? 'selected' : '' }}>Đang chiếu</option>
                            <option value="ENDED" {{ request('status') == 'ENDED' ? 'selected' : '' }}>Ngưng chiếu</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 fw-semibold shadow-sm">
                            Lọc kết quả
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-gray-800">Danh sách Phim</h5>
        <a href="{{ route('admin.movies.create') }}" class="btn btn-primary fw-semibold shadow-sm px-3 py-2">
            <i class="fas fa-plus me-1"></i> Thêm phim mới
        </a>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle custom-table mb-0">
                <thead class="table-light text-muted">
                    <tr>
                        <th width="90" class="rounded-start">Poster</th>
                        <th>Thông tin phim</th>
                        <th>Danh mục</th>
                        <th>Thời lượng</th>
                        <th>Trạng thái</th>
                        <th class="text-center rounded-end" width="160">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($movies as $movie)
                    <tr class="align-middle">
                        <td>
                            @if($movie->poster_url)
                                <div class="poster-container shadow-sm">
                                    <img src="{{ Str::startsWith($movie->poster_url, ['http://', 'https://']) ? $movie->poster_url : asset('storage/' . $movie->poster_url) }}" alt="{{ $movie->title }}" class="movie-poster">
                                </div>
                            @else
                                <div class="poster-container shadow-sm bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-film text-muted fs-4"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <h6 class="mb-1 fw-bold text-dark">{{ $movie->title }}</h6>
                            <div class="text-muted small">
                                <span class="badge bg-light text-secondary border me-1" title="Độ tuổi">{{ $movie->age_rating ?? 'N/A' }}</span>
                                <span><i class="fas fa-globe-asia me-1"></i>{{ $movie->language ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @forelse($movie->categories as $cat)
                                    <span class="badge custom-badge-category">{{ $cat->name }}</span>
                                @empty
                                    <span class="text-muted small">Chưa phân loại</span>
                                @endforelse
                            </div>
                        </td>
                        <td>
                            <span class="fw-medium text-dark"><i class="far fa-clock text-muted me-1"></i>{{ $movie->duration }} phút</span>
                        </td>
                        <td>
                            @if($movie->status == 'COMING_SOON')
                                <span class="badge rounded-pill custom-badge-warning"><i class="fas fa-calendar-alt me-1"></i> Sắp chiếu</span>
                            @elseif($movie->status == 'NOW_SHOWING')
                                <span class="badge rounded-pill custom-badge-success"><i class="fas fa-play-circle me-1"></i> Đang chiếu</span>
                            @else
                                <span class="badge rounded-pill custom-badge-danger"><i class="fas fa-stop-circle me-1"></i> Ngưng chiếu</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.movies.show', $movie) }}" class="btn btn-sm btn-icon btn-light text-primary" data-bs-toggle="tooltip" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.movies.edit', $movie) }}" class="btn btn-sm btn-icon btn-light text-warning" data-bs-toggle="tooltip" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.movies.destroy', $movie) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bộ phim này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon btn-light text-danger" data-bs-toggle="tooltip" title="Xóa">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="empty-state">
                                <div class="empty-state-icon mb-3">
                                    <i class="fas fa-film text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                                </div>
                                <h5 class="fw-bold text-dark">Chưa có dữ liệu phim</h5>
                                <p class="text-muted mb-4">Bạn chưa có bộ phim nào hoặc không tìm thấy kết quả phù hợp với bộ lọc.</p>
                                <a href="{{ route('admin.movies.create') }}" class="btn btn-primary px-4 rounded-pill">
                                    Thêm phim ngay
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($movies->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
            <div class="text-muted small">
                Hiển thị từ <strong>{{ $movies->firstItem() }}</strong> đến <strong>{{ $movies->lastItem() }}</strong> trong tổng số <strong>{{ $movies->total() }}</strong> phim
            </div>
            <div>
                {{ $movies->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>

<style>
/* Custom Styles for Professional Look */
.rounded-4 {
    border-radius: 1rem !important;
}
.bg-light {
    background-color: #f8f9fa !important;
}
.custom-table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    padding-top: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #edf2f9;
}
.custom-table td {
    padding-top: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #edf2f9;
}
.custom-table tbody tr {
    transition: all 0.2s ease;
}
.custom-table tbody tr:hover {
    background-color: #f8f9fc;
}

/* Poster styling */
.poster-container {
    width: 65px;
    height: 90px;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
    background-color: #e9ecef;
}
.movie-poster {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}
.custom-table tbody tr:hover .movie-poster {
    transform: scale(1.05);
}

/* Badges */
.custom-badge-category {
    background-color: #e2e8f0;
    color: #475569;
    font-weight: 500;
}
.custom-badge-warning {
    background-color: rgba(245, 158, 11, 0.1);
    color: #d97706;
}
.custom-badge-success {
    background-color: rgba(16, 185, 129, 0.1);
    color: #059669;
}
.custom-badge-danger {
    background-color: rgba(239, 68, 68, 0.1);
    color: #dc2626;
}

/* Action buttons */
.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s;
}
.btn-icon:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}
.btn-icon.text-primary:hover { background-color: #e0e7ff; }
.btn-icon.text-warning:hover { background-color: #fef3c7; }
.btn-icon.text-danger:hover { background-color: #fee2e2; }

/* Empty state */
.empty-state {
    padding: 2rem 0;
}

/* Input styles */
.form-control:focus, .form-select:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    border-color: #86b7fe;
}
.input-group-text {
    border-right: none;
}
.input-group .form-control {
    border-left: none;
}
.input-group .form-control:focus {
    border-color: #dee2e6; /* Keep border color same as standard to avoid left border appearing */
    box-shadow: none; /* Remove shadow on focus inside group for cleaner look */
}
.input-group:focus-within {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    border-radius: 0.375rem;
}
.input-group:focus-within .form-control, .input-group:focus-within .input-group-text {
    border-color: #86b7fe;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
</script>
@endsection

