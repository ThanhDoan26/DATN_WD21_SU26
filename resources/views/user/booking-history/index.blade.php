@extends('layouts.frontend')

@section('title', 'Lịch sử đặt vé')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-10">
        <h2 class="text-4xl font-bold text-white mb-2">
            <i class="fas fa-history text-primary mr-3"></i>Lịch sử đặt vé
        </h2>
        <p class="text-slate-400">Xem lại danh sách các vé bạn đã đặt</p>
    </div>

    <div class="bg-slate-800/50 backdrop-blur-md rounded-2xl border border-slate-700/50 overflow-hidden shadow-2xl">
        @if($bookings->isEmpty())
            <div class="text-center py-20">
                <div class="w-20 h-20 bg-slate-700/50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-500">
                    <i class="fas fa-ticket-alt text-4xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">Bạn chưa có vé nào</h3>
                <p class="text-slate-400 mb-8">Bắt đầu khám phá những bộ phim hấp dẫn ngay hôm nay!</p>
                <a href="{{ route('home') }}" class="bg-primary hover:bg-red-700 text-white px-8 py-3 rounded-full font-bold transition-all transform hover:scale-105 inline-block">
                    Đặt vé ngay
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-900/50 border-b border-slate-700/50">
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider">Mã đơn</th>
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider">Phim</th>
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider">Suất chiếu</th>
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider">Tổng tiền</th>
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider">Trạng thái</th>
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider">Ngày đặt</th>
                            <th class="px-6 py-4 text-right text-slate-400 font-semibold uppercase text-xs tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @foreach($bookings as $booking)
                            <tr class="hover:bg-white/5 transition-colors group">
                                <td class="px-6 py-5">
                                    <span class="font-mono text-primary font-bold">#{{ $booking->booking_code }}</span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="font-bold text-white group-hover:text-primary transition-colors">
                                        {{ $booking->showtime->movie->title }}
                                    </div>
                                    <div class="text-xs text-slate-500 mt-1">
                                        <i class="fas fa-video mr-1"></i>{{ $booking->showtime->room->format }}
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-white">{{ $booking->showtime->start_time->format('H:i') }}</div>
                                    <div class="text-xs text-slate-500">{{ $booking->showtime->start_time->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="text-white font-bold">{{ number_format($booking->total_price) }}đ</span>
                                </td>
                                <td class="px-6 py-5">
                                    @php
                                        $statusClasses = [
                                            'Pending' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                                            'Paid' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                            'Cancelled' => 'bg-rose-500/10 text-rose-500 border-rose-500/20',
                                            'Used' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                                        ];
                                        $statusLabel = [
                                            'Pending' => 'Chờ thanh toán',
                                            'Paid' => 'Đã thanh toán',
                                            'Cancelled' => 'Đã hủy',
                                            'Used' => 'Đã sử dụng',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 text-xs font-bold rounded-full border {{ $statusClasses[$booking->status] ?? 'bg-slate-500/10 text-slate-500' }}">
                                        {{ $statusLabel[$booking->status] ?? $booking->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-slate-500 text-sm">
                                    {{ $booking->booking_time->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <a href="{{ route('booking.history.show', $booking->booking_code) }}" class="inline-flex items-center gap-2 bg-slate-700 hover:bg-primary text-white text-xs font-bold px-4 py-2 rounded-lg transition-all transform hover:scale-105">
                                        Chi tiết <i class="fas fa-chevron-right text-[10px]"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($bookings->hasPages())
                <div class="px-6 py-6 border-t border-slate-700/50 bg-slate-900/30">
                    {{ $bookings->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
