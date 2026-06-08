@extends('admin.layouts.app')

@section('title', 'Movies - Admin')
@section('page_title', 'Movies Management')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Movies</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title">
    <div>
        <h2><i class="fas fa-video"></i> Danh sách Phim</h2>
        <p class="text-muted" style="margin-top: 5px;">Quản lý danh sách phim trong hệ thống</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('admin.movies.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Thêm Phim
        </a>
    </div>
</div>

<!-- Movies Grid -->
<div class="row">
    @forelse($movies ?? [] as $movie)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card movie-card h-100">
            <!-- Poster -->
            @if($movie->poster_url)
            <div class="movie-poster" style="height: 250px; overflow: hidden; background: #f0f0f0;">
                <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}" class="w-100 h-100 object-fit-cover">
            </div>
            @else
            <div class="movie-poster" style="height: 250px; background: #e9ecef; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-image" style="font-size: 3rem; color: #ccc;"></i>
            </div>
            @endif

            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title">{{ $movie->title }}</h5>

                <!-- Info -->
                <div class="mb-3" style="font-size: 0.85rem;">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Thời lượng:</span>
                        <strong>{{ $movie->getDurationFormatted() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Ngôn ngữ:</span>
                        <strong>{{ $movie->language ?? 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Nước:</span>
                        <strong>{{ $movie->country ?? 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Đạo diễn:</span>
                        <strong>{{ $movie->director ?? 'N/A' }}</strong>
                    </div>
                </div>

                <!-- Status -->
                <div class="mb-3">
                    @if($movie->status === 'ACTIVE')
                        <span class="badge bg-success"><i class="fas fa-check-circle"></i> Active</span>
                    @elseif($movie->status === 'COMING_SOON')
                        <span class="badge bg-info"><i class="fas fa-clock"></i> Coming Soon</span>
                    @else
                        <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Inactive</span>
                    @endif
                </div>

                <!-- Actions -->
                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('admin.movies.show', $movie->id) }}" class="btn btn-info btn-sm flex-grow-1">
                        <i class="fas fa-eye"></i> Xem
                    </a>
                    <a href="{{ route('admin.movies.edit', $movie->id) }}" class="btn btn-warning btn-sm flex-grow-1">
                        <i class="fas fa-edit"></i> Sửa
                    </a>
                    <button type="button" class="btn btn-danger btn-sm" 
                            onclick="deleteRecord('{{ route('admin.movies.destroy', $movie->id) }}')" 
                            title="Xóa">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-film" style="font-size: 3rem; color: #ccc;"></i>
                <h5 class="mt-3">Chưa có phim nào</h5>
                <p class="text-muted">Hãy thêm phim đầu tiên của bạn</p>
                <a href="{{ route('admin.movies.create') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-plus"></i> Thêm Phim
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($movies && $movies->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $movies->links() }}
</div>
@endif

<style>
.movie-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.movie-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
}

.movie-poster {
    position: relative;
}

.movie-poster img {
    object-fit: cover;
}

.object-fit-cover {
    object-fit: cover !important;
}
</style>

<script>
function deleteRecord(deleteUrl) {
    if (confirm('Bạn có chắc chắn muốn xóa phim này?')) {
        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        }).then(response => {
            if (response.ok) {
                window.location.reload();
            } else {
                alert('Lỗi xóa phim!');
            }
        }).catch(error => console.error('Error:', error));
    }
}
</script>
@endsection

