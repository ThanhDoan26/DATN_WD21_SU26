@extends('admin.layouts.app')

@section('title', 'Quản lý Bài viết - Admin')
@section('page_title', 'Danh sách Bài viết')

@section('content')
<!-- Search & Filter Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.posts.index') }}" method="GET" class="row g-3">
                    <!-- Search input -->
                    <div class="col-md-3">
                        <label class="form-label text-muted fw-semibold mb-1">Tìm kiếm</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Tiêu đề, nội dung, tác giả..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <!-- Category dropdown -->
                    <div class="col-md-2">
                        <label class="form-label text-muted fw-semibold mb-1">Danh mục</label>
                        <select name="post_category_id" class="form-select bg-light">
                            <option value="">Tất cả danh mục</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('post_category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status dropdown -->
                    <div class="col-md-2">
                        <label class="form-label text-muted fw-semibold mb-1">Trạng thái</label>
                        <select name="status" class="form-select bg-light">
                            <option value="">Tất cả trạng thái</option>
                            <option value="Draft" {{ request('status') === 'Draft' ? 'selected' : '' }}>Bản nháp (Draft)</option>
                            <option value="Published" {{ request('status') === 'Published' ? 'selected' : '' }}>Đã xuất bản (Published)</option>
                            <option value="Hidden" {{ request('status') === 'Hidden' ? 'selected' : '' }}>Đang ẩn (Hidden)</option>
                        </select>
                    </div>

                    <!-- Featured dropdown -->
                    <div class="col-md-2">
                        <label class="form-label text-muted fw-semibold mb-1">Nổi bật</label>
                        <select name="is_featured" class="form-select bg-light">
                            <option value="">Tất cả bài viết</option>
                            <option value="1" {{ request('is_featured') === '1' ? 'selected' : '' }}>Tin nổi bật</option>
                            <option value="0" {{ request('is_featured') === '0' ? 'selected' : '' }}>Tin thường</option>
                        </select>
                    </div>

                    <!-- Author dropdown -->
                    <div class="col-md-3">
                        <label class="form-label text-muted fw-semibold mb-1">Tác giả</label>
                        <select name="author_id" class="form-select bg-light">
                            <option value="">Tất cả tác giả</option>
                            @foreach($authors as $author)
                                <option value="{{ $author->id }}" {{ request('author_id') == $author->id ? 'selected' : '' }}>
                                    {{ $author->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date range filters -->
                    <div class="col-md-3">
                        <label class="form-label text-muted fw-semibold mb-1">Từ ngày đăng</label>
                        <input type="date" name="start_date" class="form-control bg-light" value="{{ request('start_date') }}">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label text-muted fw-semibold mb-1">Đến ngày đăng</label>
                        <input type="date" name="end_date" class="form-control bg-light" value="{{ request('end_date') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-muted fw-semibold mb-1">Sắp xếp</label>
                        <div class="d-flex gap-2">
                            <select name="sort_by" class="form-select bg-light">
                                <option value="published_at" {{ request('sort_by') === 'published_at' ? 'selected' : '' }}>Ngày đăng</option>
                                <option value="title" {{ request('sort_by') === 'title' ? 'selected' : '' }}>Tiêu đề</option>
                                <option value="views" {{ request('sort_by') === 'views' ? 'selected' : '' }}>Lượt xem</option>
                                <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Ngày tạo</option>
                            </select>
                            <select name="sort_order" class="form-select bg-light" style="width: 140px;">
                                <option value="desc" {{ request('sort_order') === 'desc' ? 'selected' : '' }}>Mới nhất</option>
                                <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Cũ nhất</option>
                            </select>
                        </div>
                    </div>

                    <!-- Filter Button -->
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 fw-semibold shadow-sm">
                            <i class="fas fa-filter me-1"></i> Lọc kết quả
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
        <h5 class="mb-0">Danh sách Bài viết</h5>
        @if(auth()->user()->isAdmin())
        <div class="d-flex gap-2">
            <a href="{{ route('admin.posts.trashed') }}" class="btn btn-sm btn-secondary fw-bold" title="Xem tin đã xóa">
                <i class="fas fa-trash-alt me-1"></i> Đã Xóa Tạm
            </a>
            <a href="{{ route('admin.posts.create') }}" class="btn btn-sm btn-light text-primary fw-bold">
                <i class="fas fa-plus me-1"></i> Viết bài mới
            </a>
        </div>
        @endif
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th class="text-center" width="50">STT</th>
                        <th width="100">Ảnh</th>
                        <th>Tiêu đề</th>
                        <th>Danh mục</th>
                        <th>Tác giả</th>
                        <th width="140" class="text-center">Trạng thái</th>
                        <th width="120" class="text-center">Nổi bật</th>
                        <th class="text-center" width="90">Lượt xem</th>
                        <th width="140">Ngày đăng</th>
                        <th width="150" class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $key => $post)
                    <tr>
                        <td class="text-center">{{ $posts->firstItem() + $key }}</td>
                        <td>
                            @if($post->image)
                                <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" class="img-thumbnail" style="width: 70px; height: 50px; object-fit: cover;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center text-muted" style="width: 70px; height: 50px; font-size: 10px;">No Image</div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $post->title }}</strong>
                            <p class="text-muted mb-0 small" title="{{ $post->summary }}">{{ Str::limit($post->summary, 80) }}</p>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $post->category?->name ?: 'N/A' }}</span>
                        </td>
                        <td>{{ $post->author?->name ?: 'Không rõ' }}</td>
                        <td class="text-center">
                            @if(auth()->user()->isAdmin())
                                <form action="{{ route('admin.posts.toggle-status', $post) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    @if($post->status === 'Published')
                                        <button type="submit" class="btn btn-sm btn-success w-100 py-1" title="Click để ẩn tin">Xuất bản</button>
                                    @elseif($post->status === 'Hidden')
                                        <button type="submit" class="btn btn-sm btn-warning text-dark w-100 py-1" title="Click để xuất bản">Đang ẩn</button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-info w-100 py-1" title="Click để xuất bản">Bản nháp</button>
                                    @endif
                                </form>
                            @else
                                @if($post->status === 'Published')
                                    <span class="badge bg-success">Xuất bản</span>
                                @elseif($post->status === 'Hidden')
                                    <span class="badge bg-warning text-dark">Đang ẩn</span>
                                @else
                                    <span class="badge bg-info">Bản nháp</span>
                                @endif
                            @endif
                        </td>
                        <td class="text-center">
                            @if(auth()->user()->isAdmin())
                                <form action="{{ route('admin.posts.toggle-featured', $post) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $post->is_featured ? 'btn-warning text-dark' : 'btn-light border text-muted' }}" title="Click để thay đổi nổi bật">
                                        <i class="fas {{ $post->is_featured ? 'fa-star' : 'fa-star-o' }}"></i> {{ $post->is_featured ? 'Nổi bật' : 'Thường' }}
                                    </button>
                                </form>
                            @else
                                <span class="badge {{ $post->is_featured ? 'bg-warning text-dark' : 'bg-light text-muted' }}">
                                    {{ $post->is_featured ? 'Nổi bật' : 'Thường' }}
                                </span>
                            @endif
                        </td>
                        <td class="text-center"><i class="fas fa-eye text-muted me-1"></i>{{ $post->views }}</td>
                        <td>
                            @if($post->published_at)
                                {{ $post->published_at->format('d/m/Y H:i') }}
                            @else
                                <span class="text-muted small italic">Chưa đăng</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.posts.show', $post) }}" class="btn btn-sm btn-info" title="Xem chi tiết/Xem trước">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-sm btn-primary" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này (đưa vào thùng rác)?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">Không tìm thấy bài viết nào phù hợp.</td>
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
