@extends('layouts.staff')

@section('page_title', 'Thanh Toán POS - Hoàn Tất Đơn Hàng')

@section('content')
<div class="container-fluid px-0">
    <!-- Top Action Bar -->
    <div class="d-flex align-items-center justify-content-between mb-4 bg-surface p-3 rounded-4 shadow-sm border border-light">
        <div class="d-flex align-items-center gap-3">
            <a href="javascript:history.back()" class="btn btn-outline-secondary rounded-pill px-4 fw-bold shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Trở Lại Chọn Ghế
            </a>
            <div>
                <h4 class="mb-0 fw-extrabold text-ink font-sora"><i class="fas fa-receipt text-amber me-2"></i>Quầy Thanh Toán & Xuất Vé</h4>
                <small class="text-muted">Nhập thông tin khách hàng, chọn bắp nước combo và thu tiền</small>
            </div>
        </div>
        <span class="badge bg-amber text-dark px-3 py-2 rounded-pill fw-bold">
            <i class="fas fa-building me-1"></i> {{ $showtime->room->cinema->name ?? 'Quầy Bán Vé' }}
        </span>
    </div>

    <!-- Alert for JS errors -->
    <div id="checkoutAlert" class="alert alert-danger d-none rounded-3 shadow-sm mb-4"></div>

    <div class="row g-4">
        <!-- Left Column: Customer Info & Combos -->
        <div class="col-lg-7">
            <!-- Customer Info Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--bg-surface);">
                <div class="card-body p-4">
                    <h5 class="fw-extrabold font-sora text-ink mb-3 d-flex align-items-center">
                        <i class="fas fa-user-circle text-amber me-2 fs-4"></i>Thông Tin Khách Hàng (Tùy Chọn)
                    </h5>
                    <p class="text-muted small mb-4">Để trống nếu khách mua vé vãng lai không cung cấp thông tin</p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-sora fw-bold small text-muted">Tên khách hàng</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="fas fa-user"></i></span>
                                <input type="text" id="customer_name" class="form-control border-start-0 ps-0" placeholder="Họ và tên khách hàng...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-sora fw-bold small text-muted">Số điện thoại</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="fas fa-phone"></i></span>
                                <input type="text" id="customer_phone" class="form-control border-start-0 ps-0" placeholder="Số điện thoại nhận SMS...">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-sora fw-bold small text-muted">Email (Để gửi Vé Điện Tử E-Ticket)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="fas fa-envelope"></i></span>
                                <input type="email" id="customer_email" class="form-control border-start-0 ps-0" placeholder="Email nhận vé điện tử...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Combos Selection Card -->
            <div class="card border-0 shadow-sm rounded-4" style="background: var(--bg-surface);">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h5 class="fw-extrabold font-sora text-ink mb-0 d-flex align-items-center">
                            <i class="fas fa-popcorn text-amber me-2 fs-4"></i>Chọn Combo Bắp Nước POS
                        </h5>
                        <span class="badge bg-warning-subtle text-amber border border-warning-subtle px-3 py-1 rounded-pill font-sora fw-bold">
                            <i class="fas fa-cookie-bite me-1"></i>Bắp Nước & Snack
                        </span>
                    </div>

                    @if($combos->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-utensils opacity-50 mb-2 fa-2x"></i>
                            <p class="mb-0 small">Hiện không có sản phẩm combo nào đang khả dụng.</p>
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($combos as $combo)
                                <div class="col-md-6">
                                    <div class="card h-100 border border-light rounded-4 p-3 transition-all hover-shadow" style="background: var(--bg-base);">
                                        <div class="d-flex align-items-center gap-3 mb-3">
                                            @if($combo->image)
                                                <img src="{{ asset('storage/' . $combo->image) }}" class="rounded-3 shadow-sm flex-shrink-0 border border-light" style="width: 60px; height: 60px; object-fit: cover;">
                                            @else
                                                <div class="rounded-3 bg-amber text-white flex-shrink-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                                    <i class="fas fa-popcorn fa-2x"></i>
                                                </div>
                                            @endif
                                            <div class="flex-grow-1">
                                                <h6 class="fw-bold font-sora text-ink mb-1 fs-6">{{ $combo->name }}</h6>
                                                <div class="fw-extrabold text-amber font-sora fs-5">{{ number_format($combo->price) }} ₫</div>
                                            </div>
                                        </div>

                                        <div class="mt-auto pt-2 border-top border-light">
                                            <div class="d-flex align-items-center justify-content-between p-1 rounded-pill bg-surface border border-light shadow-sm">
                                                <button class="btn btn-sm rounded-circle bg-light text-ink font-sora fw-bold border-0 shadow-sm" type="button" style="width: 36px; height: 36px;" onclick="updateCombo({{ $combo->id }}, -1)">-</button>
                                                <input type="number" class="form-control border-0 bg-transparent text-center combo-qty font-sora fw-extrabold fs-5 p-0" value="0" readonly 
                                                       data-id="{{ $combo->id }}" 
                                                       data-price="{{ $combo->price }}"
                                                       data-name="{{ $combo->name }}" style="width: 50px;">
                                                <button class="btn btn-sm rounded-circle btn-amber font-sora fw-bold border-0 shadow-sm" type="button" style="width: 36px; height: 36px;" onclick="updateCombo({{ $combo->id }}, 1)">+</button>
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

        <!-- Right Column: POS Final Receipt Terminal -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden sticky-top" style="top: 20px; background: var(--bg-surface);">
                <!-- Terminal Header -->
                <div class="p-4 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-extrabold font-sora text-white mb-0"><i class="fas fa-file-invoice text-amber me-2"></i>Hóa Đơn POS</h5>
                        <span class="badge bg-amber text-dark fw-bold px-3 py-1 rounded-pill">Quầy Thu Tiền</span>
                    </div>
                    <small class="text-muted"><i class="fas fa-film me-1"></i>{{ $showtime->movie->title }} ({{ $showtime->start_time->format('H:i d/m/Y') }})</small>
                </div>

                <div class="card-body p-4">
                    <!-- Ticket Details -->
                    <div class="mb-3">
                        <label class="form-label font-sora fw-bold small text-muted text-uppercase mb-2">Vé Xem Phim ({{ count($seatSummary) }} vé)</label>
                        <div class="space-y-2">
                            @foreach($seatSummary as $seat)
                                <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-light border border-light small">
                                    <span class="fw-bold font-sora text-ink"><i class="fas fa-chair text-amber me-2"></i>Ghế {{ $seat['code'] }} ({{ $seat['type'] }})</span>
                                    <span class="fw-bold text-ink">{{ number_format($seat['final_price']) }} ₫</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Selected Combos Container -->
                    <div id="comboSummaryContainer" class="mb-3 border-top border-light pt-3 d-none">
                        <label class="form-label font-sora fw-bold small text-muted text-uppercase mb-2">Bắp Nước Đã Chọn</label>
                        <div id="comboSummaryList" class="space-y-2"></div>
                    </div>

                    <!-- Discount Section -->
                    <div class="mb-4 border-top border-light pt-3">
                        <label class="form-label font-sora fw-bold small text-muted text-uppercase mb-2">Mã Giảm Giá (Coupon)</label>
                        <div class="input-group">
                            <input type="text" id="couponCode" class="form-control" placeholder="Nhập mã ưu đãi...">
                            <button class="btn btn-outline-amber font-sora fw-bold px-3" type="button" onclick="applyCoupon()">Áp dụng</button>
                        </div>
                        <div id="couponMessage" class="mt-2 small font-sora"></div>
                    </div>

                    <!-- Payment Method Radio Cards -->
                    <div class="mb-4">
                        <label class="form-label font-sora fw-bold small text-muted text-uppercase mb-2">Hình Thức Thu Tiền</label>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="form-check p-3 rounded-3 border border-amber bg-amber-subtle d-flex align-items-center justify-content-between">
                                <div>
                                    <input class="form-check-input me-2" type="radio" name="payment_method_radio" id="payCash" value="CASH" checked>
                                    <label class="form-check-label fw-bold font-sora text-ink" for="payCash">
                                        <i class="fas fa-money-bill-wave text-success me-2"></i>Tiền Mặt Tại Quầy (CASH)
                                    </label>
                                </div>
                                <span class="badge bg-success text-white">Khuyên dùng</span>
                            </div>
                        </div>
                    </div>

                    <!-- Totals Summary -->
                    <div class="space-y-2 mb-4 p-3 rounded-3 bg-light border border-light">
                        <div class="d-flex justify-content-between text-muted small">
                            <span>Tạm tính tổng tiền:</span>
                            <span id="subTotalDisplay" class="fw-bold text-ink">{{ number_format($total) }} ₫</span>
                        </div>
                        <div class="d-flex justify-content-between text-success fw-bold small d-none" id="discountRow">
                            <span>Giảm giá mã ưu đãi:</span>
                            <span id="discountDisplay">-0 ₫</span>
                        </div>
                        <hr class="my-2 border-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-extrabold text-ink font-sora fs-6">KHÁCH PHẢI TRẢ:</span>
                            <span id="finalTotalDisplay" class="fw-extrabold text-danger fs-2 font-sora">{{ number_format($total) }} ₫</span>
                        </div>
                    </div>

                    <!-- Submit Action Button -->
                    <button id="btnCheckout" class="btn btn-amber w-100 py-3 fw-extrabold font-sora fs-4 rounded-3 shadow-lg" onclick="processCheckout()">
                        <i class="fas fa-check-circle me-2"></i>THU TIỀN & XUẤT VÉ
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
    let baseTotal = {{ $total }};
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
                    <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-light border border-light small mb-1">
                        <span class="fw-bold font-sora text-ink"><i class="fas fa-popcorn text-amber me-2"></i>${qty}x ${input.dataset.name}</span>
                        <span class="fw-bold text-ink">${new Intl.NumberFormat('vi-VN').format(itemTotal)} ₫</span>
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
        
        msgEl.innerHTML = '<span class="text-amber"><i class="fas fa-spinner fa-spin me-1"></i>Đang xác minh mã...</span>';
        
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
                msgEl.innerHTML = '<span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i>Áp dụng mã giảm giá thành công!</span>';
                document.getElementById('couponCode').disabled = true;
                updateFinalTotal();
            } else {
                discountAmount = 0;
                msgEl.innerHTML = `<span class="text-danger fw-bold"><i class="fas fa-times-circle me-1"></i>${result.message}</span>`;
                updateFinalTotal();
            }
        } catch (e) {
            msgEl.innerHTML = '<span class="text-danger">Lỗi kết nối máy chủ.</span>';
        }
    }
    
    function updateFinalTotal() {
        const subTotalAmount = baseTotal + combosTotal;
        document.getElementById('subTotalDisplay').textContent = new Intl.NumberFormat('vi-VN').format(subTotalAmount) + ' ₫';
        
        if (discountAmount > 0) {
            document.getElementById('discountRow').classList.remove('d-none');
            document.getElementById('discountDisplay').textContent = '-' + new Intl.NumberFormat('vi-VN').format(discountAmount) + ' ₫';
        } else {
            document.getElementById('discountRow').classList.add('d-none');
        }
        
        let finalAmount = subTotalAmount - discountAmount;
        if (finalAmount < 0) finalAmount = 0;
        
        document.getElementById('finalTotalDisplay').textContent = new Intl.NumberFormat('vi-VN').format(finalAmount) + ' ₫';
    }
    
    async function processCheckout() {
        const btn = document.getElementById('btnCheckout');
        const alertBox = document.getElementById('checkoutAlert');
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>ĐANG XỬ LÝ GIAO DỊCH...';
        alertBox.classList.add('d-none');
        
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
                btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>THU TIỀN & XUẤT VÉ LẠI';
            }
        } catch (e) {
            console.error(e);
            alertBox.textContent = 'Lỗi hệ thống. Không thể kết nối tới server.';
            alertBox.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>THU TIỀN & XUẤT VÉ LẠI';
        }
    }
</script>

<style>
    .btn-amber {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #ffffff;
        border: none;
    }
    .btn-amber:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        color: #ffffff;
    }
    .btn-outline-amber {
        border-color: #f59e0b;
        color: #d97706;
    }
    .btn-outline-amber:hover {
        background: #f59e0b;
        color: #ffffff;
    }
    .hover-shadow:hover {
        box-shadow: 0 8px 20px rgba(0,0,0,0.06) !important;
    }
</style>
@endsection
