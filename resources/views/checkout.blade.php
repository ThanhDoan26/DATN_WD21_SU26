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

    <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-3xl font-bold mb-8 text-white"><i class="fas fa-shopping-cart text-primary mr-2"></i>Thanh Toán Đơn Hàng</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- Left col: Chi tiết vé -->
            <div class="md:col-span-2 space-y-6">
                <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700 shadow-xl">
                    <h2 class="text-xl font-semibold mb-4 text-white">Vé Phim Của Bạn</h2>
                    
                    <div class="flex gap-4 mb-4 pb-4 border-b border-slate-700">
                        <div class="w-24 h-32 bg-slate-700 rounded-lg overflow-hidden flex-shrink-0">
                            <!-- Placeholder Movie Image -->
                            <img src="https://images.unsplash.com/photo-1536440136628-849c177e76a1?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" class="w-full h-full object-cover" alt="Movie">
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-white mb-1">Avatar: The Way of Water</h3>
                            <p class="text-slate-400 text-sm mb-2"><i class="fas fa-map-marker-alt mr-1"></i> movieGo Vincom Metropolis</p>
                            <p class="text-slate-400 text-sm mb-2"><i class="fas fa-clock mr-1"></i> 19:30 - Hôm nay</p>
                            <div class="mt-2 flex gap-2">
                                <span class="bg-primary/20 text-red-400 px-2 py-1 rounded text-xs font-semibold">Phòng 01</span>
                                <span class="bg-slate-700 text-slate-300 px-2 py-1 rounded text-xs font-semibold">Ghế: G10, G11</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- NEW: Combo Bắp Nước -->
                <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700 shadow-xl">
                    <h2 class="text-xl font-semibold mb-4 text-white">Combo Bắp Nước</h2>
                    <div class="space-y-4">
                        @foreach($combos as $combo)
                        <div class="flex items-center gap-4 bg-slate-900 p-4 rounded-xl border border-slate-700">
                            <div class="w-16 h-16 bg-slate-700 rounded-lg overflow-hidden flex-shrink-0">
                                @if($combo->image)
                                    <img src="{{ asset('storage/' . $combo->image) }}" alt="{{ $combo->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-500"><i class="fas fa-hamburger"></i></div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h3 class="text-white font-semibold">{{ $combo->name }}</h3>
                                <p class="text-slate-400 text-xs mt-1">{{ $combo->description }}</p>
                                <p class="text-primary font-bold mt-1" data-price="{{ $combo->price }}">{{ number_format($combo->price, 0, ',', '.') }} đ</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="button" class="btn-decrease-combo w-8 h-8 rounded-full bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 flex items-center justify-center border border-slate-600 transition-colors" data-id="{{ $combo->id }}">
                                    <i class="fas fa-minus text-xs"></i>
                                </button>
                                <span class="combo-quantity font-semibold text-white w-4 text-center" data-id="{{ $combo->id }}" data-name="{{ $combo->name }}" data-price="{{ $combo->price }}">0</span>
                                <button type="button" class="btn-increase-combo w-8 h-8 rounded-full bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 flex items-center justify-center border border-slate-600 transition-colors" data-id="{{ $combo->id }}">
                                    <i class="fas fa-plus text-xs"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                        @if($combos->isEmpty())
                            <p class="text-slate-400 text-sm">Hiện không có combo nào.</p>
                        @endif
                    </div>
                </div>

                <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700 shadow-xl">
                    <h2 class="text-xl font-semibold mb-4 text-white">Thông Tin Thanh Toán</h2>
                    <p class="text-slate-400">Chọn phương thức thanh toán...</p>
                    <!-- Form thanh toán dummy -->
                    <div class="mt-4 flex gap-4">
                        <label class="flex-1 border border-slate-600 rounded-xl p-4 cursor-pointer hover:border-primary transition-colors flex flex-col items-center gap-2">
                            <input type="radio" name="payment" class="hidden" checked>
                            <i class="fas fa-qrcode text-3xl text-pink-500"></i>
                            <span class="text-sm font-medium">MoMo</span>
                        </label>
                        <label class="flex-1 border border-slate-600 rounded-xl p-4 cursor-pointer hover:border-primary transition-colors flex flex-col items-center gap-2">
                            <input type="radio" name="payment" class="hidden">
                            <i class="fas fa-credit-card text-3xl text-blue-500"></i>
                            <span class="text-sm font-medium">Thẻ ATM</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Right col: Tóm tắt & Mã giảm giá -->
            <div class="md:col-span-1">
                <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700 shadow-xl sticky top-10">
                    <h2 class="text-xl font-semibold mb-6 text-white border-b border-slate-700 pb-2">Tóm Tắt Đơn Hàng</h2>
                    
                    <div class="space-y-3 text-sm mb-6">
                        <div class="flex justify-content-between flex justify-between">
                            <span class="text-slate-400">2x Ghế VIP</span>
                            <span class="font-medium">200.000 đ</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">2x Ghế VIP</span>
                            <span class="font-medium">200.000 đ</span>
                        </div>
                        <!-- Danh sách Combos đã chọn -->
                        <div id="selected_combos_container" class="space-y-3"></div>
                        <div class="flex justify-between border-t border-slate-700 pt-3">
                            <span class="text-slate-300 font-medium">Tạm tính</span>
                            <span class="font-bold text-white" id="subtotal_display">265.000 đ</span>
                        </div>
                        <!-- Hiển thị giảm giá -->
                        <div class="flex justify-between text-emerald-400 hidden" id="discount_row">
                            <span>Mã giảm giá</span>
                            <span class="font-bold" id="discount_display">-0 đ</span>
                        </div>
                    </div>

                    <!-- Input Mã Giảm Giá -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-400 mb-2">Mã Khuyến Mãi</label>
                        <div class="flex gap-2">
                            <input type="text" id="coupon_code" placeholder="Nhập mã..." class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-primary uppercase transition-colors">
                            <button id="btn_apply_coupon" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg font-medium transition-colors whitespace-nowrap">Áp dụng</button>
                        </div>
                        <div id="coupon_message" class="text-xs mt-2 hidden"></div>
                    </div>

                    <div class="border-t border-slate-700 pt-4 mb-6 flex justify-between items-end">
                        <span class="text-slate-400 font-medium">Tổng Tiền</span>
                        <span class="text-2xl font-bold text-primary" id="final_total_display">265.000 đ</span>
                    </div>

                    <button class="w-full bg-primary hover:bg-red-700 text-white py-3 rounded-xl font-bold text-lg shadow-lg shadow-primary/30 transition-all hover:-translate-y-1">
                        Thanh Toán
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Script xử lý Coupon -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Biến nội bộ
            let ticketTotal = 200000; // Giá trị giả lập tiền vé (2x Ghế VIP = 200k)
            let combosTotal = 0;
            let currentDiscount = 0;
            let finalTotal = ticketTotal; 
            
            // Map lưu combo đang chọn: id => {name, price, qty}
            const selectedCombos = {};
            
            const btnApply = document.getElementById('btn_apply_coupon');
            const inputCode = document.getElementById('coupon_code');
            const msgBox = document.getElementById('coupon_message');
            const discountRow = document.getElementById('discount_row');
            const discountDisplay = document.getElementById('discount_display');
            const subtotalDisplay = document.getElementById('subtotal_display');
            const finalTotalDisplay = document.getElementById('final_total_display');
            const combosContainer = document.getElementById('selected_combos_container');

            // Format tiền tệ
            const formatMoney = (amount) => {
                return new Intl.NumberFormat('vi-VN').format(amount) + ' đ';
            };

            const updateOrderSummary = () => {
                combosTotal = 0;
                combosContainer.innerHTML = '';
                
                Object.values(selectedCombos).forEach(combo => {
                    if (combo.qty > 0) {
                        combosTotal += combo.price * combo.qty;
                        const div = document.createElement('div');
                        div.className = 'flex justify-between';
                        div.innerHTML = `<span class="text-slate-400">${combo.qty}x ${combo.name}</span><span class="font-medium">${formatMoney(combo.price * combo.qty)}</span>`;
                        combosContainer.appendChild(div);
                    }
                });

                let subtotal = ticketTotal + combosTotal;
                subtotalDisplay.textContent = formatMoney(subtotal);
                
                finalTotal = subtotal - currentDiscount;
                if(finalTotal < 0) finalTotal = 0;
                
                finalTotalDisplay.textContent = formatMoney(finalTotal);
            };

            // Event listeners cho các nút tăng giảm combo
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

            btnApply.addEventListener('click', function() {
                const code = inputCode.value.trim().toUpperCase();
                
                if(!code) {
                    msgBox.className = 'text-xs mt-2 text-red-400';
                    msgBox.textContent = 'Vui lòng nhập mã giảm giá!';
                    msgBox.classList.remove('hidden');
                    return;
                }

                // Hiển thị loading
                btnApply.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btnApply.disabled = true;

                let orderTotalForCoupon = ticketTotal + combosTotal;

                // Call API
                fetch('/api/apply-coupon', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        code: code,
                        order_total: orderTotalForCoupon
                    })
                })
                .then(response => response.json())
                .then(data => {
                    btnApply.innerHTML = 'Áp dụng';
                    btnApply.disabled = false;
                    msgBox.classList.remove('hidden');

                    if(data.success) {
                        msgBox.className = 'text-xs mt-2 text-emerald-400';
                        msgBox.innerHTML = `<i class="fas fa-check-circle"></i> ${data.message}`;
                        
                        currentDiscount = data.data.discount_amount;
                        discountRow.classList.remove('hidden');
                        discountDisplay.textContent = '-' + formatMoney(currentDiscount);
                        
                        updateOrderSummary();
                        
                        inputCode.disabled = true;
                        btnApply.classList.add('hidden'); // Ẩn nút áp dụng
                    } else {
                        msgBox.className = 'text-xs mt-2 text-red-400';
                        msgBox.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.message}`;
                        currentDiscount = 0;
                        discountRow.classList.add('hidden');
                        updateOrderSummary();
                    }
                })
                .catch(error => {
                    btnApply.innerHTML = 'Áp dụng';
                    btnApply.disabled = false;
                    msgBox.className = 'text-xs mt-2 text-red-400';
                    msgBox.textContent = 'Có lỗi xảy ra, vui lòng thử lại sau.';
                    msgBox.classList.remove('hidden');
                    console.error('Error:', error);
                });
            });
            
            // Khởi tạo tính toán ban đầu
            updateOrderSummary();
        });
    </script>
</body>
</html>
