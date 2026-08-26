@extends('layouts.staff')

@section('title', 'Thanh Toán Thành Công')
@section('page_title', 'Đặt Vé Thành Công')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card text-center shadow-lg border-0 rounded-4">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="fw-bold mb-2 text-success">Thanh Toán Hoàn Tất!</h2>
                    <p class="text-muted mb-4 fs-6">Đơn đặt vé tại quầy đã được thanh toán và ghi nhận thành công.</p>
                    
                    <div class="bg-light p-4 rounded-3 text-start mb-4 border">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Mã Đặt Vé:</span>
                            <span class="fw-bold text-dark fs-5">{{ $booking['booking_code'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Phim:</span>
                            <span class="fw-bold">{{ $booking['movie_title'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Ghế:</span>
                            <span class="fw-bold text-primary">
                                @foreach($booking['seats'] as $s)
                                    {{ $s->row_name }}{{ $s->seat_number }}@if(!$loop->last), @endif
                                @endforeach
                                ({{ count($booking['seats']) }} vé)
                            </span>
                        </div>
                        @if(!empty($booking['combos']) && count($booking['combos']) > 0)
                            <div class="border-top pt-2 mt-2">
                                <span class="text-muted fw-bold small">Bắp nước / Combo:</span>
                                @foreach($booking['combos'] as $combo)
                                    <div class="d-flex justify-content-between text-muted small mt-1">
                                        <span>{{ $combo->quantity }}x {{ $combo->name }}</span>
                                        <span>{{ number_format($combo->price * $combo->quantity) }}₫</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5">Số Tiền Đã Thu:</span>
                            <span class="fw-bold fs-4 text-danger">{{ number_format($booking['final_total']) }}₫</span>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="button" class="btn btn-success btn-lg fw-bold rounded-pill shadow-sm py-3 fs-5" onclick="printTickets()">
                            <i class="fas fa-print me-2"></i>In Vé Xem Phim (Khổ K80)
                        </button>
                        
                        <a href="{{ route('staff.walkin.movies') }}" class="btn btn-primary btn-lg fw-bold rounded-pill py-2">
                            <i class="fas fa-plus-circle me-2"></i>Tạo Chuyến Vé Mới
                        </a>
                    </div>
                    
                    <button class="btn btn-outline-secondary btn-sm w-100 rounded-pill" onclick="window.print()">
                        <i class="fas fa-file-invoice me-1"></i>In Hóa Đơn Thu Tiền
                    </button>
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
