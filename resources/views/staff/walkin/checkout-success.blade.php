@extends('layouts.staff')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card text-center shadow-lg border-0 rounded-4">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="fw-bold mb-3 text-success">Thanh Toán Hoàn Tất!</h2>
                    <p class="text-muted mb-4 fs-5">Vé đã được in và đơn hàng ghi nhận thành công.</p>
                    
                    <div class="bg-light p-4 rounded-3 text-start mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Mã Đặt Vé:</span>
                            <span class="fw-bold text-dark fs-5">{{ $booking['booking_code'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Phim:</span>
                            <span class="fw-bold">{{ $booking['movie_title'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Số Khách Mua:</span>
                            <span class="fw-bold">{{ count($booking['seats']) }} Vé</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold fs-5">Số Tiền Đã Thu:</span>
                            <span class="fw-bold fs-5 text-danger">{{ number_format($booking['final_total']) }}₫</span>
                        </div>
                    </div>

                    <a href="{{ route('staff.walkin.movies') }}" class="btn btn-primary btn-lg w-100 fw-bold rounded-pill">
                        <i class="fas fa-plus-circle me-2"></i>Tạo Chuyến Vé Mới
                    </a>
                    
                    <button class="btn btn-outline-secondary w-100 mt-3 rounded-pill fw-bold" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>In Hóa Đơn Lần Nữa
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
