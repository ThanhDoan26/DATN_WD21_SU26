@extends('layouts.frontend')

@section('title', 'Lịch sử đặt vé')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-20">
    <div class="mb-8">
        <h2 class="text-4xl font-bold text-white mb-2 flex items-center gap-3">
            <i class="fas fa-ticket-alt text-primary"></i> Lịch sử đặt vé
        </h2>
        <p class="text-slate-400">Xem và quản lý các vé xem phim của bạn</p>
    </div>

    <!-- Status Tabs (Option A - Standard UX) -->
    <div class="flex items-center gap-3 mb-8 overflow-x-auto pb-2 border-b border-slate-700/60">
        <a href="{{ route('booking.history', ['status' => 'paid']) }}" 
           class="flex items-center gap-2 px-5 py-3 rounded-xl font-bold text-sm transition-all whitespace-nowrap {{ ($status ?? 'paid') === 'paid' ? 'bg-primary text-white shadow-lg shadow-red-500/30' : 'bg-slate-800/80 text-slate-400 hover:text-white hover:bg-slate-700/80' }}">
            <i class="fas fa-check-circle"></i>
            <span>Đã mua (Thành công)</span>
            <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ ($status ?? 'paid') === 'paid' ? 'bg-white/20 text-white' : 'bg-slate-700 text-slate-300' }}">
                {{ $counts['paid'] ?? 0 }}
            </span>
        </a>

        <a href="{{ route('booking.history', ['status' => 'cancelled']) }}" 
           class="flex items-center gap-2 px-5 py-3 rounded-xl font-bold text-sm transition-all whitespace-nowrap {{ ($status ?? '') === 'cancelled' ? 'bg-primary text-white shadow-lg shadow-red-500/30' : 'bg-slate-800/80 text-slate-400 hover:text-white hover:bg-slate-700/80' }}">
            <i class="fas fa-times-circle"></i>
            <span>Đã hủy</span>
            <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ ($status ?? '') === 'cancelled' ? 'bg-white/20 text-white' : 'bg-slate-700 text-slate-300' }}">
                {{ $counts['cancelled'] ?? 0 }}
            </span>
        </a>

        <a href="{{ route('booking.history', ['status' => 'all']) }}" 
           class="flex items-center gap-2 px-5 py-3 rounded-xl font-bold text-sm transition-all whitespace-nowrap {{ ($status ?? '') === 'all' ? 'bg-primary text-white shadow-lg shadow-red-500/30' : 'bg-slate-800/80 text-slate-400 hover:text-white hover:bg-slate-700/80' }}">
            <i class="fas fa-list"></i>
            <span>Tất cả</span>
            <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ ($status ?? '') === 'all' ? 'bg-white/20 text-white' : 'bg-slate-700 text-slate-300' }}">
                {{ $counts['all'] ?? 0 }}
            </span>
        </a>
    </div>

    <!-- Booking List Container -->
    <div class="bg-slate-800/50 backdrop-blur-md rounded-3xl border border-slate-700/50 overflow-hidden shadow-2xl">
        @if($bookings->isEmpty())
            <div class="text-center py-20 px-4">
                <div class="w-20 h-20 bg-slate-700/50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-500">
                    @if(($status ?? 'paid') === 'cancelled')
                        <i class="fas fa-ban text-4xl text-slate-500"></i>
                    @else
                        <i class="fas fa-ticket-alt text-4xl text-slate-500"></i>
                    @endif
                </div>

                @if(($status ?? 'paid') === 'paid')
                    <h3 class="text-2xl font-bold text-white mb-2">Bạn chưa có vé đã mua nào</h3>
                    <p class="text-slate-400 mb-8 max-w-md mx-auto">Các vé thanh toán thành công sẽ xuất hiện tại đây để bạn quét mã QR khi vào rạp.</p>
                    <a href="{{ route('home') }}" class="bg-primary hover:bg-red-700 text-white px-8 py-3 rounded-full font-bold transition-all transform hover:scale-105 inline-block shadow-lg shadow-red-500/30">
                        Đặt vé ngay
                    </a>
                @elseif(($status ?? '') === 'cancelled')
                    <h3 class="text-2xl font-bold text-white mb-2">Không có đơn hàng nào bị hủy</h3>
                    <p class="text-slate-400 mb-6">Bạn chưa có lịch sử đơn hàng nào bị hủy hoặc hết hạn.</p>
                @else
                    <h3 class="text-2xl font-bold text-white mb-2">Chưa có lịch sử đặt vé</h3>
                    <p class="text-slate-400 mb-8">Bắt đầu khám phá những bộ phim hấp dẫn ngay hôm nay!</p>
                    <a href="{{ route('home') }}" class="bg-primary hover:bg-red-700 text-white px-8 py-3 rounded-full font-bold transition-all transform hover:scale-105 inline-block">
                        Đặt vé ngay
                    </a>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-900/70 border-b border-slate-700/60">
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider">Mã đơn</th>
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider">Phim & Rạp</th>
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider">Suất chiếu</th>
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider">Ghế</th>
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
                                    <div class="font-bold text-white group-hover:text-primary transition-colors text-base">
                                        {{ $booking->showtime->movie->title ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-slate-400 mt-1 flex items-center gap-2">
                                        <span><i class="fas fa-map-marker-alt text-primary/80 mr-1"></i>{{ $booking->showtime->room->cinema->name ?? 'N/A' }}</span>
                                        <span>•</span>
                                        <span><i class="fas fa-video mr-1"></i>{{ $booking->showtime->room->format ?? '2D' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-white font-bold">{{ $booking->showtime->start_time ? $booking->showtime->start_time->format('H:i') : 'N/A' }}</div>
                                    <div class="text-xs text-slate-400">{{ $booking->showtime->start_time ? $booking->showtime->start_time->format('d/m/Y') : '' }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    @php
                                        $seatNames = $booking->bookedSeats->map(function($bs) {
                                            return ($bs->seat->row_name ?? '') . ($bs->seat->seat_number ?? '');
                                        })->filter()->implode(', ');
                                    @endphp
                                    <span class="text-slate-300 font-medium text-sm">{{ $seatNames ?: 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="text-white font-bold text-base">{{ number_format($booking->total_price) }}đ</span>
                                </td>
                                <td class="px-6 py-5">
                                    @php
                                        $statusClasses = [
                                            \App\Models\Booking::STATUS_PENDING   => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                                            \App\Models\Booking::STATUS_PAID      => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                            \App\Models\Booking::STATUS_CANCELLED => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                                            \App\Models\Booking::STATUS_EXPIRED   => 'bg-slate-500/10 text-slate-400 border-slate-500/30',
                                            \App\Models\Booking::STATUS_USED      => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                                            'pending'   => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                                            'paid'      => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                            'cancelled' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                                            'expired'   => 'bg-slate-500/10 text-slate-400 border-slate-500/30',
                                            'used'      => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                                        ];
                                        $normalizedStatus = strtolower($booking->status ?? '');
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full border {{ $statusClasses[$normalizedStatus] ?? 'bg-slate-500/10 text-slate-400' }}">
                                        @if($booking->isPaid())
                                            <i class="fas fa-check-circle text-[10px]"></i>
                                        @elseif($booking->isUsed())
                                            <i class="fas fa-user-check text-[10px]"></i>
                                        @elseif($booking->isCancelled())
                                            <i class="fas fa-times-circle text-[10px]"></i>
                                        @elseif($booking->isExpired())
                                            <i class="fas fa-clock text-[10px]"></i>
                                        @elseif($booking->isPending())
                                            <i class="fas fa-hourglass-half text-[10px]"></i>
                                        @endif
                                        {{ $booking->status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-slate-400 text-sm whitespace-nowrap">
                                    {{ $booking->booking_time ? $booking->booking_time->format('d/m/Y H:i') : ($booking->created_at ? $booking->created_at->format('d/m/Y H:i') : '') }}
                                </td>
                                <td class="px-6 py-5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('booking.history.show', $booking->booking_code) }}" 
                                           class="inline-flex items-center gap-2 bg-slate-700 hover:bg-primary text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all transform hover:scale-105 shadow-md">
                                            <span>Chi tiết</span>
                                            <i class="fas fa-chevron-right text-[10px]"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($bookings->hasPages())
                <div class="px-6 py-6 border-t border-slate-700/50 bg-slate-900/40">
                    {{ $bookings->links('pagination::tailwind') }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
