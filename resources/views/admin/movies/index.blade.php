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
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách Phim</h5>
        <a href="{{ route('admin.movies.create') }}" class="btn btn-sm btn-light text-primary fw-bold">
            <i class="fas fa-plus"></i> Thêm phim mới
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th width="80">Poster</th>
                        <th>Tên phim</th>
                        <th>Danh mục</th>
                        <th>Thời lượng</th>
                        <th>Trạng thái</th>
                        <th class="text-center" width="150">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movies as $movie)
                    <tr>
                        <td>
                            @if($movie->poster_url)
                                <img src="{{ asset('storage/' . $movie->poster_url) }}" alt="{{ $movie->title }}" class="img-thumbnail" style="width: 60px; height: 80px; object-fit: cover;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center text-muted" style="width: 60px; height: 80px; font-size: 10px;">
                                    No Image
                                </div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $movie->title }}</strong><br>
                            <small class="text-muted">{{ $movie->age_rating }} | {{ $movie->language }}</small>
                        </td>
                        <td>
                            @foreach($movie->categories as $cat)
                                <span class="badge bg-secondary">{{ $cat->name }}</span>
                            @endforeach
                        </td>
                        <td>{{ $movie->getDurationFormatted() }}</td>
                        <td>
                            @if($movie->status == 'COMING_SOON')
                                <span class="badge bg-warning text-dark">Sắp chiếu</span>
                            @elseif($movie->status == 'NOW_SHOWING')
                                <span class="badge bg-success">Đang chiếu</span>
                            @else
                                <span class="badge bg-danger">Ngưng chiếu</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.movies.show', $movie) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.movies.edit', $movie) }}" class="btn btn-sm btn-primary" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.movies.destroy', $movie) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bộ phim này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Chưa có dữ liệu phim</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $movies->withQueryString()->links() }}
        </div>
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

