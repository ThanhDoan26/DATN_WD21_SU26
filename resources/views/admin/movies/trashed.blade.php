@extends('admin.layouts.app')

@section('title', 'Phim Đã Xóa - Admin')
@section('page_title', 'Danh sách Phim Đã Xóa')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.movies.index') }}">Phim</a></li>
            <li class="breadcrumb-item active">Đã Xóa</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="fas fa-trash-alt"></i> Phim Đã Xóa</h2>
        <p class="text-muted mb-0">Danh sách các bộ phim đã xóa (có thể khôi phục)</p>
    </div>
    <div>
        <a href="{{ route('admin.movies.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay Lại
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

<!-- Search Form -->
<div class="card mb-4 border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form action="{{ route('admin.movies.trashed') }}" method="GET" class="row align-items-center g-3">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Nhập tên phim để tìm kiếm..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Lọc</button>
                @if(request()->has('search') && request('search') != '')
                    <a href="{{ route('admin.movies.trashed') }}" class="btn btn-outline-secondary">Xóa bộ lọc</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Trashed Movies Table -->
<div class="card rounded-4 shadow-sm">
    <div class="card-header">
        <i class="fas fa-table"></i> Danh sách Phim Đã Xóa
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle custom-table mb-0">
                <thead>
                    <tr>
                        <th width="80" class="text-center">Poster</th>
                        <th>Tên phim</th>
                        <th>Danh mục</th>
                        <th>Thời lượng</th>
                        <th>Xóa lúc</th>
                        <th class="text-center" width="200">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movies as $movie)
                    <tr style="opacity: 0.8;">
                        <td class="text-center">
                            @if($movie->poster_url)
                                <img src="{{ asset('storage/' . $movie->poster_url) }}" alt="{{ $movie->title }}" class="img-thumbnail" style="width: 60px; height: 80px; object-fit: cover;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center text-muted" style="width: 60px; height: 80px; font-size: 10px; margin: 0 auto;">
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
                            <small class="text-muted">{{ $movie->deleted_at->format('d/m/Y H:i') }}</small>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-2 justify-content-center">
                                <!-- Restore Button -->
                                <form action="{{ route('admin.movies.restore', $movie->id) }}" method="POST" style="display:inline;" title="Khôi phục">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="Khôi phục">
                                        <i class="fas fa-redo"></i> Khôi Phục
                                    </button>
                                </form>

                                <!-- Force Delete Button -->
                                <form action="{{ route('admin.movies.forceDelete', $movie->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('⚠️ Cảnh báo: Điều này sẽ XÓA VĩNH VIỄN phim này và không thể hoàn tác!\n\nBạn chắc chắn không?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa vĩnh viễn">
                                        <i class="fas fa-times"></i> Xóa Vĩnh Viễn
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-inbox text-muted mb-3" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-0">Không có phim nào bị xóa.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
@if($movies && $movies->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $movies->links('pagination::bootstrap-4') }}
</div>
@endif

<style>
/* Re-use styling from movies index for consistency */
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
.input-group .form-control:focus {
    box-shadow: none;
    border-color: #dee2e6;
}
.input-group:focus-within {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    border-radius: 0.375rem;
}
.input-group:focus-within .form-control, .input-group:focus-within .input-group-text {
    border-color: #86b7fe;
}
</style>
@endsection
