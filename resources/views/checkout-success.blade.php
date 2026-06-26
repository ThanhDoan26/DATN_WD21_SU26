<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đặt vé thành công - movieGo</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'sans-serif'] },
                    colors: { primary: '#e50914' }
                }
            }
        }
    </script>
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>
<body class="bg-slate-900 text-slate-200 antialiased">
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <form action="{{ route('checkout.cancel') }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn vé này không? Ghế sẽ được giải phóng cho người khác.');">
                            @csrf
                            <input type="hidden" name="booking_id" value="{{ $booking['booking_id'] }}">
                            <button type="submit" class="w-full inline-flex items-center justify-center rounded-3xl bg-slate-800 border border-slate-600 px-6 py-4 text-center text-rose-400 font-semibold hover:bg-slate-700 transition">
                                <i class="fas fa-times-circle mr-2"></i> Hủy đơn vé
                            </button>
                        </form>

                        <form action="{{ route('checkout.mock-payment') }}" method="POST">
                            @csrf
                            <input type="hidden" name="booking_id" value="{{ $booking['booking_id'] }}">
                            <button type="submit" class="w-full inline-flex items-center justify-center rounded-3xl bg-emerald-600 px-6 py-4 text-center text-white font-semibold hover:bg-emerald-500 transition shadow-lg shadow-emerald-500/30">
                                <i class="fas fa-check-circle mr-2"></i> Xác nhận thanh toán
                            </button>
                        </form>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-3xl bg-slate-900 border border-slate-700 px-6 py-4 text-center text-white font-semibold hover:bg-slate-800 transition">
                            <i class="fas fa-arrow-left mr-2"></i> Quay về trang chính
                        </a>
                        <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-3xl bg-primary px-6 py-4 text-center text-white font-semibold hover:bg-red-600 transition">
                            <i class="fas fa-ticket-alt mr-2"></i> Xem danh sách booking
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
