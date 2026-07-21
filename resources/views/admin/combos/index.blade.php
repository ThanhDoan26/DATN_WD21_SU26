@extends('admin.layouts.app')

@section('title', 'Quản lý Combo')
@section('page_title', 'Danh sách Combo Bắp Nước')

@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Search Form -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body p-3">
        <form action="{{ route('admin.combos.index') }}" method="GET" class="row align-items-center g-3">
            <div class="col-12 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Nhập tên combo để tìm kiếm..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>Đang bán (ACTIVE)</option>
                    <option value="INACTIVE" {{ request('status') == 'INACTIVE' ? 'selected' : '' }}>Ngừng bán (INACTIVE)</option>
                </select>
            </div>
            <div class="col-12 col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Lọc</button>
                @if((request()->has('search') && request('search') != '') || (request()->has('status') && request('status') != ''))
                    <a href="{{ route('admin.combos.index') }}" class="btn btn-outline-secondary">Xóa bộ lọc</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách Combo</h5>
        <a href="{{ route('admin.combos.create') }}" class="btn btn-sm btn-light text-primary fw-bold">
            <i class="fas fa-plus me-1"></i> Thêm mới
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th width="100">Hình ảnh</th>
                        <th>Tên Combo</th>
                        <th>Giá</th>
                        <th>Trạng thái</th>
                        <th width="150">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($combos as $combo)
                    <tr>
                        <td>{{ $combo->id }}</td>
                        <td>
                            @if($combo->image)
                                <img src="{{ asset('storage/' . $combo->image) }}" alt="{{ $combo->name }}" style="width: 80px; height: 80px; object-fit: cover;" class="rounded">
                            @else
                                <span class="text-muted">Không có ảnh</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $combo->name }}</strong>
                            <div class="text-muted small">{{ Str::limit($combo->description, 50) }}</div>
                        </td>
                        <td>{{ number_format($combo->price, 0, ',', '.') }} đ</td>
                        <td>
                            <form action="{{ route('admin.combos.toggle-status', $combo) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                @if($combo->status == 'ACTIVE')
                                    <button type="submit" class="btn btn-sm btn-success py-1 px-2 border-0" title="Click để ngừng bán">
                                        Đang bán <i class="fas fa-sync-alt ms-1 text-white-50 small"></i>
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-sm btn-secondary py-1 px-2 border-0" title="Click để bán lại">
                                        Ngừng bán <i class="fas fa-sync-alt ms-1 text-white-50 small"></i>
                                    </button>
                                @endif
                            </form>
                        </td>
                        <td>
                            <a href="{{ route('admin.combos.show', $combo) }}" class="btn btn-sm btn-info text-white" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.combos.edit', $combo) }}" class="btn btn-sm btn-primary" title="Chỉnh sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.combos.destroy', $combo) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa combo này không?');">
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
                        <td colspan="6" class="text-center text-muted py-4">Chưa có dữ liệu Combo</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $combos->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
