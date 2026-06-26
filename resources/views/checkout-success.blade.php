@extends('layouts.frontend')

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
                            <div class="flex justify-between"><span>Thời gian đặt:</span><span>{{ \\Carbon\\Carbon::parse($booking['booking_time'])->format('H:i d/m/Y') }}</span></div>
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
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <a href="/" class="inline-flex items-center justify-center rounded-3xl bg-slate-900 border border-slate-700 px-6 py-4 text-center text-white font-semibold hover:bg-slate-800 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Quay về trang chính
                    </a>
                    <a href="/" class="inline-flex items-center justify-center rounded-3xl bg-primary px-6 py-4 text-center text-white font-semibold hover:bg-red-600 transition">
                        <i class="fas fa-ticket-alt mr-2"></i> Xem danh sách booking
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
