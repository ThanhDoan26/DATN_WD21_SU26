@extends('admin.layouts.app')

@section('title', 'Xem chi tiết Bài viết - Admin')
@section('page_title', 'Xem Bài Viết')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.posts.index') }}">Bài viết</a></li>
            <li class="breadcrumb-item active">Chi tiết</li>
        </ol>
    </nav>
</div>

<!-- Main Detail View -->
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4 shadow-sm">
            @if($post->banner)
                <img src="{{ asset('storage/' . $post->banner) }}" class="card-img-top" alt="Post Banner" style="max-height: 350px; object-fit: cover;">
            @endif
            <div class="card-body p-4">
                <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge bg-secondary px-3 py-2">{{ $post->category?->name ?: 'N/A' }}</span>
                    @if($post->status === 'Published')
                        <span class="badge bg-success px-3 py-2">Đã xuất bản</span>
                    @elseif($post->status === 'Hidden')
                        <span class="badge bg-warning text-dark px-3 py-2">Đang ẩn</span>
                    @else
                        <span class="badge bg-info px-3 py-2">Bản nháp</span>
                    @endif
                    
                    @if($post->is_featured)
                        <span class="badge bg-danger px-3 py-2"><i class="fas fa-star me-1"></i> Tin nổi bật</span>
                    @endif
                </div>

                <h1 class="h2 fw-bold text-primary mb-3">{{ $post->title }}</h1>

                <div class="text-muted d-flex gap-4 mb-4 border-top border-bottom py-2" style="font-size: 0.9rem;">
                    <span><i class="fas fa-user me-1"></i> Tác giả: <strong>{{ $post->author?->name ?: 'Không rõ' }}</strong></span>
                    <span><i class="fas fa-calendar-alt me-1"></i> Ngày đăng: <strong>{{ $post->published_at ? $post->published_at->format('d/m/Y H:i') : 'Chưa đăng' }}</strong></span>
                    <span><i class="fas fa-eye me-1"></i> Lượt xem: <strong>{{ $post->views }}</strong></span>
                </div>

                <div class="mb-4 lead fw-normal text-secondary border-start border-4 border-primary ps-3 py-1">
                    {{ $post->summary }}
                </div>

                <div class="content-body" style="line-height: 1.8;">
                    {!! $post->content !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Info Column -->
    <div class="col-lg-4">
        <!-- Thumbnail Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-image me-1"></i> Ảnh đại diện</h5>
            </div>
            <div class="card-body text-center">
                @if($post->image)
                    <img src="{{ asset('storage/' . $post->image) }}" alt="Post Thumbnail" class="img-fluid rounded shadow-sm" style="max-height: 200px; object-fit: cover;">
                @else
                    <div class="bg-light text-muted p-4 rounded text-center">Không có ảnh đại diện</div>
                @endif
            </div>
        </div>

        <!-- SEO Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-search me-1"></i> Thông tin SEO</h5>
            </div>
            <div class="card-body">
                <p><strong>SEO Title:</strong> <br><span class="text-muted">{{ $post->seo_title ?: 'Không có' }}</span></p>
                <hr>
                <p><strong>SEO Keywords:</strong> <br><span class="text-muted">{{ $post->seo_keywords ?: 'Không có' }}</span></p>
                <hr>
                <p><strong>SEO Description:</strong> <br><span class="text-muted">{{ $post->seo_description ?: 'Không có' }}</span></p>
            </div>
        </div>

        @if(auth()->user()->isAdmin())
        <!-- Actions Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body d-flex flex-column gap-2">
                <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-primary py-2 fw-bold"><i class="fas fa-edit me-1"></i> Sửa bài viết</a>
                <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary py-2 fw-bold"><i class="fas fa-list me-1"></i> Quay lại danh sách</a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
