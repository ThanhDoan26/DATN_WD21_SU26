@extends('layouts.staff')

@section('title', 'Quản lý Mã Giảm Giá')
@section('page_title', 'Quản lý Mã Giảm Giá')

@section('extra_css')
<style>
    .coupon-table-card {
        background: var(--bg-surface);
        border-radius: 18px;
        border: 1px solid var(--border-light);
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    .coupon-table-card .card-top {
        background: linear-gradient(135deg, #1c1408 0%, #0f0b04 100%);
        padding: 18px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .filter-bar {
        background: var(--bg-base);
        border-radius: 12px;
        padding: 16px 20px;
        margin: 20px 24px 0;
        border: 1px solid var(--border-light);
    }
    .table thead th {
        background: var(--bg-base) !important;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-muted);
        font-weight: 700;
        padding: 12px 16px;
        border-bottom: 2px solid var(--border-light);
    }
    .table tbody td { padding: 14px 16px; vertical-align: middle; font-size: 0.875rem; }
    .table tbody tr:hover { background: var(--bg-base); }
    .code-badge {
        font-family: 'Courier New', monospace;
        font-weight: 800;
        font-size: 0.88rem;
        letter-spacing: 1.5px;
        background: linear-gradient(135deg, rgba(217,119,6,0.1), rgba(245,158,11,0.08));
        border: 1px solid rgba(245,158,11,0.3);
        color: #d97706;
        padding: 4px 10px;
        border-radius: 8px;
    }
    .progress-bar-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .qty-bar {
        flex: 1;
        height: 6px;
        background: var(--border-light);
        border-radius: 3px;
        overflow: hidden;
        min-width: 60px;
    }
    .qty-bar-fill { height: 100%; border-radius: 3px; }
</style>
@endsection

@section('content')
<div class="container-fluid p-4">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="coupon-table-card">
        {{-- Header --}}
        <div class="card-top">
            <div>
                <h5 class="text-white fw-bold font-sora mb-0">
                    <i class="fas fa-tag text-warning me-2"></i>Danh sách Mã Giảm Giá
                </h5>
                <p class="text-warning opacity-75 small mb-0 mt-1">Quản lý toàn bộ voucher trong hệ thống</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('staff.coupon.expired') }}" class="btn btn-outline-light btn-sm px-3 rounded-3">
                    <i class="fas fa-history me-1"></i>Mã hết hạn
                </a>

            </div>
        </div>

        {{-- Filter --}}
        <div class="filter-bar">
            <form method="GET" action="{{ route('staff.coupons.index') }}" class="d-flex gap-3 align-items-end flex-wrap">
                <div>
                    <label class="form-label small fw-bold text-muted mb-1">Tìm mã</label>
                    <input type="text" name="code" class="form-control form-control-sm" placeholder="Nhập mã..." value="{{ request('code') }}" style="min-width:180px;">
                </div>
                <div>
                    <label class="form-label small fw-bold text-muted mb-1">Trạng thái</label>
                    <select name="status" class="form-select form-select-sm" style="min-width:150px;">
                        <option value="">Tất cả</option>
                        <option value="ACTIVE"   {{ request('status') === 'ACTIVE'   ? 'selected' : '' }}>✅ Hoạt động</option>
                        <option value="INACTIVE" {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>🔒 Bị khoá</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm px-4 rounded-3">
                        <i class="fas fa-search me-1"></i>Lọc
                    </button>
                    <a href="{{ route('staff.coupons.index') }}" class="btn btn-outline-secondary btn-sm px-3 rounded-3">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="p-4">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Mã code</th>
                            <th>Loại / Giá trị</th>
                            <th>Đơn tối thiểu</th>
                            <th>Lượt dùng</th>
                            <th>Thời gian hiệu lực</th>
                            <th>Trạng thái</th>

                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($coupons as $coupon)
                            <tr>
                                <td class="text-muted small">{{ $coupon->id }}</td>
                                <td>
                                    <span class="code-badge">{{ $coupon->code }}</span>
                                </td>
                                <td>
                                    @if($coupon->type == 'percent')
                                        <span class="badge bg-info text-dark me-1">%</span>
                                        <strong>{{ rtrim(rtrim($coupon->value, '0'), '.') }}%</strong>
                                        @if($coupon->max_discount_amount)
                                            <br><span class="text-muted" style="font-size:0.75rem;">Tối đa {{ number_format($coupon->max_discount_amount, 0, ',', '.') }}₫</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary me-1">₫</span>
                                        <strong>{{ number_format($coupon->value, 0, ',', '.') }}₫</strong>
                                    @endif
                                </td>
                                <td>
                                    @if($coupon->min_order_value > 0)
                                        {{ number_format($coupon->min_order_value, 0, ',', '.') }}₫
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $qty = $coupon->quantity;
                                        $used = $coupon->used_count;
                                        $pct = ($qty > 0) ? min(100, round($used / $qty * 100)) : 0;
                                        $barColor = $pct >= 90 ? '#ef4444' : ($pct >= 60 ? '#f59e0b' : '#10b981');
                                    @endphp
                                    @if($qty == 0)
                                        <span class="badge bg-success">Vô hạn</span>
                                        <small class="text-muted ms-1">{{ $used }} lượt</small>
                                    @else
                                        <div class="progress-bar-wrap">
                                            <span class="small fw-bold">{{ $used }}/{{ $qty }}</span>
                                            <div class="qty-bar">
                                                <div class="qty-bar-fill" style="width:{{ $pct }}%; background:{{ $barColor }};"></div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td style="font-size:0.8rem; min-width:140px;">
                                    <div class="text-muted">
                                        <i class="fas fa-play-circle text-success me-1"></i>
                                        {{ $coupon->start_date ? $coupon->start_date->format('d/m/Y H:i') : '—' }}
                                    </div>
                                    <div class="{{ $coupon->end_date && $coupon->end_date->isPast() ? 'text-danger fw-bold' : 'text-muted' }}">
                                        <i class="fas fa-stop-circle {{ $coupon->end_date && $coupon->end_date->isPast() ? 'text-danger' : 'text-secondary' }} me-1"></i>
                                        {{ $coupon->end_date ? $coupon->end_date->format('d/m/Y H:i') : '—' }}
                                    </div>
                                </td>
                                <td>
                                    @if($coupon->end_date && $coupon->end_date->isPast())
                                        <span class="badge bg-secondary">Hết hạn</span>
                                    @elseif($coupon->status === 'ACTIVE')
                                        <span class="badge bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge bg-danger">Bị khoá</span>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fa-3x mb-3 opacity-25 d-block"></i>
                                    Không có mã giảm giá nào.
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
