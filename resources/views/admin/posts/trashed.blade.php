@extends('admin.layouts.app')

@section('title', 'Thùng rác Bài viết - Admin')
@section('page_title', 'Bài Viết Đã Xóa Tạm')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.posts.index') }}">Bài viết</a></li>
            <li class="breadcrumb-item active">Thùng rác</li>
        </ol>
    </nav>
</div>

<!-- Search Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.posts.trashed') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-9">
                        <label class="form-label text-muted fw-semibold mb-1">Tìm kiếm tin đã xóa</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Nhập tiêu đề bài viết cần tìm..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100 fw-semibold shadow-sm">
                            Tìm kiếm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách Bài viết đã xóa tạm thời</h5>
        <a href="{{ route('admin.posts.index') }}" class="btn btn-sm btn-light text-primary fw-bold">
            <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách chính
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th class="text-center" width="50">ID</th>
                        <th width="100">Ảnh</th>
                        <th>Tiêu đề</th>
                        <th>Danh mục</th>
                        <th>Tác giả</th>
                        <th width="150">Ngày xóa</th>
                        <th width="180" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                    <tr>
                        <td class="text-center">{{ $post->id }}</td>
                        <td>
                            @if($post->image)
                                <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" class="img-thumbnail" style="width: 70px; height: 50px; object-fit: cover;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center text-muted" style="width: 70px; height: 50px; font-size: 10px;">No Image</div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $post->title }}</strong>
                            <p class="text-muted mb-0 small">{{ Str::limit($post->summary, 80) }}</p>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $post->category?->name ?: 'N/A' }}</span>
                        </td>
                        <td>{{ $post->author?->name ?: 'Không rõ' }}</td>
                        <td>{{ $post->deleted_at ? $post->deleted_at->format('d/m/Y H:i') : 'N/A' }}</td>
                        <td class="text-center">
                            <form action="{{ route('admin.posts.restore', $post->id) }}" method="POST" class="d-inline-block">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" title="Khôi phục">
                                    <i class="fas fa-undo me-1"></i> Khôi phục
                                </button>
                            </form>
                            <form action="{{ route('admin.posts.forceDelete', $post->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('CẢNH BÁO: Bài viết và hình ảnh đi kèm sẽ bị xóa vĩnh viễn và không thể khôi phục! Bạn chắc chắn muốn xóa?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Xóa vĩnh viễn">
                                    <i class="fas fa-trash-alt me-1"></i> Xóa vĩnh viễn
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Thùng rác trống.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $posts->links() }}
        </div>
    </div>
</div>
@endsection
