@extends('layouts.manager')

@section('title', 'Quản Lý Combo Bắp Nước')
@section('page_title', 'Danh Sách Combo Bắp Nước')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('manager.dashboard') }}" class="text-decoration-none text-muted fw-semibold">Dashboard</a></li>
            <li class="breadcrumb-item active text-emerald font-sora fw-bold">Combo Bắp Nước</li>
        </ol>
    </nav>
</div>

<!-- Page Title & Header Actions -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="fw-extrabold text-ink font-sora mb-1 fs-3"><i class="fas fa-hamburger text-emerald me-2"></i>Danh Sách Combo Bắp Nước</h2>
        <p class="text-muted small mb-0">Xem và quản lý bảng giá các sản phẩm combo bắp nước phục vụ rạp chiếu</p>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--bg-surface);">
    <div class="card-body p-3">
        <form action="{{ route('manager.combos.index') }}" method="GET" class="row align-items-center g-3">
            <div class="col-12 col-md-6">
                <div class="input-group">
                    <span class="input-group-text border-end-0 bg-transparent text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Nhập tên combo bắp nước để tìm kiếm..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select name="status" class="form-select font-sora fw-semibold">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>Đang bán (ACTIVE)</option>
                    <option value="INACTIVE" {{ request('status') == 'INACTIVE' ? 'selected' : '' }}>Ngừng bán (INACTIVE)</option>
                </select>
            </div>
            <div class="col-12 col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-emerald font-sora fw-bold px-4"><i class="fas fa-filter me-1"></i> Lọc</button>
                @if((request()->has('search') && request('search') != '') || (request()->has('status') && request('status') != ''))
                    <a href="{{ route('manager.combos.index') }}" class="btn btn-outline-secondary px-3">Xóa bộ lọc</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Combos Table Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--bg-surface);">
    <div class="card-header bg-transparent border-bottom border-light p-4 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-extrabold font-sora text-ink"><i class="fas fa-utensils me-2 text-emerald"></i>Danh Sách Combo</h5>
        <span class="badge bg-emerald-subtle text-emerald px-3 py-2 rounded-pill font-sora fw-extrabold" style="background-color: rgba(16, 185, 129, 0.1); color: #059669;">
            Tổng: {{ $combos->total() ?? count($combos) }} combo
        </span>
    </div>

    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th class="ps-4" style="width: 70px;">ID</th>
                    <th style="width: 90px;">Hình Ảnh</th>
                    <th>Tên Combo & Mô Tả</th>
                    <th>Đơn Giá</th>
                    <th>Trạng Thái</th>
                    <th class="pe-4 text-end" style="width: 100px;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($combos as $combo)
                <tr>
                    <td class="ps-4 font-sora fw-bold text-muted">#{{ $combo->id }}</td>
                    <td>
                        @if($combo->image)
                            <img src="{{ asset('storage/' . $combo->image) }}" alt="{{ $combo->name }}" style="width: 60px; height: 60px; object-fit: cover;" class="rounded-3 shadow-sm border border-light">
                        @else
                            <div class="bg-emerald-subtle text-emerald rounded-3 d-flex align-items-center justify-content-center fw-bold" style="width: 60px; height: 60px; background: rgba(16, 185, 129, 0.1);">
                                <i class="fas fa-popcorn fa-lg"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="font-sora fw-extrabold text-ink fs-6 mb-1">{{ $combo->name }}</div>
                        <div class="text-muted small text-truncate" style="max-width: 350px;">{{ Str::limit($combo->description, 60) }}</div>
                    </td>
                    <td>
                        <span class="font-sora fw-extrabold text-emerald fs-6">{{ number_format($combo->price, 0, ',', '.') }} ₫</span>
                    </td>
                    <td>
                        @if($combo->status == 'ACTIVE')
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill font-sora fw-bold">
                                <i class="fas fa-check-circle me-1"></i>Đang bán
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1 rounded-pill font-sora fw-bold">
                                <i class="fas fa-ban me-1"></i>Ngừng bán
                            </span>
                        @endif
                    </td>
                    <td class="pe-4 text-end">
                        <div class="btn-action-group">
                            <a href="{{ route('manager.combos.show', $combo) }}" class="btn-action btn-action-view" title="Xem Chi Tiết Combo">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="fas fa-cookie-bite text-muted opacity-50 mb-3" style="font-size: 3.5rem;"></i>
                        <p class="text-muted font-sora fs-6">Chưa tìm thấy combo bắp nước nào phù hợp.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($combos, 'hasPages') && $combos->hasPages())
        <div class="card-footer bg-transparent border-top border-light p-3 d-flex justify-content-center">
            {{ $combos->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection

@section('extra_css')
<style>
    .btn-emerald {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff;
        border: none;
    }
    .btn-emerald:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: #ffffff;
    }
    .text-emerald {
        color: #10b981 !important;
    }
</style>
@endsection
