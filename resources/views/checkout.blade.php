@extends('layouts.frontend')

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
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
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
                                <span class="text-slate-400 font-medium mb-1">Tổng thanh toán</span>
                                <span id="final-total" class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-primary to-red-500">{{ number_format($total, 0, ',', '.') }} đ</span>
                            </div>
                        </div>

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
                        <p class="mt-4 text-center text-xs text-slate-500">Ghế của bạn sẽ được giữ trong <span class="text-slate-300 font-medium"><i class="far fa-clock"></i> {{ \App\Services\BookingService::PENDING_PAYMENT_TIMEOUT_MINUTES }} phút</span></p>
                    </div>
                </div>
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(!$showtime || empty($seatSummary))
                return;
            @endif

            const showtimeId = @json($showtimeId ?? '');
            const seatIds = @json($seatIds ?? '');
            const ticketTotal = {{ $total ?? 0 }}; // Tiền vé đã bao gồm phụ thu
            const apiApplyCoupon = @json(route('api.apply-coupon', [], false));
            const reserveUrl = @json(route('checkout.reserve', [], false));
            const successUrl = @json(route('checkout.success', [], false));
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
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
                confirmReservationButton.addEventListener('click', function() {
                    if (!showtimeId || !seatIds) {
                        window.location.href = '/';
                        return;
                    }

                    const selectedPayment = document.querySelector('input[name="payment"]:checked').value;

                    confirmReservationButton.disabled = true;
                    confirmReservationButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Đang xử lý...';
                    
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
                            payment_method: selectedPayment,
                            coupon_code: document.querySelector('input[name="coupon"]:checked') ? document.querySelector('input[name="coupon"]:checked').value : null,
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = successUrl + '?booking_id=' + data.data.booking_id;
                        } else {
                            confirmReservationButton.disabled = false;
                            confirmReservationButton.innerHTML = 'Thanh toán ngay <i class="fas fa-arrow-right ml-2"></i>';
                            couponResult.className = 'text-sm mb-6 text-rose-400 block';
                            couponResult.innerHTML = `<i class="fas fa-exclamation-triangle mr-1"></i> ${data.message}`;
                            couponResult.classList.remove('hidden');
                        }
                    })
                    .catch(() => {
                        confirmReservationButton.disabled = false;
                        confirmReservationButton.innerHTML = 'Thanh toán ngay <i class="fas fa-arrow-right ml-2"></i>';
                        couponResult.className = 'text-sm mb-6 text-rose-400 block';
                        couponResult.innerHTML = '<i class="fas fa-wifi mr-1"></i> Lỗi kết nối, vui lòng thử lại.';
                        couponResult.classList.remove('hidden');
                    });
                });
            }
            
            // Khởi tạo tính toán ban đầu
            updateOrderSummary();
        });
    </script>
@endpush
