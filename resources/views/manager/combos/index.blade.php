@extends('layouts.manager')

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
        <form action="{{ route('manager.combos.index') }}" method="GET" class="row align-items-center g-3">
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
                    <a href="{{ route('manager.combos.index') }}" class="btn btn-outline-secondary">Xóa bộ lọc</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách Combo</h5>
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
                            @if($combo->status == 'ACTIVE')
                                <span class="badge bg-success">Đang bán</span>
                            @else
                                <span class="badge bg-secondary">Ngừng bán</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('manager.combos.show', $combo) }}" class="btn btn-sm btn-info text-white" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
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
