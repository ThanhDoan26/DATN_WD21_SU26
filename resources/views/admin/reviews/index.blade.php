@extends('admin.layouts.app')

@section('title', 'Quản Lý Đánh Giá Phim')
@section('page_title', 'Quản Lý Đánh Giá')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Reviews</li>
        </ol>
    </nav>
</div>

<!-- Page Title & Actions -->
<div class="page-title d-flex justify-content-between align-items-center">
    <h2><i class="fas fa-comments"></i> Danh Sách Đánh Giá</h2>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.reviews.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" placeholder="Tìm kiếm theo Tên User, Email hoặc Tên Phim..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">Tất cả trạng thái</option>
                    <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Hiển thị (ACTIVE)</option>
                    <option value="HIDDEN" {{ request('status') === 'HIDDEN' ? 'selected' : '' }}>Đã ẩn (HIDDEN)</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Tìm kiếm</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary w-100"><i class="fas fa-sync"></i> Xóa lọc</a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Người Dùng</th>
                        <th>Phim</th>
                        <th>Đánh Giá</th>
                        <th>Bình Luận</th>
                        <th>Trạng Thái</th>
                        <th>Ngày Đăng</th>
                        <th class="text-end">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                        <tr>
                            <td>{{ $review->id }}</td>
                            <td>
                                <strong>{{ $review->user->name }}</strong><br>
                                <small class="text-muted">{{ $review->user->email }}</small>
                            </td>
                            <td>{{ $review->movie->title }}</td>
                            <td>
                                <div class="text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $review->rating ? '' : 'text-muted' }}"></i>
                                    @endfor
                                </div>
                            </td>
                            <td class="text-break" style="max-width: 300px;">
                                {{ Str::limit($review->comment, 100) }}
                            </td>
                            <td>
                                @if($review->status === 'ACTIVE')
                                    <span class="badge bg-success">ACTIVE</span>
                                @else
                                    <span class="badge bg-secondary">HIDDEN</span>
                                @endif
                            </td>
                            <td>{{ $review->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <form action="{{ route('admin.reviews.toggle-status', $review->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $review->status === 'ACTIVE' ? 'btn-warning' : 'btn-success' }}" 
                                                title="{{ $review->status === 'ACTIVE' ? 'Ẩn đánh giá này' : 'Hiện đánh giá này' }}">
                                            <i class="fas {{ $review->status === 'ACTIVE' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn đánh giá này không?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Xóa đánh giá">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486831.png" alt="No data" width="80" class="mb-3 opacity-50">
                                <p class="text-muted mb-0">Chưa có đánh giá nào.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-4">
            {{ $reviews->links() }}
        </div>
    </div>
</div>
@endsection
