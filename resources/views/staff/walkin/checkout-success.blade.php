@extends('layouts.staff')

@section('title', 'Thanh Toán Thành Công (POS)')
@section('page_title', 'Đặt Vé Thành Công')

@section('extra_css')
<style>
    .success-card-pos {
        background: var(--bg-surface, #ffffff);
        border-radius: 24px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.08);
        border: 1px solid var(--border-light, #e2e8f0);
        overflow: hidden;
    }
    .success-badge-glow {
        width: 90px;
        height: 90px;
        background: rgba(16, 185, 129, 0.12);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        box-shadow: 0 0 0 10px rgba(16, 185, 129, 0.05);
        animation: pulseGreen 2s infinite;
    }
    @keyframes pulseGreen {
        0% { transform: scale(0.98); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.3); }
        70% { transform: scale(1); box-shadow: 0 0 0 15px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.98); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .pos-bill-box {
        background: #f8fafc;
        border: 1.5px dashed #cbd5e1;
        border-radius: 16px;
        padding: 20px;
    }
    .dark-theme .pos-bill-box {
        background: #1e293b;
        border-color: #334155;
    }
</style>
@endsection

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="success-card-pos text-center p-4 p-md-5">
                <!-- Icon Animated -->
                <div class="success-badge-glow">
                    <i class="fas fa-check-circle text-success" style="font-size: 3.5rem;"></i>
                </div>

                <h3 class="fw-bold mb-1 font-sora text-success">Thanh Toán Hoàn Tất!</h3>
                <p class="text-muted small mb-4">Giao dịch bán vé tại quầy đã được ghi nhận và in vé thành công.</p>
                
                <!-- Bill Summary Box -->
                <div class="pos-bill-box text-start mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom border-light">
                        <span class="text-muted small">Mã Đặt Vé:</span>
                        <span class="fw-bold text-dark font-monospace fs-5">{{ $booking['booking_code'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Phim:</span>
                        <span class="fw-bold text-dark">{{ $booking['movie_title'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Ghế Đã Đặt:</span>
                        <span class="fw-bold text-primary">
                            @foreach($booking['seats'] as $s)
                                <span class="badge bg-primary me-1">{{ $s->row_name }}{{ $s->seat_number }}</span>
                            @endforeach
                            <small class="text-muted">({{ count($booking['seats']) }} vé)</small>
                        </span>
                    </div>

                    @if(!empty($booking['combos']) && count($booking['combos']) > 0)
                        <div class="border-top pt-2 mt-2">
                            <span class="text-muted fw-bold small text-uppercase"><i class="fas fa-popcorn text-warning me-1"></i>Bắp Nước (Phiếu riêng):</span>
                            @foreach($booking['combos'] as $combo)
                                <div class="d-flex justify-content-between text-muted small mt-1">
                                    <span><strong>{{ $combo->quantity }}x</strong> {{ $combo->name }}</span>
                                    <span class="fw-bold text-dark">{{ number_format($combo->price * $combo->quantity) }}₫</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="border-top pt-3 mt-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold fs-5 text-dark font-sora">Số Tiền Đã Thu:</span>
                        <span class="fw-bold fs-3 text-danger font-sora">{{ number_format($booking['final_total']) }}₫</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-3 mb-2">
                    <button type="button" class="btn btn-success btn-lg fw-bold rounded-4 shadow py-3 fs-5" onclick="printTickets()">
                        <i class="fas fa-print me-2"></i>IN VÉ XEM PHIM & PHIẾU COMBO
                    </button>
                    
                    <a href="{{ route('staff.walkin.movies') }}" class="btn btn-outline-primary btn-lg fw-bold rounded-4 py-2">
                        <i class="fas fa-plus-circle me-2"></i>Bán Đơn Vé Mới
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_js')
<script>
    function printTickets() {
        const printUrl = "{{ route('staff.ticket.print', ['type' => 'booking', 'id' => $booking['booking_id']]) }}";
        let iframe = document.getElementById('print-iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'print-iframe';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
        }
        iframe.src = printUrl;
    }

    @if(request()->query('auto_print'))
    window.addEventListener('DOMContentLoaded', () => {
        setTimeout(printTickets, 400);
    });
    @endif
</script>
@endsection
