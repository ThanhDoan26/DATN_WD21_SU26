@extends('admin.layouts.app')

@section('title', 'Danh mục Tin tức - Admin')
@section('page_title', 'Quản lý Danh mục Tin tức')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Danh mục Tin tức</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="fas fa-folder"></i> Danh mục Tin tức / Blog</h2>
        <p class="text-muted" style="margin-top: 5px; margin-bottom: 0;">Quản lý các chủ đề bài viết như Khuyến mãi, Sự kiện, Tin điện ảnh...</p>
    </div>
    <div>
        <a href="{{ route('admin.post-categories.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm Danh Mục Mới</a>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách Danh mục</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th>Tên danh mục</th>
                        <th>Slug</th>
                        <th>Mô tả</th>
                        <th width="150" class="text-center">Số bài viết</th>
                        <th width="150" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td><strong>{{ $category->name }}</strong></td>
                        <td><code class="text-muted">{{ $category->slug }}</code></td>
                        <td>{{ Str::limit($category->description, 100) ?: 'Chưa có mô tả' }}</td>
                        <td class="text-center">
                            <span class="badge bg-info px-3 py-2">{{ $category->posts_count }} bài viết</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.post-categories.edit', $category) }}" class="btn btn-sm btn-primary" title="Chỉnh sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.post-categories.destroy', $category) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Xóa" {{ $category->posts_count > 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Chưa có danh mục nào được tạo.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $categories->links() }}
        </div>
    </div>
</div>
@endsection
