@extends($layout ?? 'layouts.frontend')

@push('styles')
    <style>
        /* Ticket edge effect */
        .ticket-edge {
            mask-image: radial-gradient(circle at 10px 10px, transparent 10px, black 11px);
            mask-size: 20px 20px;
            mask-position: -10px -10px;
        }
        .payment-radio:checked + div {
            border-color: #e50914;
            background-color: rgba(229, 9, 20, 0.05);
        }
        .payment-radio:checked + div .check-icon {
            opacity: 1;
            transform: scale(1);
        }

        /* ===================== COUNTDOWN TIMER ===================== */
        #booking-timer-bar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 9999;
            display: none;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 4px 24px rgba(0,0,0,0.5);
            padding: 0 24px;
            height: 60px;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            animation: timerSlideDown 0.4s cubic-bezier(0.4,0,0.2,1) forwards;
        }
        @keyframes timerSlideDown {
            from { transform: translateY(-100%); opacity: 0; }
            to   { transform: translateY(0);     opacity: 1; }
        }
        #timer-progress-track {
            flex: 1; max-width: 300px;
            height: 6px; background: rgba(255,255,255,0.1);
            border-radius: 99px; overflow: hidden;
        }
        #timer-progress-fill {
            height: 100%; border-radius: 99px;
            background: linear-gradient(90deg, #22c55e, #facc15, #e50914);
            background-size: 300% 100%;
            background-position: 0% 50%;
            transition: width 1s linear, background-position 1s linear;
            width: 100%;
        }
        #timer-digits {
            font-variant-numeric: tabular-nums;
            font-size: 1.25rem; font-weight: 800;
            letter-spacing: 1px; min-width: 56px;
            text-align: center; transition: color 0.5s;
            color: #ffffff;
        }
        #timer-digits.urgent { color: #ef4444 !important; animation: timerPulse 0.8s infinite; }
        @keyframes timerPulse {
            0%,100% { opacity: 1; }
            50%      { opacity: 0.55; }
        }
        /* Expired overlay */
        #booking-expired-overlay {
            display: none; position: fixed; inset: 0; z-index: 99999;
            background: rgba(0,0,0,0.85); backdrop-filter: blur(6px);
            align-items: center; justify-content: center;
        }
        #booking-expired-overlay.active { display: flex; }
        .expired-card {
            background: #0f172a; border: 1px solid rgba(239,68,68,0.2);
            border-radius: 24px; padding: 48px 40px;
            max-width: 440px; width: 90%; text-align: center;
            box-shadow: 0 32px 64px rgba(0,0,0,0.6);
            animation: expiredZoomIn 0.35s cubic-bezier(0.34,1.56,0.64,1) forwards;
        }
        @keyframes expiredZoomIn {
            from { transform: scale(0.85); opacity: 0; }
            to   { transform: scale(1);    opacity: 1; }
        }
        .expired-icon {
            width: 72px; height: 72px; border-radius: 50%;
            background: rgba(239,68,68,0.15); border: 2px solid rgba(239,68,68,0.3);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px; font-size: 1.75rem; color: #ef4444;
        }
        /* ============================================================ */
    </style>
@endpush

@section('content')

    <div class="max-w-6xl mx-auto px-4 pt-32 pb-20">
        <div class="mb-10 text-center">
            <h1 class="text-4xl font-bold text-white mb-2"><i class="fas fa-ticket-alt text-primary mr-3"></i>Thanh Toán Vé</h1>
            <p class="text-slate-400">Hoàn tất các bước cuối cùng để thưởng thức bộ phim của bạn.</p>
        </div>

        @if(!$showtime || empty($seatSummary))
            <div class="rounded-3xl bg-slate-900 border border-slate-800 p-12 text-center max-w-2xl mx-auto shadow-2xl">
                <div class="text-6xl text-slate-700 mb-6"><i class="fas fa-ticket-alt"></i></div>
                <h2 class="text-2xl font-semibold text-white mb-4">Không có dữ liệu thanh toán</h2>
                <p class="text-slate-400 mb-8">Vui lòng quay lại trang chọn ghế để tiếp tục quá trình đặt vé.</p>
                <a href="{{ url()->previous() }}" class="inline-flex items-center justify-center rounded-full bg-primary px-8 py-4 text-white font-semibold hover:bg-red-600 transition shadow-lg shadow-primary/30">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại chọn ghế
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left Column (Main Info) -->
                <div class="lg:col-span-8 space-y-8">

                    @if(isset($isWalkIn) && $isWalkIn)
                    <!-- Customer Info (Walk-in) -->
                    <div class="rounded-3xl bg-slate-900 border border-slate-800 shadow-xl p-8 mb-8">
                        <h2 class="text-xl font-bold text-white mb-4"><i class="fas fa-user mr-2 text-primary"></i> Thông Tin Khách Hàng (Tùy chọn)</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Họ Tên</label>
                                <input type="text" id="customer_name" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-primary" placeholder="Nhập họ tên">
                            </div>
                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Số Điện Thoại</label>
                                <input type="text" id="customer_phone" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-primary" placeholder="Nhập SĐT">
                            </div>
                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Email</label>
                                <input type="email" id="customer_email" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-primary" placeholder="Nhập Email">
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Ticket Info -->
                    <div class="rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl overflow-hidden relative">
                        <!-- Decorative top edge -->
                        <div class="h-3 w-full bg-gradient-to-r from-primary to-red-800"></div>
                        <div class="p-8">
                            <h2 class="text-xl font-bold text-white mb-6 uppercase tracking-wider text-slate-400"><i class="fas fa-film mr-2 text-primary"></i> Thông Tin Phim</h2>
                            <div class="flex flex-col md:flex-row gap-6 items-start md:items-center">
                                @if($showtime->movie->poster_url)
                                    <div class="w-24 h-36 rounded-xl overflow-hidden shadow-lg flex-shrink-0 border border-slate-700">
                                        <img src="{{ str_starts_with($showtime->movie->poster_url, 'http') ? $showtime->movie->poster_url : asset('storage/' . $showtime->movie->poster_url) }}" alt="Poster" class="w-full h-full object-cover">
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <h3 class="text-3xl font-bold text-white mb-2">{{ $showtime->movie->title }}</h3>
                                    <div class="flex flex-wrap gap-4 text-sm text-slate-300 mb-4">
                                        <span class="bg-slate-800 px-3 py-1 rounded-full"><i class="fas fa-calendar-alt mr-1 text-slate-400"></i> {{ $showtime->start_time->format('d/m/Y') }}</span>
                                        <span class="bg-slate-800 px-3 py-1 rounded-full"><i class="fas fa-clock mr-1 text-slate-400"></i> {{ $showtime->start_time->format('H:i') }}</span>
                                        <span class="bg-slate-800 px-3 py-1 rounded-full"><i class="fas fa-map-marker-alt mr-1 text-slate-400"></i> {{ $showtime->room->cinema->name }}</span>
                                    </div>
                                    <p class="text-slate-400 font-medium">Phòng chiếu: <span class="text-white">{{ $showtime->room->name }}</span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Dotted Divider -->
                        <div class="relative flex items-center px-4">
                            <div class="w-6 h-6 rounded-full bg-slate-950 absolute left-0 -ml-3"></div>
                            <div class="w-full border-t-2 border-dashed border-slate-700"></div>
                            <div class="w-6 h-6 rounded-full bg-slate-950 absolute right-0 -mr-3"></div>
                        </div>

                        <div class="p-8 bg-slate-900/50">
                            <h2 class="text-xl font-bold text-white mb-4 uppercase tracking-wider text-slate-400"><i class="fas fa-chair mr-2 text-primary"></i> Ghế Đã Chọn</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($seatSummary as $seat)
                                    <div class="rounded-2xl bg-slate-800/50 border border-slate-700/50 p-4 flex justify-between items-center group hover:bg-slate-800 transition">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center text-primary font-bold shadow-inner">
                                                {{ $seat['code'] }}
                                            </div>
                                            <div>
                                                <div class="text-white font-medium capitalize">{{ strtolower($seat['type']) }}</div>
                                                <div class="text-slate-400 text-xs mt-0.5">Giá: {{ number_format($seat['base_price'], 0, ',', '.') }} đ</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-white font-bold">{{ number_format($seat['final_price'], 0, ',', '.') }} đ</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Combos -->
                    <div class="rounded-3xl bg-slate-900 border border-slate-800 shadow-xl p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-white"><i class="fas fa-popcorn mr-2 text-amber-500"></i> Combo Bắp Nước</h2>
                            <span class="text-sm bg-slate-800 text-slate-300 px-3 py-1 rounded-full border border-slate-700">Tùy chọn</span>
                        </div>

                        <div class="space-y-4">
                            @forelse($combos as $combo)
                                <div class="flex flex-col sm:flex-row items-center gap-6 bg-slate-950/50 hover:bg-slate-800/80 p-5 rounded-2xl border border-slate-800/80 hover:border-slate-600 transition-all duration-300 group">
                                    <div class="w-20 h-20 bg-slate-800 rounded-xl overflow-hidden flex-shrink-0 border border-slate-700 group-hover:border-slate-500 transition shadow-lg">
                                        @if($combo->image)
                                            <img src="{{ asset('storage/' . $combo->image) }}" alt="{{ $combo->name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-slate-500 text-2xl"><i class="fas fa-hamburger"></i></div>
                                        @endif
                                    </div>
                                    <div class="flex-1 text-center sm:text-left">
                                        <h3 class="text-lg text-white font-bold mb-1">{{ $combo->name }}</h3>
                                        <p class="text-slate-400 text-sm mb-2 line-clamp-2">{{ $combo->description }}</p>
                                        <p class="text-primary font-bold text-lg" data-price="{{ $combo->price }}">{{ number_format($combo->price, 0, ',', '.') }} đ</p>
                                    </div>
                                    <div class="flex items-center gap-4 bg-slate-900 rounded-full px-2 py-1 border border-slate-700">
                                        <button type="button" class="btn-decrease-combo w-10 h-10 rounded-full text-slate-400 hover:text-white hover:bg-slate-700 active:bg-slate-600 flex items-center justify-center transition" data-id="{{ $combo->id }}">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <span class="combo-quantity font-bold text-white w-6 text-center text-lg" data-id="{{ $combo->id }}" data-name="{{ $combo->name }}" data-price="{{ $combo->price }}">0</span>
                                        <button type="button" class="btn-increase-combo w-10 h-10 rounded-full text-primary hover:text-white hover:bg-primary active:bg-red-700 flex items-center justify-center transition" data-id="{{ $combo->id }}">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 border-2 border-dashed border-slate-800 rounded-2xl">
                                    <div class="text-4xl text-slate-600 mb-3"><i class="fas fa-box-open"></i></div>
                                    <p class="text-slate-400">Hiện không có combo nào.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="rounded-3xl bg-slate-900 border border-slate-800 shadow-xl p-8">
                        <h2 class="text-2xl font-bold text-white mb-6"><i class="fas fa-wallet mr-2 text-emerald-500"></i> Phương Thức Thanh Toán</h2>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="payment" value="MOMO" class="peer payment-radio hidden" checked>
                                <div class="border-2 border-slate-700 rounded-2xl p-6 transition-all duration-300 hover:border-slate-500 flex flex-col items-center gap-3 bg-slate-950/30">
                                    <div class="absolute top-4 right-4 text-primary opacity-0 scale-50 transition-all duration-300 check-icon">
                                        <i class="fas fa-check-circle text-xl"></i>
                                    </div>
                                    <div class="w-16 h-16 rounded-2xl bg-pink-500/10 flex items-center justify-center text-pink-500 text-3xl mb-2">
                                        <i class="fas fa-qrcode"></i>
                                    </div>
                                    <span class="text-white font-semibold text-lg">Ví điện tử MoMo</span>
                                </div>
                            </label>

                            <label class="relative cursor-pointer group">
                                <input type="radio" name="payment" value="ATM" class="peer payment-radio hidden">
                                <div class="border-2 border-slate-700 rounded-2xl p-6 transition-all duration-300 hover:border-slate-500 flex flex-col items-center gap-3 bg-slate-950/30">
                                    <div class="absolute top-4 right-4 text-primary opacity-0 scale-50 transition-all duration-300 check-icon">
                                        <i class="fas fa-check-circle text-xl"></i>
                                    </div>
                                    <div class="w-16 h-16 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-500 text-3xl mb-2">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    <span class="text-white font-semibold text-lg">Thẻ ATM / Visa</span>
                                </div>
                            </label>

                            <label class="relative cursor-pointer group">
                                <input type="radio" name="payment" value="Stripe" class="peer payment-radio hidden">
                                <div class="border-2 border-slate-700 rounded-2xl p-6 transition-all duration-300 hover:border-slate-500 flex flex-col items-center gap-3 bg-slate-950/30">
                                    <div class="absolute top-4 right-4 text-primary opacity-0 scale-50 transition-all duration-300 check-icon">
                                        <i class="fas fa-check-circle text-xl"></i>
                                    </div>
                                    <div class="w-16 h-16 rounded-2xl bg-purple-500/10 flex items-center justify-center text-purple-500 text-3xl mb-2">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                    <span class="text-white font-semibold text-lg">Stripe</span>
                                </div>
                            </label>

                            @if(isset($isWalkIn) && $isWalkIn)
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="payment" value="CASH" class="peer payment-radio hidden">
                                <div class="border-2 border-slate-700 rounded-2xl p-6 transition-all duration-300 hover:border-slate-500 flex flex-col items-center gap-3 bg-slate-950/30">
                                    <div class="absolute top-4 right-4 text-primary opacity-0 scale-50 transition-all duration-300 check-icon">
                                        <i class="fas fa-check-circle text-xl"></i>
                                    </div>
                                    <div class="w-16 h-16 rounded-2xl bg-green-500/10 flex items-center justify-center text-green-500 text-3xl mb-2">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <span class="text-white font-semibold text-lg">Tiền mặt</span>
                                </div>
                            </label>
                            @endif
                        </div>
                    </div>

                    <!-- Coupons Section -->
                    <div class="rounded-3xl bg-slate-900 border border-slate-800 shadow-xl p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-white"><i class="fas fa-ticket-alt mr-2 text-primary"></i> Mã Giảm Giá</h2>
                            <span class="text-sm bg-slate-800 text-slate-300 px-3 py-1 rounded-full border border-slate-700">Tùy chọn</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5" id="coupons-list">
                            <label class="relative cursor-pointer group coupon-label" data-code="" data-min="0" data-value="0" data-type="fixed" data-max="0">
                                <input type="radio" name="coupon" value="" class="peer coupon-radio hidden" checked>
                                <div class="coupon-card border-2 border-slate-700 rounded-2xl p-4 transition-all duration-300 hover:border-slate-500 bg-slate-950/30">
                                    <div class="absolute top-4 right-4 text-primary opacity-0 scale-50 transition-all duration-300 check-icon">
                                        <i class="fas fa-check-circle text-xl"></i>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 text-xl flex-shrink-0">
                                            <i class="fas fa-times"></i>
                                        </div>
                                        <h3 class="text-white font-bold text-lg">Không dùng mã</h3>
                                    </div>
                                </div>
                            </label>

                            @forelse($coupons as $coupon)
                                <label class="relative cursor-pointer group coupon-label" data-code="{{ $coupon->code }}" data-min="{{ $coupon->min_order_value }}" data-value="{{ $coupon->value }}" data-type="{{ $coupon->type }}" data-max="{{ $coupon->max_discount_amount }}">
                                    <input type="radio" name="coupon" value="{{ $coupon->code }}" class="peer coupon-radio hidden">
                                    <div class="coupon-card border-2 border-slate-700 rounded-2xl p-4 transition-all duration-300 hover:border-primary bg-slate-950/30 relative overflow-hidden">
                                        <div class="absolute top-4 right-4 text-primary opacity-0 scale-50 transition-all duration-300 check-icon">
                                            <i class="fas fa-check-circle text-xl"></i>
                                        </div>
                                        <div class="flex items-start gap-4">
                                            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary text-xl flex-shrink-0">
                                                <i class="fas fa-percentage"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-white font-bold text-lg mb-1">{{ $coupon->code }}</h3>
                                                <p class="text-primary text-sm mb-1 font-semibold">Giảm {{ $coupon->type == 'percent' ? number_format($coupon->value, 0) . '%' : number_format($coupon->value, 0, ',', '.') . 'đ' }}</p>
                                                <p class="text-xs text-slate-400">Đơn tối thiểu: {{ number_format($coupon->min_order_value, 0, ',', '.') }}đ</p>
                                            </div>
                                        </div>
                                        <div class="mt-3 text-xs text-rose-500 font-medium hidden error-message bg-rose-500/10 p-2 rounded-lg text-center">Chưa đủ điều kiện</div>
                                    </div>
                                </label>
                            @empty
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Right Column (Summary) -->
                <div class="lg:col-span-4">
                    <div class="rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl p-6 sticky top-10">
                        <h2 class="text-xl font-bold text-white mb-6 border-b border-slate-800 pb-4">Tóm tắt đơn hàng</h2>

                        <!-- Details -->
                        <div class="space-y-4 mb-6 text-sm">
                            <div class="flex justify-between items-center text-slate-300">
                                <span class="flex items-center"><i class="fas fa-chair text-slate-500 w-5"></i> Ghế đã chọn ({{ count($seatSummary) }})</span>
                                <span class="font-medium text-white">{{ number_format($subtotal + $surcharge * count($seatSummary), 0, ',', '.') }} đ</span>
                            </div>

                            <div id="selected_combos_container" class="space-y-3 pl-5 border-l-2 border-slate-800 ml-2 mt-2 hidden">
                                <!-- JS Populated -->
                            </div>
                        </div>

                        <!-- Selected Coupon Display -->
                        <div id="selected_coupon_display" class="mb-6 bg-primary/10 p-4 rounded-2xl border border-primary/30 hidden items-center gap-3">
                            <i class="fas fa-ticket-alt text-primary text-xl"></i>
                            <div class="flex-1">
                                <p class="text-xs text-primary uppercase font-bold tracking-wider mb-1">Mã đã áp dụng</p>
                                <p id="applied_coupon_code" class="text-white font-bold text-lg">CODE</p>
                            </div>
                        </div>

                        <div id="discount_row" class="flex justify-between items-center text-sm text-emerald-400 mb-6 hidden border-t border-slate-800 pt-4">
                            <span class="flex items-center font-medium"><i class="fas fa-tag mr-2"></i> Giảm giá</span>
                            <span id="discount_display" class="font-bold text-lg">-0 đ</span>
                        </div>

                        <!-- Total -->
                        <div class="border-t border-slate-800 pt-6 mb-6">
                            <div class="flex justify-between items-end">
                                <span class="text-slate-400 font-medium mb-1">Tổng thanh toán: </span>
                                <span id="final-total" class="text-4xl font-black text-white">{{ number_format($total, 0, ',', '.') }} đ</span>
                            </div>
                        </div>

                        <!-- ===== COUNTDOWN CLOCK WIDGET ===== -->
                        <div id="sidebar-timer-widget"
                             style="margin-bottom:20px;background:linear-gradient(135deg,rgba(15,23,42,0.9),rgba(30,41,59,0.9));border:1px solid rgba(255,255,255,0.08);border-radius:16px;padding:16px 20px;display:flex;align-items:center;gap:14px">
                            <!-- Ring icon -->
                            <div id="sidebar-timer-ring" style="position:relative;width:64px;height:64px;flex-shrink:0">
                                <svg viewBox="0 0 64 64" style="width:64px;height:64px;transform:rotate(-90deg)">
                                    <circle cx="32" cy="32" r="27" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="5"/>
                                    <circle id="sidebar-ring-arc" cx="32" cy="32" r="27" fill="none"
                                            stroke="#22c55e" stroke-width="5" stroke-linecap="round"
                                            stroke-dasharray="169.6" stroke-dashoffset="0"
                                            style="transition:stroke-dashoffset 1s linear,stroke 0.5s"/>
                                </svg>
                                <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center">
                                    <i class="far fa-clock" id="sidebar-clock-icon" style="font-size:1.1rem;color:#94a3b8;transition:color 0.5s"></i>
                                </div>
                            </div>
                            <!-- Time text -->
                            <div style="flex:1;min-width:0">
                                <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:1px;color:#64748b;font-weight:600;margin-bottom:4px">
                                    Thời gian giữ ghế
                                </div>
                                <div id="sidebar-timer-digits"
                                     style="font-size:2rem;font-weight:900;font-variant-numeric:tabular-nums;letter-spacing:2px;color:#ffffff;line-height:1;transition:color 0.4s">
                                    10:00
                                </div>
                                <div id="sidebar-timer-label" style="font-size:0.72rem;color:#64748b;margin-top:4px">
                                    Chờ xác nhận thanh toán...
                                </div>
                            </div>
                        </div>
                        <!-- ==================================== -->

                        <div class="space-y-3">
                            <button id="confirm-reservation" class="w-full rounded-2xl bg-[#e50914] px-6 py-4 text-white text-lg font-bold hover:bg-[#b80710] hover:shadow-lg hover:shadow-red-500/40 transition-all duration-300 hover:-translate-y-1 flex items-center justify-center gap-3">
                                <span>Thanh toán ngay</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                            <a href="{{ url()->previous() }}" class="w-full rounded-2xl bg-slate-800/50 border border-slate-700 px-6 py-3 text-slate-300 text-base font-bold hover:bg-slate-800 hover:text-white transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-arrow-left"></i>
                                <span>Quay lại</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- ======== COUNTDOWN TIMER BAR (sticky top) ======== --}}
    <div id="booking-timer-bar">
        <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
            <i class="far fa-clock" style="color:#facc15;font-size:1.1rem"></i>
            <span style="color:#94a3b8;font-size:0.85rem;font-weight:500" class="hidden-xs">Thời gian giữ ghế</span>
            <span id="timer-digits">10:00</span>
        </div>
        <div id="timer-progress-track">
            <div id="timer-progress-fill"></div>
        </div>
        <span style="font-size:0.75rem;color:#64748b;flex-shrink:0" class="hidden-xs">Thanh toán trước khi hết giờ</span>
    </div>

    {{-- ======== SESSION EXPIRED OVERLAY ======== --}}
    <div id="booking-expired-overlay">
        <div class="expired-card">
            <div class="expired-icon"><i class="fas fa-clock"></i></div>
            <h2 style="font-size:1.5rem;font-weight:800;color:#fff;margin-bottom:12px">Phiên đặt vé đã hết hạn</h2>
            <p style="color:#94a3b8;font-size:0.9rem;line-height:1.6;margin-bottom:28px">
                Quá <strong style="color:#fff">10 phút</strong> mà chưa hoàn tất thanh toán,<br>
                ghế của bạn đã được giải phóng.<br>
                Vui lòng chọn lại ghế để tiếp tục.
            </p>
            <a id="expired-back-btn" href="/"
               style="display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#e50914;color:#fff;font-weight:700;padding:14px 32px;border-radius:16px;text-decoration:none;width:100%;transition:background 0.2s"
               onmouseover="this.style.background='#b80710'" onmouseout="this.style.background='#e50914'">
                <i class="fas fa-arrow-left"></i> Chọn lại ghế
            </a>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(!$showtime || empty($seatSummary))
                return;
            @endif

            console.log("Checkout timer script: DOMContentLoaded loaded.");

            const showtimeId = @json($showtimeId ?? '');
            const seatIds = @json($seatIds ?? '');
            const ticketTotal = {{ $total ?? 0 }}; // Tiền vé đã bao gồm phụ thu
            const apiApplyCoupon = @json(route('api.apply-coupon', [], false));
            const reserveUrl = @json(route('checkout.reserve', [], false));
            const stripeSessionUrl = @json(route('stripe.session', [], false));
            const successUrl = @json(route('checkout.success', [], false));
            
            const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
            const TIMEOUT_SECONDS = {{ \App\Services\BookingService::PENDING_PAYMENT_TIMEOUT_MINUTES * 60 }};
            const seatSelectionUrl = '{{ url()->previous() }}';

            // ===================== COUNTDOWN TIMER =====================
            let countdownInterval = null;
            const timerBar       = document.getElementById('booking-timer-bar');
            const timerDigits    = document.getElementById('timer-digits');
            const timerFill      = document.getElementById('timer-progress-fill');
            const expiredOverlay = document.getElementById('booking-expired-overlay');
            const expiredBackBtn = document.getElementById('expired-back-btn');

            function startCountdown(expiresAtMs) {
                console.log("startCountdown starting for timestamp:", expiresAtMs, "Current time:", Date.now());
                if (expiredBackBtn) expiredBackBtn.href = seatSelectionUrl;
                if (timerBar) timerBar.style.display = 'flex';

                // Sidebar widget elements
                const sidebarDigits  = document.getElementById('sidebar-timer-digits');
                const sidebarLabel   = document.getElementById('sidebar-timer-label');
                const sidebarArc     = document.getElementById('sidebar-ring-arc');
                const sidebarIcon    = document.getElementById('sidebar-clock-icon');
                const ARC_LEN = 169.6; // 2*π*27

                function tick() {
                    const now = Date.now();
                    const remaining = Math.max(0, Math.floor((expiresAtMs - now) / 1000));
                    const mins = String(Math.floor(remaining / 60)).padStart(2, '0');
                    const secs = String(remaining % 60).padStart(2, '0');
                    const display = `${mins}:${secs}`;

                    // --- Top bar ---
                    if (timerDigits) timerDigits.textContent = display;
                    const pct = (remaining / TIMEOUT_SECONDS) * 100;
                    if (timerFill) {
                        timerFill.style.width = pct + '%';
                        timerFill.style.backgroundPosition = `${100 - pct}% 50%`;
                    }

                    // --- Sidebar widget ---
                    if (sidebarDigits) sidebarDigits.textContent = display;

                    // SVG arc: dashoffset goes from 0 (full) → ARC_LEN (empty)
                    const offset = ARC_LEN * (1 - remaining / TIMEOUT_SECONDS);
                    if (sidebarArc) {
                        sidebarArc.setAttribute('stroke-dashoffset', offset);
                    }

                    // Color transitions: green → yellow → red
                    let color;
                    if (remaining > 300)      color = '#22c55e'; // green  > 5 min
                    else if (remaining > 90)  color = '#facc15'; // yellow 1.5-5 min
                    else                      color = '#ef4444'; // red    < 1.5 min

                    if (sidebarArc)  sidebarArc.style.stroke = color;
                    if (sidebarIcon) sidebarIcon.style.color  = color;
                    if (sidebarDigits) sidebarDigits.style.color = color;

                    if (remaining <= 90) {
                        if (timerDigits) timerDigits.classList.add('urgent');
                        if (sidebarLabel) {
                            sidebarLabel.textContent = '⚠ Sắp hết thời gian!';
                            sidebarLabel.style.color = '#ef4444';
                        }
                    } else {
                        if (timerDigits) timerDigits.classList.remove('urgent');
                        if (sidebarLabel) {
                            sidebarLabel.textContent = 'Thanh toán trước khi hết giờ';
                            sidebarLabel.style.color = '#64748b';
                        }
                    }

                    if (remaining <= 0) {
                        console.log("Timer expired!");
                        clearInterval(countdownInterval);
                        sessionStorage.removeItem('booking_expires_at');
                        if (timerBar) timerBar.style.display = 'none';
                        if (expiredOverlay) expiredOverlay.classList.add('active');
                    }
                }

                tick();
                clearInterval(countdownInterval);
                countdownInterval = setInterval(tick, 1000);
            }

            // ---- Khởi động timer ngay khi vào trang ----
            // 1. Nếu server trả về thời gian kết thúc của Booking có sẵn trong DB → Ưu tiên dùng
            // 2. Nếu có timer lưu trong sessionStorage (resume từ Stripe) → dùng tiếp
            // 3. Nếu chưa có → đếm 10 phút từ hiện tại
            (function initTimer() {
                console.log("initTimer called.");
                if (expiredBackBtn) expiredBackBtn.href = seatSelectionUrl;

                const serverExpiresAt = @json($expiresAtMs ?? null);
                if (serverExpiresAt) {
                    const expiresAtMs = parseInt(serverExpiresAt, 10);
                    console.log("initTimer: found server-side expiry:", expiresAtMs);
                    sessionStorage.setItem('booking_expires_at', expiresAtMs.toString());
                    if (expiresAtMs > Date.now()) {
                        startCountdown(expiresAtMs);
                    } else {
                        sessionStorage.removeItem('booking_expires_at');
                        if (expiredOverlay) expiredOverlay.classList.add('active');
                    }
                    return;
                }

                const stored = sessionStorage.getItem('booking_expires_at');
                if (stored) {
                    const expiresAtMs = parseInt(stored, 10);
                    console.log("initTimer: found stored expiry:", expiresAtMs);
                    if (expiresAtMs > Date.now()) {
                        startCountdown(expiresAtMs);
                    } else {
                        console.log("initTimer: stored expiry is in the past, showing overlay.");
                        sessionStorage.removeItem('booking_expires_at');
                        if (expiredOverlay) expiredOverlay.classList.add('active');
                    }
                } else {
                    // Mới vào trang → bắt đầu đếm 10 phút ngay
                    const freshExpiry = Date.now() + TIMEOUT_SECONDS * 1000;
                    console.log("initTimer: starting fresh expiry:", freshExpiry);
                    sessionStorage.setItem('booking_expires_at', freshExpiry.toString());
                    startCountdown(freshExpiry);
                }
            })();
            // ---------------------------------------------

            let combosTotal = 0;
            let currentDiscount = 0;
            let finalTotal = ticketTotal;

            // Format money function
            const formatMoney = (amount) => {
                return new Intl.NumberFormat('vi-VN').format(amount) + ' đ';
            };

            const selectedCombos = {};

            const couponLabels = document.querySelectorAll('.coupon-label');
            const selectedCouponDisplay = document.getElementById('selected_coupon_display');
            const appliedCouponCode = document.getElementById('applied_coupon_code');
            const discountRow = document.getElementById('discount_row');
            const discountDisplay = document.getElementById('discount_display');
            const combosContainer = document.getElementById('selected_combos_container');
            const finalTotalDisplay = document.getElementById('final-total');
            const confirmReservationButton = document.getElementById('confirm-reservation');

            const updateOrderSummary = () => {
                combosTotal = 0;
                combosContainer.innerHTML = '';

                let hasCombos = false;
                Object.values(selectedCombos).forEach(combo => {
                    if (combo.qty > 0) {
                        hasCombos = true;
                        combosTotal += combo.price * combo.qty;
                        const div = document.createElement('div');
                        div.className = 'flex justify-between items-center text-slate-400';
                        div.innerHTML = `
                            <span><span class="text-amber-500 font-medium">${combo.qty}x</span> ${combo.name}</span>
                            <span>${formatMoney(combo.price * combo.qty)}</span>
                        `;
                        combosContainer.appendChild(div);
                    }
                });

                if (hasCombos) {
                    combosContainer.classList.remove('hidden');
                } else {
                    combosContainer.classList.add('hidden');
                }

                let subtotal = ticketTotal + combosTotal;

                // Re-evaluate coupons eligibility
                let activeRadio = document.querySelector('input[name="coupon"]:checked');

                couponLabels.forEach(label => {
                    const minOrder = parseFloat(label.getAttribute('data-min'));
                    const code = label.getAttribute('data-code');
                    const radio = label.querySelector('.coupon-radio');
                    const card = label.querySelector('.coupon-card');
                    const errorMsg = label.querySelector('.error-message');

                    if (code && subtotal < minOrder) {
                        // Invalid
                        radio.disabled = true;
                        card.classList.add('opacity-40', 'grayscale');
                        card.classList.remove('hover:border-primary', 'cursor-pointer');
                        errorMsg.classList.remove('hidden');

                        // If it was selected, unselect it and select "Không dùng mã"
                        if (radio.checked) {
                            radio.checked = false;
                            document.querySelector('input[name="coupon"][value=""]').checked = true;
                        }
                    } else {
                        // Valid
                        radio.disabled = false;
                        card.classList.remove('opacity-40', 'grayscale');
                        card.classList.add('hover:border-primary', 'cursor-pointer');
                        if(errorMsg) errorMsg.classList.add('hidden');
                    }
                });

                // Calculate current discount based on selected
                activeRadio = document.querySelector('input[name="coupon"]:checked');
                const activeCode = activeRadio ? activeRadio.value : "";

                if (activeCode) {
                    const activeLabel = document.querySelector(`.coupon-label[data-code="${activeCode}"]`);
                    const type = activeLabel.getAttribute('data-type');
                    const value = parseFloat(activeLabel.getAttribute('data-value'));
                    const max = parseFloat(activeLabel.getAttribute('data-max'));

                    if (type === 'percent') {
                        currentDiscount = (subtotal * value) / 100;
                        if (max > 0 && currentDiscount > max) currentDiscount = max;
                    } else {
                        currentDiscount = value;
                    }
                    if (currentDiscount > subtotal) currentDiscount = subtotal;

                    selectedCouponDisplay.classList.remove('hidden');
                    selectedCouponDisplay.classList.add('flex');
                    appliedCouponCode.textContent = activeCode;

                    discountRow.classList.remove('hidden');
                    discountDisplay.textContent = '-' + formatMoney(currentDiscount);
                } else {
                    currentDiscount = 0;
                    selectedCouponDisplay.classList.add('hidden');
                    selectedCouponDisplay.classList.remove('flex');
                    discountRow.classList.add('hidden');
                }

                finalTotal = subtotal - currentDiscount;
                if(finalTotal < 0) finalTotal = 0;

                finalTotalDisplay.textContent = formatMoney(finalTotal);
            };

            // Combo buttons
            document.querySelectorAll('.btn-decrease-combo').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const span = document.querySelector(`.combo-quantity[data-id="${id}"]`);
                    let qty = parseInt(span.textContent);
                    if (qty > 0) {
                        qty--;
                        span.textContent = qty;
                        selectedCombos[id].qty = qty;
                        updateOrderSummary();
                    }
                });
            });

            document.querySelectorAll('.btn-increase-combo').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const span = document.querySelector(`.combo-quantity[data-id="${id}"]`);
                    let qty = parseInt(span.textContent);
                    qty++;
                    span.textContent = qty;

                    if (!selectedCombos[id]) {
                        selectedCombos[id] = {
                            name: span.getAttribute('data-name'),
                            price: parseFloat(span.getAttribute('data-price')),
                            qty: 0
                        };
                    }
                    selectedCombos[id].qty = qty;
                    updateOrderSummary();
                });
            });

            // Coupon Selection
            document.querySelectorAll('input[name="coupon"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    updateOrderSummary();
                });
            });

            // Confirm Reservation
            if (confirmReservationButton) {
                confirmReservationButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    if (!showtimeId || !seatIds) {
                        alert('Vui lòng chọn ghế trước khi thanh toán');
                        window.location.href = '/';
                        return;
                    }

                    const selectedPayment = document.querySelector('input[name="payment"]:checked').value;
                    const couponCode = document.querySelector('input[name="coupon"]:checked').value || '';

                    confirmReservationButton.disabled = true;
                    confirmReservationButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Đang xử lý...';

                    // ========== BƯỚC 1: TẠO BOOKING (STATUS = PENDING) ==========
                    fetch(reserveUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            showtime_id: showtimeId,
                            seat_ids: Array.isArray(seatIds) ? seatIds.join(',') : seatIds,
                            combos: selectedCombos,
                            payment_method: selectedPayment,
                            coupon_code: couponCode,
                            customer_name: document.getElementById('customer_name') ? document.getElementById('customer_name').value : null,
                            customer_phone: document.getElementById('customer_phone') ? document.getElementById('customer_phone').value : null,
                            customer_email: document.getElementById('customer_email') ? document.getElementById('customer_email').value : null
                        })
                    })
                    .then(async response => {
                        const text = await response.text();
                        let data;
                        try {
                            data = text ? JSON.parse(text) : {};
                        } catch (err) {
                            throw new Error(`Invalid JSON response from reserve: ${text}`);
                        }
                        if (!response.ok) {
                            const message = data?.message || data?.error || response.statusText;
                            throw new Error(message || 'Lỗi tạo booking');
                        }
                        return data;
                    })
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'Lỗi tạo booking');
                        }

                        if (data.isWalkIn) {
                            window.location.href = data.redirect_url;
                            return;
                        }

                        const bookingId = data.data.booking_id;

                        // ===== KHỞI ĐỘNG ĐỒNG HỒ ĐẾM NGƯỢC =====
                        const timeoutMs = (data.data?.timeout_minutes ?? {{ \App\Services\BookingService::PENDING_PAYMENT_TIMEOUT_MINUTES }}) * 60 * 1000;
                        const bookingTime = data.data?.booking_time
                            ? new Date(data.data.booking_time).getTime()
                            : Date.now();
                        const expiresAtMs = bookingTime + timeoutMs;
                        // Lưu vào sessionStorage để timer vẫn chạy nếu Stripe redirect về
                        sessionStorage.setItem('booking_expires_at', expiresAtMs.toString());
                        startCountdown(expiresAtMs);
                        // ==========================================

                        // ========== BƯỚC 2: TẠO STRIPE SESSION ==========
                        return fetch(stripeSessionUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                booking_id: bookingId
                            })
                        })
                        .then(async response => {
                            const text = await response.text();
                            let data;
                            try {
                                data = text ? JSON.parse(text) : {};
                            } catch (err) {
                                throw new Error(`Invalid JSON response from Stripe session: ${text}`);
                            }
                            if (!response.ok) {
                                const message = data?.message || data?.error || response.statusText;
                                throw new Error(message || 'Không tạo được phiên thanh toán Stripe');
                            }
                            return data;
                        })
                        .then(session => {
                            if (!session.url) {
                                throw new Error('Stripe không trả về đường dẫn thanh toán');
                            }

                            // ========== BƯỚC 3: REDIRECT ĐẾN STRIPE CHECKOUT ==========
                            window.location.href = session.url;
                        });
                    })
                    .catch(error => {
                        confirmReservationButton.disabled = false;
                        confirmReservationButton.innerHTML = '<span>Thanh toán ngay</span><i class="fas fa-arrow-right ml-2"></i>';

                        console.error('Error:', error);
                        alert('❌ Lỗi: ' + error.message);
                    });
                });
            }

            // Khởi tạo tính toán ban đầu
            updateOrderSummary();
        });
    </script>
@endpush
