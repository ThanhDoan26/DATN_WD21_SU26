@extends('layouts.frontend')

@section('title', 'Chi tiết vé #' . $booking->booking_code)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-3xl font-bold text-white">
            <i class="fas fa-ticket-alt text-primary mr-3"></i>Chi tiết đặt vé
        </h2>
        <a href="{{ route('booking.history') }}" class="text-slate-400 hover:text-white flex items-center gap-2 font-medium transition-colors">
            <i class="fas fa-arrow-left text-xs"></i> Quay lại
        </a>
    </div>

    <!-- Premium Ticket Design -->
    <div class="relative group">
        <!-- Decoration Dots for Ticket feel -->
        <div class="absolute -left-4 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-slate-900 z-20 hidden md:block"></div>
        <div class="absolute -right-4 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-slate-900 z-20 hidden md:block"></div>
        
        <div class="bg-slate-800 rounded-3xl overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.5)] border border-slate-700/50">
            <!-- Movie Banner Header -->
            <div class="relative h-48 md:h-64 overflow-hidden">
                <img src="https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Ticket Background" class="w-full h-full object-cover opacity-40 group-hover:scale-105 transition-transform duration-700" />
                <div class="absolute inset-0 bg-gradient-to-t from-slate-800 via-slate-800/60 to-transparent"></div>
                
                <div class="absolute bottom-6 left-8 right-8 flex justify-between items-end">
                    <div>
                        <span class="inline-block py-1 px-3 rounded-full bg-primary text-white text-[10px] font-bold uppercase tracking-wider mb-2">Vé xem phim</span>
                        <h3 class="text-3xl md:text-4xl font-black text-white uppercase tracking-tighter">{{ $booking->showtime->movie->title }}</h3>
                        <p class="text-slate-300 font-medium mt-1">
                            <i class="fas fa-map-marker-alt text-primary mr-2"></i>{{ $booking->showtime->room->cinema->name }}
                        </p>
                    </div>
                    <div class="text-right hidden sm:block">
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">Mã đặt vé</p>
                        <p class="text-2xl font-mono text-primary font-black">#{{ $booking->booking_code }}</p>
                    </div>
                </div>
            </div>

            <!-- Ticket Core Details -->
            <div class="p-8 md:p-10">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10 pb-10 border-b border-slate-700/50">
                    <!-- Column 1: Time -->
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-700/50 flex items-center justify-center text-primary text-xl">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <p class="text-slate-400 text-xs uppercase font-bold tracking-wider">Suất chiếu</p>
                            <p class="text-white text-xl font-bold">{{ $booking->showtime->start_time->format('H:i') }}</p>
                            <p class="text-slate-500 text-sm">{{ $booking->showtime->start_time->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    <!-- Column 2: Location -->
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-700/50 flex items-center justify-center text-primary text-xl">
                            <i class="fas fa-door-open"></i>
                        </div>
                        <div>
                            <p class="text-slate-400 text-xs uppercase font-bold tracking-wider">Phòng chiếu</p>
                            <p class="text-white text-xl font-bold">{{ $booking->showtime->room->name }}</p>
                            <p class="text-slate-500 text-sm">Định dạng: {{ $booking->showtime->room->format }}</p>
                        </div>
                    </div>

                    <!-- Column 3: Status -->
                    <div class="flex items-center gap-4">
                        @php
                            $statusIcons = [
                                'Paid' => 'fa-check-circle text-emerald-500',
                                'Pending' => 'fa-hourglass-half text-amber-500',
                                'Cancelled' => 'fa-times-circle text-rose-500',
                                'Used' => 'fa-user-check text-blue-500',
                            ];
                            $statusLabel = [
                                'Pending' => 'Chờ thanh toán',
                                'Paid' => 'Đã thanh toán',
                                'Cancelled' => 'Đã hủy',
                                'Used' => 'Đã sử dụng',
                            ];
                        @endphp
                        <div class="w-12 h-12 rounded-2xl bg-slate-700/50 flex items-center justify-center text-xl">
                            <i class="fas {{ $statusIcons[$booking->status] ?? 'fa-info-circle text-slate-400' }}"></i>
                        </div>
                        <div>
                            <p class="text-slate-400 text-xs uppercase font-bold tracking-wider">Trạng thái</p>
                            <p class="text-white text-xl font-bold">{{ $statusLabel[$booking->status] ?? $booking->status }}</p>
                            <p class="text-slate-500 text-sm">Cập nhật: {{ $booking->updated_at->format('H:i d/m/Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Seats & QR Section -->
                <div class="flex flex-col md:flex-row gap-10">
                    <div class="flex-grow">
                        <h4 class="text-white font-bold mb-6 flex items-center gap-2">
                            <i class="fas fa-couch text-primary"></i> Danh sách ghế ({{ $booking->bookedSeats->count() }})
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($booking->bookedSeats as $bookedSeat)
                                <div class="bg-slate-900/50 border border-slate-700/50 p-4 rounded-2xl flex justify-between items-center group/seat hover:border-primary/50 transition-colors">
                                    <div>
                                        <p class="text-white font-bold group-hover/seat:text-primary transition-colors">{{ $bookedSeat->seat->row_name }}{{ $bookedSeat->seat->seat_number }}</p>
                                        <p class="text-slate-500 text-xs">{{ $bookedSeat->seat->seat_type }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-white font-bold text-sm">{{ number_format($bookedSeat->price_at_booking) }}đ</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-10 p-6 bg-slate-900/30 rounded-2xl border border-dashed border-slate-700">
                             <div class="flex justify-between items-end">
                                <div>
                                    <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Tổng cộng đã thanh toán</p>
                                    <p class="text-4xl font-black text-primary">{{ number_format($booking->total_price) }}<span class="text-sm font-normal ml-1">VNĐ</span></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-slate-600 text-[10px] italic">Đã bao gồm VAT & phụ phí</p>
                                </div>
                             </div>
                        </div>
                    </div>

                    <!-- QR Section -->
                    <div class="md:w-64 flex flex-col items-center">
                        @php
                            $isExpired = $booking->status === 'Paid' && ($booking->showtime->status === \App\Models\Showtime::STATUS_COMPLETED || ($booking->showtime->end_time && $booking->showtime->end_time->isPast()));
                        @endphp
                        
                        <div class="p-4 bg-white rounded-3xl shadow-2xl mb-4 group/qr">
                            @if($booking->status === 'Used')
                                <div class="w-48 h-48 bg-emerald-50 rounded-2xl flex flex-col items-center justify-center border-4 border-emerald-100 p-4 text-center">
                                     <i class="fas fa-check-double text-5xl text-emerald-500 mb-3"></i>
                                     <p class="text-emerald-800 text-xs font-bold uppercase tracking-tighter">Vé đã được sử dụng</p>
                                </div>
                            @elseif($isExpired)
                                <div class="w-48 h-48 bg-rose-50 rounded-2xl flex flex-col items-center justify-center border-4 border-rose-100 p-4 text-center grayscale">
                                     <i class="fas fa-lock text-5xl text-rose-400 mb-3"></i>
                                     <p class="text-rose-800 text-xs font-bold uppercase tracking-tighter">Vé đã quá hạn</p>
                                </div>
                            @elseif($booking->status === 'Paid')
                                <div class="w-48 h-48 bg-slate-100 rounded-2xl flex items-center justify-center border-4 border-slate-50 overflow-hidden group-hover/qr:scale-105 transition-transform duration-500">
                                     {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(160)->generate(url('/tickets/' . $booking->ticket_token)) !!}
                                </div>
                            @elseif($booking->status === 'Pending')
                                <div class="w-48 h-48 bg-amber-50 rounded-2xl flex flex-col items-center justify-center border-4 border-amber-100 p-4 text-center">
                                     <i class="fas fa-clock text-5xl text-amber-500 mb-3 animate-pulse"></i>
                                     <p class="text-amber-800 text-xs font-bold uppercase tracking-tighter">Đang chờ thanh toán</p>
                                </div>
                            @else
                                <div class="w-48 h-48 bg-slate-200 rounded-2xl flex flex-col items-center justify-center border-4 border-slate-300 p-4 text-center grayscale">
                                     <i class="fas fa-times-circle text-5xl text-slate-400 mb-3"></i>
                                     <p class="text-slate-600 text-xs font-bold uppercase tracking-tighter">Vé đã hủy</p>
                                </div>
                            @endif
                        </div>
                        
                        @if($booking->status === 'Used')
                            <p class="text-emerald-500 text-[10px] text-center uppercase font-bold tracking-widest leading-relaxed">Cảm ơn bạn đã xem phim</p>
                        @elseif($isExpired)
                            <p class="text-rose-500 text-[10px] text-center uppercase font-bold tracking-widest leading-relaxed">Suất chiếu đã kết thúc</p>
                        @elseif($booking->status === 'Paid')
                            <p class="text-slate-400 text-[10px] text-center uppercase font-bold tracking-widest leading-relaxed">Xuất trình mã này tại quầy<br/>để nhận vé vào phòng chiếu</p>
                        @elseif($booking->status === 'Pending')
                             <button class="w-full bg-amber-500 hover:bg-amber-600 text-black font-bold py-3 rounded-2xl transition-all shadow-lg shadow-amber-500/20">THANH TOÁN NGAY</button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer Note -->
            <div class="bg-slate-900/50 p-6 border-t border-slate-700/50 text-center flex flex-col items-center justify-center gap-2">
                @if($booking->status === 'Paid')
                    <p class="text-slate-500 text-xs">
                        <i class="fas fa-info-circle mr-2"></i>Vé đã thanh toán không thể hoàn trả hoặc đổi trả theo quy chuẩn nghiệp vụ của MovieGo.
                    </p>
                @elseif($booking->status === 'Pending')
                    {{-- TODO: Thêm nút và logic hủy vé cho khách hàng chưa thanh toán ở đây (Dành cho thành viên khác phát triển) --}}
                @else
                    <p class="text-slate-500 text-xs">
                        <i class="fas fa-info-circle mr-2"></i>Vé không thể hoàn trả hoặc đổi trả.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
