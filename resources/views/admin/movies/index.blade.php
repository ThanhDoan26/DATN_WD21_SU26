@extends('admin.layouts.app')

@section('title', 'Danh sách Phim')
@section('page_title', 'Danh sách Phim')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Bộ lọc & Tìm kiếm</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.movies.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Tìm theo tên phim..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">-- Tất cả danh mục --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="COMING_SOON" {{ request('status') == 'COMING_SOON' ? 'selected' : '' }}>Sắp chiếu</option>
                    <option value="NOW_SHOWING" {{ request('status') == 'NOW_SHOWING' ? 'selected' : '' }}>Đang chiếu</option>
                    <option value="ENDED" {{ request('status') == 'ENDED' ? 'selected' : '' }}>Ngưng chiếu</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Lọc</button>
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

