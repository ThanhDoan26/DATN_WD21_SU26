@extends('layouts.staff')

@section('title', 'Danh sách & Tra cứu Mã Giảm Giá - Staff')
@section('page_title', 'Danh sách Mã Giảm Giá')

@section('content')
<div class="container-fluid p-0">

    {{-- Search & Filter Section --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1 text-dark"><i class="fas fa-tags text-warning me-2"></i>Tra cứu Mã Giảm Giá Ưu Đãi</h5>
                    <p class="text-muted small mb-0">Tra cứu nhanh điều kiện và hạn dùng mã giảm giá để hỗ trợ khách hàng tại quầy vé.</p>
                </div>
                <a href="{{ route('staff.walkin.movies') }}" class="btn btn-warning btn-sm fw-semibold">
                    <i class="fas fa-cash-register me-1"></i> Đến màn hình bán vé quầy
                </a>
            </div>

            <form method="GET" action="{{ route('staff.coupons.index') }}" class="row g-2">
                <div class="col-md-7 col-sm-8">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="code" class="form-control border-start-0" placeholder="Nhập mã ưu đãi (VD: SUMMER2026)..." value="{{ request('code') }}">
                    </div>
                </div>
                <div class="col-md-3 col-sm-4">
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="ACTIVE" {{ request('status') === 'ACTIVE' || (!request()->has('status') && request('status') !== '') ? 'selected' : '' }}>Đang hiệu lực (ACTIVE)</option>
                        <option value="INACTIVE" {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>Đã khóa (INACTIVE)</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-12">
                    <button type="submit" class="btn btn-dark w-100 fw-semibold"><i class="fas fa-search me-1"></i> Tra cứu</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Cards Grid --}}
    <div class="row g-3">
        @forelse($coupons as $coupon)
            @php
                $isExpired = $coupon->end_date && $coupon->end_date->isPast();
                $isNotStarted = $coupon->start_date && $coupon->start_date->isFuture();
                $isOutOfStock = $coupon->quantity > 0 && $coupon->used_count >= $coupon->quantity;
                $isValid = $coupon->status === 'ACTIVE' && !$isExpired && !$isNotStarted && !$isOutOfStock;
            @endphp
            <div class="col-xl-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden position-relative" style="border-left: 5px solid {{ $isValid ? '#10b981' : '#ef4444' }} !important;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge {{ $isValid ? 'bg-success' : 'bg-secondary' }} mb-1">
                                    @if($isValid) <i class="fas fa-check-circle me-1"></i>Đang hiệu lực @elseif($isExpired) Hết hạn @elseif($isNotStarted) Chưa diễn ra @elseif($isOutOfStock) Hết lượt dùng @else Đã khóa @endif
                                </span>
                                <div class="font-monospace fw-bold text-dark fs-5 tracking-wider">{{ $coupon->code }}</div>
                            </div>
                            <div class="text-end">
                                <span class="fs-4 fw-extrabold {{ $isValid ? 'text-success' : 'text-muted' }}">
                                    @if($coupon->type == 'percent')
                                        -{{ rtrim(rtrim($coupon->value, '0'), '.') }}%
                                    @else
                                        -{{ number_format($coupon->value, 0, ',', '.') }}đ
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="bg-light p-2.5 rounded-2 mb-3 text-muted small" style="font-size: 0.82rem;">
                            <div class="d-flex justify-content-between mb-1">
                                <span><i class="fas fa-shopping-cart text-secondary me-1"></i>Đơn tối thiểu:</span>
                                <strong class="text-dark">{{ number_format($coupon->min_order_value, 0, ',', '.') }} VNĐ</strong>
                            </div>
                            @if($coupon->type == 'percent' && $coupon->max_discount_amount)
                            <div class="d-flex justify-content-between mb-1">
                                <span><i class="fas fa-shield-alt text-secondary me-1"></i>Giảm tối đa:</span>
                                <strong class="text-dark">{{ number_format($coupon->max_discount_amount, 0, ',', '.') }} VNĐ</strong>
                            </div>
                            @endif
                            <div class="d-flex justify-content-between">
                                <span><i class="fas fa-ticket-alt text-secondary me-1"></i>Lượt còn lại:</span>
                                <strong class="text-dark">
                                    @if($coupon->quantity == 0 || $coupon->quantity === null)
                                        Vô hạn
                                    @else
                                        {{ number_format(max(0, $coupon->quantity - $coupon->used_count)) }} / {{ number_format($coupon->quantity) }}
                                    @endif
                                </strong>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top text-muted" style="font-size: 0.76rem;">
                            <div>
                                <i class="far fa-calendar-alt me-1"></i>
                                Hạn: {{ $coupon->end_date ? $coupon->end_date->format('d/m/Y H:i') : 'Vô thời hạn' }}
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-warning py-0 px-2 text-dark font-monospace" style="font-size: 0.75rem;" onclick="navigator.clipboard.writeText('{{ $coupon->code }}'); alert('Đã sao chép mã {{ $coupon->code }}');">
                                <i class="far fa-copy me-1"></i>Sao chép
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm p-5 text-center text-muted">
                    <i class="fas fa-tags fs-1 mb-3 text-secondary opacity-50"></i>
                    <h5>Không tìm thấy mã giảm giá phù hợp</h5>
                    <p class="small mb-0">Thử tìm kiếm lại với mã khác hoặc bỏ lọc trạng thái.</p>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $coupons->links() }}
    </div>

</div>
@endsection
