@if(isset($booking) && $booking)
@php
    $movieName = $booking->showtime->movie->title ?? 'Phim';
    $showtimeStart = $booking->showtime && $booking->showtime->start_time ? \Carbon\Carbon::parse($booking->showtime->start_time)->format('H:i') : '';
    $seats = $booking->bookedSeats->map(function($bs) {
        return $bs->seat ? $bs->seat->row_name . $bs->seat->seat_number : '';
    })->filter()->join(', ');
    $bookingTime = $booking->booking_time->timestamp * 1000;
    $durationMs = \App\Services\BookingService::getHoldDuration() * 60 * 1000;
    $expiresAtMs = $bookingTime + $durationMs;
@endphp

<!-- Floating Booking Widget - Bottom Left -->
<style>
    #active-booking-indicator {
        position: fixed;
        bottom: 24px;
        left: 24px;
        z-index: 9999;
        width: 320px;
        max-width: calc(100vw - 48px);
        font-size: 0.875rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        animation: slideInWidget 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }
    #active-booking-indicator .widget-card {
        background: rgba(15, 23, 42, 0.92);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(239, 68, 68, 0.25);
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255,255,255,0.05), 0 0 20px rgba(239, 68, 68, 0.08);
        overflow: hidden;
    }
    #active-booking-indicator .widget-header {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(239, 68, 68, 0.05) 100%);
        border-bottom: 1px solid rgba(239, 68, 68, 0.15);
        padding: 12px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        user-select: none;
    }
    #active-booking-indicator .widget-header:hover {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(239, 68, 68, 0.08) 100%);
    }
    #active-booking-indicator .widget-body {
        padding: 14px 16px 16px;
        max-height: 300px;
        overflow: hidden;
        transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1), padding 0.35s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s ease;
        opacity: 1;
    }
    #active-booking-indicator.collapsed .widget-body {
        max-height: 0;
        padding-top: 0;
        padding-bottom: 0;
        opacity: 0;
    }
    #active-booking-indicator .widget-toggle-icon {
        transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    #active-booking-indicator.collapsed .widget-toggle-icon {
        transform: rotate(180deg);
    }
    #active-booking-indicator .countdown-ring {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(239, 68, 68, 0.1);
        border: 2px solid rgba(239, 68, 68, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: monospace;
        font-weight: 700;
        font-size: 0.8rem;
        color: #f87171;
        flex-shrink: 0;
    }
    #active-booking-indicator .countdown-ring.critical {
        border-color: #ef4444;
        animation: pulseBorder 1s ease-in-out infinite;
        color: #ef4444;
    }
    #active-booking-indicator .widget-btn {
        display: block;
        width: 100%;
        text-align: center;
        padding: 9px 12px;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.2s ease;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }
    #active-booking-indicator .widget-btn-primary {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #fff;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
    }
    #active-booking-indicator .widget-btn-primary:hover {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.45);
        transform: translateY(-1px);
    }
    #active-booking-indicator .widget-btn-secondary {
        background: rgba(71, 85, 105, 0.5);
        color: #cbd5e1;
    }
    #active-booking-indicator .widget-btn-secondary:hover {
        background: rgba(71, 85, 105, 0.7);
        color: #fff;
    }
    @keyframes slideInWidget {
        from { transform: translateY(30px) scale(0.95); opacity: 0; }
        to { transform: translateY(0) scale(1); opacity: 1; }
    }
    @keyframes pulseBorder {
        0%, 100% { border-color: #ef4444; box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.3); }
        50% { border-color: #f87171; box-shadow: 0 0 12px 2px rgba(239, 68, 68, 0.2); }
    }
    @media (max-width: 400px) {
        #active-booking-indicator { width: calc(100vw - 32px); left: 16px; bottom: 16px; }
    }
</style>

<div id="active-booking-indicator">
    <div class="widget-card">
        {{-- Header - click to toggle --}}
        <div class="widget-header" onclick="toggleBookingWidget()">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:30px; height:30px; border-radius:50%; background:rgba(239,68,68,0.15); display:flex; align-items:center; justify-content:center; color:#f87171; font-size:14px; flex-shrink:0;">
                    <i class="fa-solid fa-ticket"></i>
                </div>
                <span style="color:#f1f5f9; font-weight:700; font-size:0.85rem;">Đang giữ ghế</span>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <span id="active-booking-countdown" style="font-family:monospace; font-weight:700; font-size:0.85rem; color:#f87171;">00:00</span>
                <i class="fa-solid fa-chevron-down widget-toggle-icon" style="color:#94a3b8; font-size:11px;"></i>
            </div>
        </div>

        {{-- Body - collapsible --}}
        <div class="widget-body">
            {{-- Movie & Seat Info --}}
            <div style="margin-bottom:14px;">
                <div style="color:#f1f5f9; font-weight:600; font-size:0.85rem; margin-bottom:4px; line-height:1.3; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                    {{ $movieName }}
                </div>
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    @if($showtimeStart)
                    <span style="display:inline-flex; align-items:center; gap:4px; background:rgba(239,68,68,0.1); color:#fca5a5; padding:3px 8px; border-radius:6px; font-size:0.75rem; font-weight:500;">
                        <i class="fa-regular fa-clock" style="font-size:10px;"></i> {{ $showtimeStart }}
                    </span>
                    @endif
                    <span style="display:inline-flex; align-items:center; gap:4px; background:rgba(100,116,139,0.25); color:#94a3b8; padding:3px 8px; border-radius:6px; font-size:0.75rem; font-weight:500;">
                        <i class="fa-solid fa-couch" style="font-size:10px;"></i> {{ $seats }}
                    </span>
                </div>
            </div>

            {{-- Action Button --}}
            <div>
                <a href="{{ route('booking.select-seats', $booking->showtime_id) }}" class="widget-btn widget-btn-primary" onclick="document.getElementById('active-booking-indicator').style.display='none'">
                    <i class="fa-solid fa-arrow-right" style="margin-right:6px; font-size:11px;"></i>Tiếp tục thanh toán
                </a>
            </div>
        </div>
    </div>
</div>

@if(session('show_active_booking_modal'))
<!-- Modal -->
<div id="active-booking-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm">
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 max-w-md w-full shadow-2xl mx-4 transform transition-all">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-primary/20 text-primary rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Bạn đang có đơn đặt vé chưa hoàn tất!</h3>
            <p class="text-slate-400 text-sm">Vui lòng hoàn thành hoặc hủy đơn đặt vé hiện tại trước khi bắt đầu đặt phim mới.</p>
        </div>
        
        <div class="bg-slate-900 rounded-lg p-4 mb-6 border border-slate-700">
            <div class="font-medium text-white mb-1">{{ $movieName }}</div>
            <div class="text-sm text-slate-400 flex justify-between">
                <span>Ghế: {{ $seats }}</span>
                <span class="text-primary font-mono" id="modal-countdown">00:00</span>
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <a href="{{ route('booking.select-seats', $booking->showtime_id) }}" class="w-full text-center px-4 py-3 bg-primary hover:bg-red-700 text-white rounded-lg font-bold transition-colors">
                Tiếp tục đơn hiện tại
            </a>
            <button type="button" onclick="cancelActiveBooking({{ $booking->id }})" class="w-full px-4 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-medium transition-colors">
                Hủy đơn hiện tại
            </button>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
// Toggle collapse/expand widget
function toggleBookingWidget() {
    const widget = document.getElementById('active-booking-indicator');
    if (widget) widget.classList.toggle('collapsed');
}

document.addEventListener('DOMContentLoaded', function() {
    const expiresAtMs = {{ $expiresAtMs }};
    const countdownEl = document.getElementById('active-booking-countdown');
    const modalCountdownEl = document.getElementById('modal-countdown');
    const indicatorEl = document.getElementById('active-booking-indicator');
    const modalEl = document.getElementById('active-booking-modal');
    
    function updateCountdown() {
        const now = new Date().getTime();
        const distance = expiresAtMs - now;
        
        if (distance <= 0) {
            if (countdownEl) countdownEl.innerHTML = "00:00";
            if (modalCountdownEl) modalCountdownEl.innerHTML = "00:00";
            // Do not force reload, just hide the UI element
            if (indicatorEl) indicatorEl.style.display = 'none';
            if (modalEl) modalEl.style.display = 'none';
            return;
        }
        
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        const text = (minutes < 10 ? "0" : "") + minutes + ":" + (seconds < 10 ? "0" : "") + seconds;
        if (countdownEl) countdownEl.innerHTML = text;
        if (modalCountdownEl) modalCountdownEl.innerHTML = text;
        
        if (distance < 120000) { // < 2 minutes -> Critical
            if (countdownEl) {
                countdownEl.style.color = '#ef4444';
                countdownEl.style.animation = 'pulseBorder 1s ease-in-out infinite';
            }
            if (modalCountdownEl) {
                modalCountdownEl.classList.add('text-red-500', 'animate-pulse');
            }
        }
    }
    
    updateCountdown();
    setInterval(updateCountdown, 1000);
});

function cancelActiveBooking(bookingId) {
    if(!confirm('Bạn có chắc chắn muốn hủy đơn đặt vé hiện tại? Ghế sẽ được nhả ra cho người khác.')) return;
    
    // Call explicit cancel API
    fetch('{{ route('api.booking.cancel-explicit') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ booking_id: bookingId })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Có lỗi xảy ra khi hủy vé.');
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi gọi hệ thống.');
    });
}
</script>
@endpush
@endif
