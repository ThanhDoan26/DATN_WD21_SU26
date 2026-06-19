<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Thanh Toán - movieGo</title>

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
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-200 antialiased pt-10">

    <div class="max-w-6xl mx-auto px-4">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white"><i class="fas fa-credit-card text-primary mr-2"></i> Thanh Toán Vé</h1>
            <p class="text-slate-400">Kiểm tra lại thông tin, áp dụng mã giảm giá và giữ ghế trước khi thanh toán.</p>
        </div>

        @if(!$showtime || empty($seatSummary))
            <div class="rounded-3xl bg-slate-800 border border-slate-700 p-8 text-center">
                <h2 class="text-2xl font-semibold text-white mb-4">Không có dữ liệu thanh toán</h2>
                <p class="text-slate-400 mb-6">Vui lòng chọn ghế từ trang đặt vé trước khi vào trang thanh toán.</p>
                <a href="{{ url()->previous() }}" class="inline-flex items-center justify-center rounded-full bg-primary px-6 py-3 text-white font-semibold hover:bg-red-600 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại chọn ghế
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <div class="rounded-3xl bg-slate-800 border border-slate-700 p-8 shadow-xl">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                            <div>
                                <h2 class="text-2xl font-semibold text-white">Thông tin suất chiếu</h2>
                                <p class="text-slate-400 mt-2">
                                    <strong>{{ $showtime->movie->title }}</strong><br>
                                    {{ $showtime->room->cinema->name }} - {{ $showtime->room->name }}<br>
                                    {{ $showtime->start_time->format('H:i d/m/Y') }}
                                </p>
                            </div>
                            <div class="rounded-3xl bg-slate-900 border border-slate-700 px-5 py-4 text-center">
                                <div class="text-slate-400 text-sm">Phụ thu/suất chiếu</div>
                                <div class="text-xl font-semibold text-white">{{ number_format($surcharge, 0, ',', '.') }} đ</div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            @foreach($seatSummary as $seat)
                                <div class="rounded-3xl bg-slate-900 border border-slate-700 p-5 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                                    <div>
                                        <div class="text-white font-semibold">Ghế {{ $seat['code'] }} ({{ $seat['type'] }})</div>
                                        <div class="text-slate-400 text-sm">Giá cơ bản: {{ number_format($seat['base_price'], 0, ',', '.') }} đ</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-slate-400 text-sm">Phụ thu</div>
                                        <div class="text-white font-semibold">{{ number_format($seat['surcharge'], 0, ',', '.') }} đ</div>
                                        <div class="text-slate-300 text-sm">Tổng: {{ number_format($seat['final_price'], 0, ',', '.') }} đ</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-3xl bg-slate-800 border border-slate-700 p-8 shadow-xl">
                        <h2 class="text-2xl font-semibold text-white mb-4">Áp dụng mã giảm giá</h2>
                        <div class="grid gap-4">
                            <input id="coupon_code" type="text" placeholder="Nhập mã giảm giá" class="w-full rounded-3xl border border-slate-700 bg-slate-900 px-5 py-4 text-white outline-none focus:border-primary" />
                            <button id="apply-coupon" class="inline-flex items-center justify-center rounded-3xl bg-primary px-6 py-4 text-white font-semibold hover:bg-red-600 transition">Áp dụng mã</button>
                            <div id="coupon-result" class="text-sm"></div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl bg-slate-800 border border-slate-700 p-8 shadow-xl sticky top-10">
                    <h2 class="text-2xl font-semibold text-white mb-6">Tóm tắt đơn hàng</h2>
                    <div class="space-y-4 text-sm text-slate-300">
                        <div class="flex justify-between">
                            <span>Ghế đã chọn</span>
                            <span>{{ count($seatSummary) }} ghế</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Tạm tính</span>
                            <span>{{ number_format($subtotal, 0, ',', '.') }} đ</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Phụ thu suất chiếu</span>
                            <span>{{ number_format($surcharge, 0, ',', '.') }} đ / ghế</span>
                        </div>
                        <div class="border-t border-slate-700 pt-4 flex justify-between items-center">
                            <span class="font-semibold text-white">Tổng thanh toán</span>
                            <span id="final-total" class="text-2xl font-bold text-primary">{{ number_format($total, 0, ',', '.') }} đ</span>
                        </div>
                    </div>
                    <button id="confirm-reservation" class="mt-8 w-full rounded-3xl bg-primary px-6 py-4 text-white font-semibold hover:bg-red-600 transition">Giữ ghế & thanh toán</button>
                    <p class="mt-4 text-xs text-slate-500">Ghế sẽ được giữ trong <strong>{{ \App\Services\BookingService::PENDING_PAYMENT_TIMEOUT_MINUTES }} phút</strong>.</p>
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const showtimeId = @json($showtimeId ?? '');
            const seatIds = @json($seatIds ?? '');
            const totalPrice = {{ $total ?? 0 }};
            const apiApplyCoupon = @json(route('api.apply-coupon'));
            const reserveUrl = @json(route('checkout.reserve'));
            const successUrl = @json(route('checkout.success'));
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const applyCouponButton = document.getElementById('apply-coupon');
            const confirmReservationButton = document.getElementById('confirm-reservation');
            const couponCodeInput = document.getElementById('coupon_code');
            const couponResult = document.getElementById('coupon-result');
            const finalTotalElement = document.getElementById('final-total');

            if (applyCouponButton) {
                applyCouponButton.addEventListener('click', function() {
                    const code = couponCodeInput.value.trim();
                    if (!code) {
                        couponResult.innerHTML = '<div class="text-sm text-rose-400">Vui lòng nhập mã giảm giá.</div>';
                        return;
                    }

                    applyCouponButton.disabled = true;
                    applyCouponButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang áp dụng';

                    fetch(apiApplyCoupon, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ code, order_total: totalPrice })
                    })
                        .then(response => response.json())
                        .then(data => {
                            applyCouponButton.disabled = false;
                            applyCouponButton.innerHTML = 'Áp dụng mã';

                            if (data.success) {
                                couponResult.innerHTML = '<div class="text-sm text-emerald-400">' + data.message + '</div>';
                                finalTotalElement.textContent = new Intl.NumberFormat('vi-VN').format(data.data.final_total) + ' đ';
                            } else {
                                couponResult.innerHTML = '<div class="text-sm text-rose-400">' + data.message + '</div>';
                                finalTotalElement.textContent = new Intl.NumberFormat('vi-VN').format(totalPrice) + ' đ';
                            }
                        })
                        .catch(() => {
                            applyCouponButton.disabled = false;
                            applyCouponButton.innerHTML = 'Áp dụng mã';
                            couponResult.innerHTML = '<div class="text-sm text-rose-400">Không thể kết nối tới server.</div>';
                        });
                });
            }

            if (confirmReservationButton) {
                confirmReservationButton.addEventListener('click', function() {
                    if (!showtimeId || !seatIds) {
                        window.location.href = '{{ url('/') }}';
                        return;
                    }

                    confirmReservationButton.disabled = true;
                    confirmReservationButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang giữ ghế';

                    fetch(reserveUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            showtime_id: showtimeId,
                            seat_ids: seatIds,
                            payment_method: 'ONLINE'
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.href = successUrl + '?booking_id=' + data.data.booking_id;
                            } else {
                                confirmReservationButton.disabled = false;
                                confirmReservationButton.innerHTML = 'Giữ ghế & thanh toán';
                                couponResult.innerHTML = '<div class="text-sm text-rose-400">' + data.message + '</div>';
                            }
                        })
                        .catch(() => {
                            confirmReservationButton.disabled = false;
                            confirmReservationButton.innerHTML = 'Giữ ghế & thanh toán';
                            couponResult.innerHTML = '<div class="text-sm text-rose-400">Không thể kết nối tới server.</div>';
                        });
                });
            }
        });
    </script>
</body>
</html>
