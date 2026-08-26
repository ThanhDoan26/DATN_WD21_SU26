@extends('layouts.staff')

@section('page_title', 'Thanh Toán Thành Công')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card text-center border-0 shadow-lg rounded-4 overflow-hidden" style="background: var(--bg-surface);">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center" style="width: 90px; height: 90px;">
                            <i class="fas fa-check-circle fa-4x"></i>
                        </div>
                    </div>

                    <h2 class="fw-extrabold font-sora text-ink mb-2">Thanh Toán Hoàn Tất!</h2>
                    <p class="text-muted mb-4">Vé xem phim và hóa đơn đã được tạo thành công tại quầy POS.</p>
                    
                    <div class="p-4 rounded-4 text-start mb-4 border border-light" style="background: var(--bg-base);">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted font-sora small uppercase fw-bold">Mã Đơn Vé POS:</span>
                            <span class="fw-extrabold text-amber fs-5 font-sora">{{ $booking['booking_code'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Tên Phim:</span>
                            <span class="fw-bold text-ink">{{ $booking['movie_title'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Số Ghế Mua:</span>
                            <span class="badge bg-amber text-dark font-sora fw-bold">{{ count($booking['seats']) }} Ghế</span>
                        </div>
                        <hr class="my-3 border-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold font-sora text-ink fs-6">SỐ TIỀN ĐÃ THU MẶT:</span>
                            <span class="fw-extrabold fs-3 text-danger font-sora">{{ number_format($booking['final_total']) }} ₫</span>
                        </div>
                    </div>

                    <a href="{{ route('staff.walkin.movies') }}" class="btn btn-amber btn-lg w-100 fw-extrabold font-sora rounded-3 mb-3 shadow-sm">
                        <i class="fas fa-plus-circle me-2"></i>TẠO ĐƠN HÀNG VÉ MỚI
                    </a>
                    
                    <button type="button" class="btn btn-outline-secondary w-100 rounded-3 fw-bold font-sora" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>In Hóa Đơn Khách Hàng
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_css')
<style>
    .btn-amber {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #ffffff;
        border: none;
    }
    .btn-amber:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        color: #ffffff;
    }
</style>
@endsection
