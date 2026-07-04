@extends('layouts.staff')

@section('content')
<div class="container-fluid p-4 bg-white rounded-3 shadow-sm">
    <div class="d-flex align-items-center mb-4 border-bottom pb-3">
        <a href="javascript:history.back()" class="btn btn-outline-secondary me-3">
            <i class="fas fa-arrow-left"></i> Trở Lại
        </a>
        <h2 class="mb-0 text-primary fw-bold"><i class="fas fa-cash-register me-2"></i>Thanh Toán</h2>
    </div>

    <!-- Alert for JS errors -->
    <div id="checkoutAlert" class="alert alert-danger d-none"></div>

    <div class="row">
        <!-- Left Column: Combos & Customer Info -->
        <div class="col-lg-7 mb-4">
            <!-- Customer Info -->
            <div class="card mb-4 shadow-sm border-0 bg-light">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-dark mb-3"><i class="fas fa-user-circle text-primary me-2"></i>Thông tin Khách hàng (Tùy chọn)</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Tên khách hàng</label>
                            <input type="text" id="customer_name" class="form-control" placeholder="Nhập tên KH...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Số điện thoại</label>
                            <input type="text" id="customer_phone" class="form-control" placeholder="Số điện thoại...">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small">Email (Để gửi vé điện tử)</label>
                            <input type="email" id="customer_email" class="form-control" placeholder="Email...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Combos -->
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-dark mb-3"><i class="fas fa-popcorn text-warning me-2"></i>Chọn Bắp Ước</h5>
                    @if($combos->isEmpty())
                        <p class="text-muted">Không có combo nào đang bán.</p>
                    @else
                        <div class="row g-3">
                            @foreach($combos as $combo)
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100 d-flex flex-column">
                                        <div class="d-flex align-items-start mb-2">
                                            @if($combo->image)
                                                <img src="{{ asset('storage/' . $combo->image) }}" class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <div class="bg-secondary text-white rounded d-flex align-items-center justify-content-center me-2" style="width: 50px; height: 50px;">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="fw-bold mb-1">{{ $combo->name }}</h6>
                                                <p class="text-danger fw-bold mb-0 small">{{ number_format($combo->price) }}₫</p>
                                            </div>
                                        </div>
                                        <div class="mt-auto pt-2 border-top">
                                            <div class="input-group input-group-sm">
                                                <button class="btn btn-outline-secondary btn-minus" type="button" onclick="updateCombo({{ $combo->id }}, -1)">-</button>
                                                <input type="number" class="form-control text-center combo-qty bg-white" value="0" readonly 
                                                       data-id="{{ $combo->id }}" 
                                                       data-price="{{ $combo->price }}"
                                                       data-name="{{ $combo->name }}">
                                                <button class="btn btn-outline-secondary btn-plus" type="button" onclick="updateCombo({{ $combo->id }}, 1)">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Final Bill / Checkout -->
        <div class="col-lg-5">
            <div class="card border-primary sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas fa-receipt me-2"></i>Hóa Đơn Bán Hàng
                </div>
                <div class="card-body bg-light">
                    <!-- Tickets -->
                    <div class="mb-3">
                        <p class="fw-bold mb-2">Vé xem phim ({{ count($seatSummary) }} vé)</p>
                        @foreach($seatSummary as $seat)
                            <div class="d-flex justify-content-between text-muted small mb-1">
                                <span>Ghế {{ $seat['code'] }} ({{ $seat['type'] }})</span>
                                <span>{{ number_format($seat['final_price']) }}₫</span>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Combos placeholder -->
                    <div id="comboSummaryContainer" class="mb-3 border-top pt-2 d-none">
                        <p class="fw-bold mb-2">Bắp nước</p>
                        <div id="comboSummaryList"></div>
                    </div>

                    <!-- Discount Section -->
                    <div class="mb-3 border-top pt-3">
                        <label class="form-label fw-bold small">Mã Giảm Giá</label>
                        <div class="input-group">
                            <input type="text" id="couponCode" class="form-control" placeholder="Nhập mã...">
                            <button class="btn btn-outline-primary" type="button" onclick="applyCoupon()">Áp dụng</button>
                        </div>
                        <div id="couponMessage" class="mt-1 small"></div>
                    </div>

                    <hr class="border-secondary">

                    <!-- Totals -->
                    <div class="d-flex justify-content-between mb-1 text-muted">
                        <span>Tổng tiền hàng:</span>
                        <span id="subTotalDisplay">{{ number_format($total) }}₫</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-success fw-bold d-none" id="discountRow">
                        <span>Giảm giá:</span>
                        <span id="discountDisplay">-0₫</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4 pt-2 border-top border-dark">
                        <span class="fw-bold fs-5">KHÁCH PHẢI TRẢ:</span>
                        <span class="fs-3 fw-bold text-danger" id="finalTotalDisplay">{{ number_format($total) }}₫</span>
                    </div>

                    <p class="text-center text-muted small mb-2"><i class="fas fa-info-circle"></i> Phương thức thu: <strong>TIỀN MẶT</strong></p>

                    <button id="btnCheckout" class="btn btn-success w-100 py-3 fw-bold fs-5 shadow-sm" onclick="processCheckout()">
                        <i class="fas fa-money-bill-wave me-2"></i>THU TIỀN & XUẤT VÉ
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_js')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const showtimeId = {{ $showtimeId }};
    const seatIds = "{{ is_array($seatIds) ? implode(',', $seatIds) : $seatIds }}";
    let baseTotal = {{ $total }}; // Includes ticket prices and surcharge
    let combosTotal = 0;
    let discountAmount = 0;
    
    function updateCombo(id, change) {
        const input = document.querySelector(`.combo-qty[data-id="${id}"]`);
        let currentVal = parseInt(input.value) || 0;
        let newVal = currentVal + change;
        if (newVal < 0) newVal = 0;
        input.value = newVal;
        
        recalculateCart();
    }
    
    function recalculateCart() {
        combosTotal = 0;
        const comboSummaryList = document.getElementById('comboSummaryList');
        const comboSummaryContainer = document.getElementById('comboSummaryContainer');
        comboSummaryList.innerHTML = '';
        
        let comboCount = 0;
        document.querySelectorAll('.combo-qty').forEach(input => {
            const qty = parseInt(input.value) || 0;
            if (qty > 0) {
                comboCount++;
                const price = parseFloat(input.dataset.price);
                const itemTotal = price * qty;
                combosTotal += itemTotal;
                
                comboSummaryList.innerHTML += `
                    <div class="d-flex justify-content-between text-muted small mb-1">
                        <span>${qty}x ${input.dataset.name}</span>
                        <span>${new Intl.NumberFormat('vi-VN').format(itemTotal)}₫</span>
                    </div>
                `;
            }
        });
        
        if (comboCount > 0) {
            comboSummaryContainer.classList.remove('d-none');
        } else {
            comboSummaryContainer.classList.add('d-none');
        }
        
        updateFinalTotal();
    }
    
    async function applyCoupon() {
        const code = document.getElementById('couponCode').value.trim();
        const msgEl = document.getElementById('couponMessage');
        
        if (!code) {
            discountAmount = 0;
            updateFinalTotal();
            msgEl.innerHTML = '';
            return;
        }
        
        msgEl.innerHTML = '<span class="text-primary">Đang kiểm tra...</span>';
        
        try {
            const subtotal = baseTotal + combosTotal;
            const response = await fetch('/api/apply-coupon', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    coupon_code: code,
                    subtotal: subtotal
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                discountAmount = parseFloat(result.discount_amount);
                msgEl.innerHTML = '<span class="text-success fw-bold"><i class="fas fa-check-circle"></i> Áp dụng thành công!</span>';
                document.getElementById('couponCode').disabled = true;
                updateFinalTotal();
            } else {
                discountAmount = 0;
                msgEl.innerHTML = `<span class="text-danger"><i class="fas fa-times-circle"></i> ${result.message}</span>`;
                updateFinalTotal();
            }
        } catch (e) {
            msgEl.innerHTML = '<span class="text-danger">Lỗi kết nối.</span>';
        }
    }
    
    function updateFinalTotal() {
        const subTotalAmount = baseTotal + combosTotal;
        document.getElementById('subTotalDisplay').textContent = new Intl.NumberFormat('vi-VN').format(subTotalAmount) + '₫';
        
        if (discountAmount > 0) {
            document.getElementById('discountRow').classList.remove('d-none');
            document.getElementById('discountDisplay').textContent = '-' + new Intl.NumberFormat('vi-VN').format(discountAmount) + '₫';
        } else {
            document.getElementById('discountRow').classList.add('d-none');
        }
        
        let finalAmount = subTotalAmount - discountAmount;
        if (finalAmount < 0) finalAmount = 0;
        
        document.getElementById('finalTotalDisplay').textContent = new Intl.NumberFormat('vi-VN').format(finalAmount) + '₫';
    }
    
    async function processCheckout() {
        const btn = document.getElementById('btnCheckout');
        const alertBox = document.getElementById('checkoutAlert');
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        alertBox.classList.add('d-none');
        
        // Collect Combo Data
        const combos = {};
        document.querySelectorAll('.combo-qty').forEach(input => {
            const qty = parseInt(input.value) || 0;
            if (qty > 0) {
                combos[input.dataset.id] = qty;
            }
        });
        
        const payload = {
            showtime_id: showtimeId,
            seat_ids: seatIds,
            combos: Object.keys(combos).length > 0 ? combos : null,
            payment_method: 'CASH',
            coupon_code: document.getElementById('couponCode').value.trim() || null,
            customer_name: document.getElementById('customer_name').value.trim() || null,
            customer_phone: document.getElementById('customer_phone').value.trim() || null,
            customer_email: document.getElementById('customer_email').value.trim() || null,
        };
        
        try {
            const response = await fetch('/staff/walk-in/reserve', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });
            
            const result = await response.json();
            
            if (result.success && result.redirect_url) {
                window.location.href = result.redirect_url;
            } else {
                alertBox.textContent = result.message || 'Lỗi không xác định khi thanh toán.';
                alertBox.classList.remove('d-none');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-money-bill-wave me-2"></i>THU TIỀN LẠI';
            }
        } catch (e) {
            console.error(e);
            alertBox.textContent = 'Lỗi hệ thống. Không thể kết nối tới server.';
            alertBox.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-money-bill-wave me-2"></i>THU TIỀN LẠI';
        }
    }
</script>
@endsection
