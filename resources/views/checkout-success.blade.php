@extends($layout ?? 'layouts.frontend')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-16">
        <div class="bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 overflow-hidden">
            <div class="bg-gradient-to-r from-primary to-red-600 p-10 text-center">
                <i class="fas fa-check-circle text-6xl text-white"></i>
                <h1 class="text-4xl font-bold text-white mt-6">Đặt vé thành công!</h1>
                <p class="mt-3 text-slate-200">Bạn đã giữ ghế thành công. Vui lòng hoàn tất thanh toán trước khi thời gian giữ vé hết.</p>
            </div>
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-slate-900 rounded-3xl p-6 border border-slate-700">
                        <h2 class="text-lg font-semibold mb-4 text-white">Thông tin đặt vé</h2>
                        <div class="space-y-3 text-sm text-slate-300">
                            <div class="flex justify-between"><span>Mã booking:</span><span class="font-semibold text-white">{{ $booking['booking_code'] }}</span></div>
                            <div class="flex justify-between"><span>Trạng thái:</span><span class="font-semibold text-emerald-400">{{ $booking['status'] }}</span></div>
                            <div class="flex justify-between"><span>Thời gian đặt:</span><span>{{ \Carbon\Carbon::parse($booking['booking_time'])->format('H:i d/m/Y') }}</span></div>
                            <div class="flex justify-between"><span>Tổng thanh toán:</span><span class="font-semibold text-white">{{ number_format($booking['total_price'], 0, ',', '.') }} đ</span></div>
                            <div class="flex justify-between"><span>Phương thức:</span><span>{{ $booking['payment_method'] ?? 'Chưa chọn' }}</span></div>
                        </div>
                    </div>
                    <div class="bg-slate-900 rounded-3xl p-6 border border-slate-700">
                        <h2 class="text-lg font-semibold mb-4 text-white">Ghế đã chọn</h2>
                        <div class="space-y-3 text-sm text-slate-300">
                            @foreach($booking['seats'] as $seat)
                                <div class="rounded-2xl bg-slate-800 p-4 border border-slate-700">
                                    <div class="flex justify-between gap-4">
                                        <div>
                                            <div class="font-semibold text-white">{{ $seat->row_name }}{{ $seat->seat_number }}</div>
                                            <div class="text-slate-400 text-xs">{{ $seat->seat_type }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-semibold text-white">{{ number_format($seat->price_at_booking, 0, ',', '.') }} đ</div>
                                            <div class="text-slate-500 text-xs">{{ $seat->status }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if(!empty($booking['combos']) && count($booking['combos']) > 0)
                            <h2 class="text-lg font-semibold mt-6 mb-4 text-white">Combo đã chọn</h2>
                            <div class="space-y-3 text-sm text-slate-300">
                                @foreach($booking['combos'] as $combo)
                                    <div class="rounded-2xl bg-slate-800 p-4 border border-slate-700">
                                        <div class="flex justify-between gap-4">
                                            <div>
                                                <div class="font-semibold text-white">{{ $combo->name }}</div>
                                                <div class="text-slate-400 text-xs">Số lượng: {{ $combo->quantity }}</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-semibold text-white">{{ number_format($combo->price * $combo->quantity, 0, ',', '.') }} đ</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                @if(session('success'))
                    <div class="bg-emerald-500/10 border border-emerald-500 text-emerald-400 p-4 rounded-2xl mb-6">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-rose-500/10 border border-rose-500 text-rose-400 p-4 rounded-2xl mb-6">
                        {{ session('error') }}
                    </div>
                @endif

                @if($booking['status'] === 'Pending')
                    <div class="flex justify-center">
                        <form action="{{ route('checkout.cancel') }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn vé này không? Ghế sẽ được giải phóng cho người khác.');">
                            @csrf
                            <input type="hidden" name="booking_id" value="{{ $booking['booking_id'] }}">
                            <button type="submit" class="inline-flex items-center justify-center rounded-3xl bg-slate-800 border border-slate-600 px-8 py-4 text-center text-rose-400 font-semibold hover:bg-slate-700 transition">
                                <i class="fas fa-times-circle mr-2"></i> Hủy đơn vé
                            </button>
                        </form>
                    </div>
                @else
                    <div class="mt-10 pt-8 border-t border-slate-700/50">
                        <div class="flex flex-col sm:flex-row justify-center items-center gap-4 sm:gap-6">
                            <a href="{{ route('home') }}" class="w-full sm:w-auto min-w-[220px] inline-flex items-center justify-center rounded-full bg-slate-800 border border-slate-600 px-8 py-3.5 text-slate-300 font-semibold hover:text-white hover:bg-slate-700 hover:border-slate-500 transition-all duration-300">
                                <i class="fas fa-arrow-left mr-2"></i> Quay về trang chính
                            </a>
                            <a href="{{ route('booking.history') }}" class="w-full sm:w-auto min-w-[220px] inline-flex items-center justify-center rounded-full bg-gradient-to-r from-primary to-red-600 px-8 py-3.5 text-white font-bold shadow-lg shadow-red-500/30 hover:shadow-red-500/50 hover:-translate-y-0.5 transition-all duration-300">
                                <i class="fas fa-ticket-alt mr-2"></i> Xem danh sách booking
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Thanh toán thành công — xóa toàn bộ timer và dữ liệu chọn ghế tạm trong sessionStorage
    sessionStorage.removeItem('booking_expires_at');
    Object.keys(sessionStorage).forEach(key => {
        if (key.startsWith('selectedSeats_showtime_')) {
            sessionStorage.removeItem(key);
        }
    });
</script>
@endpush
