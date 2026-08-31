@extends('layouts.staff')

@section('title', 'Thanh Toán Tại Quầy (POS)')
@section('page_title', 'Thanh Toán & Xuất Vé')

@section('extra_css')
<style>
    .pos-card {
        background: var(--bg-surface, #ffffff);
        border-radius: 18px;
        border: 1px solid var(--border-light, #e2e8f0);
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    .pos-card-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #ffffff;
        padding: 16px 20px;
    }
    
    .combo-pos-card {
        background: var(--bg-surface, #ffffff);
        border: 1.5px solid var(--border-light, #e2e8f0);
        border-radius: 14px;
        padding: 14px;
        transition: all 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .combo-pos-card:hover {
        border-color: #f59e0b;
        box-shadow: 0 8px 20px rgba(245, 158, 11, 0.12);
        transform: translateY(-2px);
    }
    .combo-img {
        width: 65px;
        height: 65px;
        border-radius: 10px;
        object-fit: cover;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    
    .btn-qty-pos {
        width: 34px;
        height: 34px;
        font-weight: 800;
        font-size: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .btn-qty-pos:hover {
        background: #f59e0b;
        color: #000;
        border-color: #f59e0b;
    }

    /* Cash Calculator */
    .cash-calc-panel {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(245, 158, 11, 0.02) 100%);
        border: 1.5px dashed #f59e0b;
        border-radius: 14px;
        padding: 16px;
    }
    .cash-preset-btn {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .cash-preset-btn:hover {
        background: #f59e0b;
        color: #000;
        border-color: #f59e0b;
    }

    .pos-receipt-sticky {
        position: sticky;
        top: 20px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-4">
    <!-- Header Navigation -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <a href="#" id="backToSeats" class="btn btn-outline-secondary px-3 py-2 rounded-3 fw-bold">
            <i class="fas fa-arrow-left me-2"></i>Chọn Lại Ghế
        </a>
        <div class="text-muted small">
            <i class="fas fa-clock text-warning me-1"></i> Ca làm việc: <strong class="text-dark">{{ Auth::user()->name ?? 'Nhân viên' }}</strong>
        </div>
    </div>

    <!-- Alert for JS errors -->
    <div id="checkoutAlert" class="alert alert-danger d-none shadow-sm rounded-3"></div>

    <div class="row g-4">
        <!-- Left Column: Customer & Concessions -->
        <div class="col-xl-7 col-lg-6">
            <!-- 1. Customer Info -->
            <div class="pos-card mb-4">
                <div class="pos-card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold font-sora"><i class="fas fa-user-circle text-warning me-2"></i>Thông Tin Khách Hàng</span>
                    <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" onclick="setGuestCustomer()">
                        Khách Vãng Lai
                    </button>
                </div>
                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Tên khách hàng</label>
                            <input type="text" id="customer_name" class="form-control rounded-3 py-2" placeholder="Họ và tên...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Số điện thoại (Tích điểm)</label>
                            <input type="tel" id="customer_phone" class="form-control rounded-3 py-2" placeholder="09xxxxxxxx...">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small fw-bold">Email (Nhận vé điện tử / Hóa đơn)</label>
                            <input type="email" id="customer_email" class="form-control rounded-3 py-2" placeholder="email@example.com...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Concessions & Combos -->
            <div class="pos-card mb-4">
                <div class="pos-card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold font-sora"><i class="fas fa-popcorn text-warning me-2"></i>Chọn Bắp Nước (Concessions)</span>
                    <span class="badge bg-warning text-dark">{{ $combos->count() }} món khả dụng</span>
                </div>
                
                <div class="p-4">
                    @if($combos->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-box-open fa-2x mb-2 opacity-50"></i>
                            <p class="mb-0 small">Hiện không có combo bắp nước nào đang bán.</p>
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($combos as $combo)
                                <div class="col-md-6">
                                    <div class="combo-pos-card">
                                        <div class="d-flex align-items-start gap-3 mb-2">
                                            @if($combo->image)
                                                <img src="{{ asset('storage/' . $combo->image) }}" class="combo-img" alt="{{ $combo->name }}">
                                            @else
                                                <div class="combo-img bg-secondary d-flex align-items-center justify-content-center text-white">
                                                    <i class="fas fa-popcorn fa-lg opacity-60"></i>
                                                </div>
                                            @endif
                                            <div class="flex-grow-1">
                                                <h6 class="fw-bold mb-1 font-sora">{{ $combo->name }}</h6>
                                                <p class="text-danger fw-bold mb-0">{{ number_format($combo->price) }}₫</p>
                                                @if($combo->description)
                                                    <p class="text-muted small mb-0 fst-italic" style="font-size: 11px;">{{ $combo->description }}</p>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
                                            <span class="text-muted small">Số lượng:</span>
                                            <div class="d-flex align-items-center gap-2">
                                                <button class="btn-qty-pos" type="button" onclick="updateCombo({{ $combo->id }}, -1)">-</button>
                                                <input type="number" class="form-control text-center combo-qty bg-light fw-bold p-1" 
                                                       value="0" readonly style="width: 45px; height: 34px;"
                                                       data-id="{{ $combo->id }}" 
                                                       data-price="{{ $combo->price }}"
                                                       data-name="{{ $combo->name }}">
                                                <button class="btn-qty-pos" type="button" onclick="updateCombo({{ $combo->id }}, 1)">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- 3. Smart Cash Calculator -->
            <div class="pos-card">
                <div class="pos-card-header">
                    <span class="fw-bold font-sora"><i class="fas fa-calculator text-warning me-2"></i>Tính Tiền Thối Cho Khách (Cash Calculator)</span>
                </div>
                <div class="p-4">
                    <div class="cash-calc-panel">
                        <div class="row g-3 align-items-center mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark mb-1">Tiền khách đưa (₫):</label>
                                <input type="number" id="cashGivenInput" class="form-control form-control-lg fw-bold text-primary font-sora" 
                                       placeholder="Nhập số tiền..." oninput="calculateChange()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark mb-1">Tiền thối lại cho khách:</label>
                                <div class="fs-3 fw-bold text-success font-sora" id="changeAmountDisplay">0₫</div>
                            </div>
                        </div>

                        <!-- Quick Cash Buttons -->
                        <div class="d-flex flex-wrap gap-2 pt-2 border-top border-warning border-opacity-25">
                            <span class="text-muted small align-self-center me-1">Chọn nhanh:</span>
                            <button type="button" class="cash-preset-btn" onclick="setCashGiven('EXACT')">Vừa đủ</button>
                            <button type="button" class="cash-preset-btn" onclick="setCashGiven(100000)">100.000₫</button>
                            <button type="button" class="cash-preset-btn" onclick="setCashGiven(200000)">200.000₫</button>
                            <button type="button" class="cash-preset-btn" onclick="setCashGiven(300000)">300.000₫</button>
                            <button type="button" class="cash-preset-btn" onclick="setCashGiven(500000)">500.000₫</button>
                            <button type="button" class="cash-preset-btn" onclick="setCashGiven(1000000)">1.000.000₫</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Final POS Receipt Sticky -->
        <div class="col-xl-5 col-lg-6">
            <div class="pos-card pos-receipt-sticky">
                <div class="pos-card-header">
                    <h5 class="fw-bold mb-0 font-sora d-flex align-items-center justify-content-between">
                        <span><i class="fas fa-file-invoice-dollar me-2 text-warning"></i>Hóa Đơn Thu Ngân</span>
                        <span class="badge bg-success text-white">POS Walk-in</span>
                    </h5>
                </div>
                
                <div class="p-4">
                    <!-- Movie Ticket Items -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark text-uppercase small">1. Vé Xem Phim</span>
                            <span class="badge bg-primary">{{ count($seatSummary) }} vé</span>
                        </div>
                        @foreach($seatSummary as $seat)
                            <div class="d-flex justify-content-between text-muted small py-1 border-bottom border-light">
                                <span>Ghế <strong>{{ $seat['code'] }}</strong> ({{ $seat['type'] }})</span>
                                <span class="fw-bold text-dark">{{ number_format($seat['final_price']) }}₫</span>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Combos placeholder -->
                    <div id="comboSummaryContainer" class="mb-3 pt-2 d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark text-uppercase small">2. Bắp Nước (Combo)</span>
                        </div>
                        <div id="comboSummaryList"></div>
                    </div>

                    <!-- Coupon Code Section -->
                    <div class="mb-3 pt-3 border-top">
                        <label class="form-label fw-bold small text-dark">Mã Giảm Giá / Voucher</label>
                        <div class="input-group">
                            <input type="text" id="couponCode" class="form-control text-uppercase" placeholder="Nhập mã voucher...">
                            <button class="btn btn-outline-primary fw-bold" type="button" onclick="applyCoupon()">Áp dụng</button>
                        </div>
                        <div id="couponMessage" class="mt-1 small"></div>
                    </div>

                    <!-- Price Calculations -->
                    <div class="pt-3 border-top">
                        <div class="d-flex justify-content-between mb-2 text-muted">
                            <span>Tổng tiền hàng:</span>
                            <span class="fw-bold text-dark" id="subTotalDisplay">{{ number_format($total) }}₫</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-success fw-bold d-none" id="discountRow">
                            <span>Giảm giá voucher:</span>
                            <span id="discountDisplay">-0₫</span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4 pt-3 border-top border-dark">
                            <span class="fw-bold fs-5 text-dark font-sora">KHÁCH PHẢI TRẢ:</span>
                            <span class="fs-2 fw-bold text-danger font-sora" id="finalTotalDisplay">{{ number_format($total) }}₫</span>
                        </div>

                        <!-- Action Button -->
                        <button id="btnCheckout" class="btn btn-success w-100 py-3 fw-bold fs-5 rounded-3 shadow-sm" onclick="processCheckout()">
                            <i class="fas fa-money-bill-wave me-2"></i>XÁC NHẬN THU TIỀN & IN VÉ
                        </button>
                    </div>
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
    const staffBookingId = {{ $staffBookingId ?? 'null' }};
    let baseTotal = {{ $total }}; // Includes ticket prices and surcharge
    let combosTotal = 0;
    let discountAmount = 0;
    let finalAmountPayable = baseTotal;

    function setGuestCustomer() {
        document.getElementById('customer_name').value = 'Khách vãng lai';
        document.getElementById('customer_phone').value = '';
        document.getElementById('customer_email').value = '';
    }

    document.getElementById('backToSeats').addEventListener('click', async (event) => {
        event.preventDefault();
        try {
            await fetch('{{ route('staff.walkin.release-hold') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                keepalive: true,
            });
        } finally {
            history.back();
        }
    });
    
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
                    <div class="d-flex justify-content-between text-muted small py-1 border-bottom border-light">
                        <span><strong>${qty}x</strong> ${input.dataset.name}</span>
                        <span class="fw-bold text-dark">${new Intl.NumberFormat('vi-VN').format(itemTotal)}₫</span>
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
        
        msgEl.innerHTML = '<span class="text-primary"><i class="fas fa-spinner fa-spin"></i> Đang kiểm tra voucher...</span>';
        
        try {
            const subtotal = baseTotal + combosTotal;
            const response = await fetch('/api/apply-coupon', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    code: code,
                    coupon_code: code,
                    order_total: subtotal,
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
            msgEl.innerHTML = '<span class="text-danger">Lỗi kết nối kiểm tra mã.</span>';
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
        
        finalAmountPayable = subTotalAmount - discountAmount;
        if (finalAmountPayable < 0) finalAmountPayable = 0;
        
        document.getElementById('finalTotalDisplay').textContent = new Intl.NumberFormat('vi-VN').format(finalAmountPayable) + '₫';
        calculateChange();
    }

    function setCashGiven(amount) {
        if (amount === 'EXACT') {
            document.getElementById('cashGivenInput').value = finalAmountPayable;
        } else {
            document.getElementById('cashGivenInput').value = amount;
        }
        calculateChange();
    }

    function calculateChange() {
        const cashGiven = parseFloat(document.getElementById('cashGivenInput').value) || 0;
        const changeDisplay = document.getElementById('changeAmountDisplay');
        const diff = cashGiven - finalAmountPayable;

        if (cashGiven === 0) {
            changeDisplay.textContent = '0₫';
            changeDisplay.className = 'fs-3 fw-bold text-muted font-sora';
        } else if (diff >= 0) {
            changeDisplay.textContent = new Intl.NumberFormat('vi-VN').format(diff) + '₫';
            changeDisplay.className = 'fs-3 fw-bold text-success font-sora';
        } else {
            changeDisplay.textContent = 'Còn thiếu ' + new Intl.NumberFormat('vi-VN').format(Math.abs(diff)) + '₫';
            changeDisplay.className = 'fs-3 fw-bold text-danger font-sora';
        }
    }
    
    async function processCheckout() {
        const btn = document.getElementById('btnCheckout');
        const alertBox = document.getElementById('checkoutAlert');
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> ĐANG XUẤT VÉ & LƯU ĐƠN...';
        alertBox.classList.add('d-none');
        
        // Collect Combo Data
        const combos = {};
        document.querySelectorAll('.combo-qty').forEach(input => {
            const qty = parseInt(input.value) || 0;
            if (qty > 0) {
                combos[input.dataset.id] = { qty: qty };
            }
        });
        
        const payload = {
            showtime_id: showtimeId,
            seat_ids: seatIds,
            booking_id: staffBookingId,
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
                window.showToast(result.message || 'Lỗi không xác định khi thanh toán.', 'error');
                alertBox.textContent = result.message || 'Lỗi không xác định khi thanh toán.';
                alertBox.classList.remove('d-none');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-money-bill-wave me-2"></i>THỬ LẠI';
            }
        } catch (e) {
            console.error(e);
<<<<<<< HEAD
            window.showToast('Lỗi hệ thống. Không thể kết nối tới server.', 'error');
            alertBox.textContent = 'Lỗi hệ thống. Không thể kết nối tới server.';
=======
            alertBox.textContent = 'Lỗi hệ thống. Không thể kết nối tới máy chủ.';
>>>>>>> 6047c5e0baf1953fcbc7a848c6eda47789dee5e1
            alertBox.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-money-bill-wave me-2"></i>THỬ LẠI';
        }
    }
</script>
@endsection
