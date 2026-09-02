@extends('layouts.manager')

@section('title', 'Mã Giảm Giá Đã Hết Hạn')
@section('page_title', 'Mã Đã Hết Hạn')

@section('content')
<div class="container-fluid p-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card" style="border-radius:18px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.06);">
        <div class="card-header d-flex justify-content-between align-items-center"
             style="background: linear-gradient(135deg,#1c1408,#0f0b04); padding:18px 24px;">
            <h5 class="text-white fw-bold font-sora mb-0">
                <i class="fas fa-history text-warning me-2"></i>Danh Sách Mã Đã Hết Hạn
            </h5>
            <a href="{{ route('manager.coupons.index') }}" class="btn btn-outline-light btn-sm px-3 rounded-3">
                <i class="fas fa-arrow-left me-1"></i>Về danh sách
            </a>
        </div>

        <div class="card-body p-4">
            {{-- Filter --}}
            <form method="GET" action="{{ route('manager.coupon.expired') }}" class="d-flex gap-3 mb-4 flex-wrap">
                <input type="text" name="code" class="form-control" style="max-width:220px;"
                       placeholder="Tìm theo mã..." value="{{ request('code') }}">
                <button type="submit" class="btn btn-secondary px-4 rounded-3">
                    <i class="fas fa-search me-1"></i>Lọc
                </button>
                <a href="{{ route('manager.coupon.expired') }}" class="btn btn-outline-secondary rounded-3">
                    <i class="fas fa-times"></i>
                </a>
            </form>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr style="background:var(--bg-base);">
                            <th style="font-size:0.78rem; text-transform:uppercase; color:var(--text-muted); padding:12px 16px;">#</th>
                            <th style="font-size:0.78rem; text-transform:uppercase; color:var(--text-muted); padding:12px 16px;">Mã code</th>
                            <th style="font-size:0.78rem; text-transform:uppercase; color:var(--text-muted); padding:12px 16px;">Loại / Giá trị</th>
                            <th style="font-size:0.78rem; text-transform:uppercase; color:var(--text-muted); padding:12px 16px;">Đơn tối thiểu</th>
                            <th style="font-size:0.78rem; text-transform:uppercase; color:var(--text-muted); padding:12px 16px;">Lượt dùng</th>
                            <th style="font-size:0.78rem; text-transform:uppercase; color:var(--text-muted); padding:12px 16px;">Ngày hết hạn</th>
                            <th style="font-size:0.78rem; text-transform:uppercase; color:var(--text-muted); padding:12px 16px; text-align:center;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($coupons as $coupon)
                            <tr>
                                <td class="text-muted small">{{ $coupon->id }}</td>
                                <td>
                                    <span class="fw-bold" style="font-family:monospace; letter-spacing:1.5px; color:#d97706;">
                                        {{ $coupon->code }}
                                    </span>
                                </td>
                                <td>
                                    @if($coupon->type == 'percent')
                                        <span class="badge bg-info text-dark me-1">%</span>
                                        {{ rtrim(rtrim($coupon->value, '0'), '.') }}%
                                    @else
                                        <span class="badge bg-secondary me-1">₫</span>
                                        {{ number_format($coupon->value, 0, ',', '.') }}₫
                                    @endif
                                </td>
                                <td>
                                    {{ $coupon->min_order_value > 0 ? number_format($coupon->min_order_value, 0, ',', '.') . '₫' : '—' }}
                                </td>
                                <td>
                                    {{ $coupon->used_count }}
                                    @if($coupon->quantity > 0)
                                        / {{ $coupon->quantity }}
                                    @else
                                        <span class="badge bg-success ms-1">Vô hạn</span>
                                    @endif
                                </td>
                                <td class="text-danger small fw-bold">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $coupon->end_date ? $coupon->end_date->format('d/m/Y H:i') : '—' }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('manager.coupons.edit', $coupon->id) }}" class="btn btn-warning btn-sm rounded-2 me-1" title="Sửa ngày">
                                        <i class="fas fa-edit me-1"></i>Sửa ngày
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-50 d-block"></i>
                                    Không có mã giảm giá nào hết hạn.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $coupons->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
