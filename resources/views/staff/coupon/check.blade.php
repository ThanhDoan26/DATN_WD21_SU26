@extends('layouts.staff')

@section('title', 'Kiểm Tra Phiếu Giảm Giá')
@section('page_title', 'Kiểm Tra Phiếu Giảm Giá')

@section('extra_css')
<style>
    .coupon-hero {
        background: linear-gradient(135deg, #1c1408 0%, #2d1f05 50%, #1c1408 100%);
        border-radius: 20px;
        padding: 32px;
        position: relative;
        overflow: hidden;
        margin-bottom: 28px;
    }
    .coupon-hero::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 200px; height: 200px;
        background: radial-gradient(circle, rgba(245,158,11,0.18) 0%, transparent 70%);
        border-radius: 50%;
    }
    .coupon-hero::after {
        content: '';
        position: absolute;
        bottom: -40px; left: -40px;
        width: 150px; height: 150px;
        background: radial-gradient(circle, rgba(245,158,11,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .search-input-wrap {
        position: relative;
    }
    .search-input-wrap .search-icon {
        position: absolute;
        left: 18px; top: 50%;
        transform: translateY(-50%);
        color: #f59e0b;
        font-size: 1.2rem;
        pointer-events: none;
        z-index: 2;
    }
    #couponCodeInput {
        padding-left: 50px;
        height: 58px;
        font-size: 1.25rem;
        font-weight: 700;
        letter-spacing: 3px;
        text-transform: uppercase;
        border-radius: 14px;
        border: 2px solid rgba(245,158,11,0.3);
        background: rgba(255,255,255,0.08);
        color: #fff;
        transition: all 0.2s;
    }
    #couponCodeInput::placeholder { color: rgba(255,255,255,0.35); letter-spacing: 1px; font-weight: 400; font-size: 1rem; }
    #couponCodeInput:focus {
        outline: none;
        border-color: #f59e0b;
        background: rgba(255,255,255,0.12);
        box-shadow: 0 0 0 4px rgba(245,158,11,0.15);
    }
    .btn-check-coupon {
        height: 58px;
        min-width: 140px;
        font-size: 1rem;
        font-weight: 700;
        border-radius: 14px;
        background: linear-gradient(135deg, #d97706, #f59e0b);
        border: none;
        color: #1c1408;
        transition: all 0.2s;
        box-shadow: 0 4px 16px rgba(245,158,11,0.35);
    }
    .btn-check-coupon:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(245,158,11,0.45);
        color: #1c1408;
    }
    .btn-check-coupon:disabled {
        opacity: 0.7;
        transform: none;
    }

    /* Result Card */
    .result-card {
        border-radius: 18px;
        border: 1.5px solid var(--border-light, #e2e8f0);
        background: var(--bg-surface, #fff);
        box-shadow: 0 8px 30px rgba(0,0,0,0.07);
        overflow: hidden;
        animation: fadeSlideIn 0.35s ease;
    }
    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .result-header {
        padding: 20px 24px;
    }
    .result-header.verdict-valid    { background: linear-gradient(135deg, #065f46, #047857); }
    .result-header.verdict-warning  { background: linear-gradient(135deg, #78350f, #92400e); }
    .result-header.verdict-invalid  { background: linear-gradient(135deg, #7f1d1d, #991b1b); }
    .result-header.verdict-notfound { background: linear-gradient(135deg, #1e293b, #334155); }

    .verdict-badge {
        font-size: 1rem;
        font-weight: 800;
        padding: 6px 18px;
        border-radius: 30px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        letter-spacing: 0.04em;
    }
    .verdict-badge.valid   { background: rgba(255,255,255,0.2); color: #d1fae5; }
    .verdict-badge.warning { background: rgba(255,255,255,0.2); color: #fef3c7; }
    .verdict-badge.invalid { background: rgba(255,255,255,0.2); color: #fee2e2; }

    /* Coupon info chips */
    .coupon-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--bg-base, #f8fafc);
        border: 1px solid var(--border-light, #e2e8f0);
        border-radius: 10px;
        padding: 8px 14px;
        font-size: 0.82rem;
        font-weight: 600;
    }
    .coupon-chip .chip-label { color: var(--text-muted, #64748b); font-weight: 500; }
    .coupon-chip .chip-val   { color: var(--text-ink, #0f172a); }

    /* Check items */
    .check-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 12px 0;
        border-bottom: 1px solid var(--border-light, #f1f5f9);
    }
    .check-item:last-child { border-bottom: none; }
    .check-icon {
        width: 32px; height: 32px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.9rem;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .check-icon.pass    { background: #d1fae5; color: #065f46; }
    .check-icon.fail    { background: #fee2e2; color: #991b1b; }
    .check-icon.warn    { background: #fef3c7; color: #92400e; }
    .check-label { font-weight: 700; font-size: 0.88rem; color: var(--text-ink, #0f172a); }
    .check-detail { font-size: 0.8rem; color: var(--text-muted, #64748b); margin-top: 2px; }

    /* Discount highlight */
    .discount-highlight {
        background: linear-gradient(135deg, rgba(5, 150, 105, 0.08), rgba(16, 185, 129, 0.04));
        border: 1.5px solid rgba(5, 150, 105, 0.25);
        border-radius: 14px;
        padding: 16px 20px;
    }

    /* History pills */
    .history-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--bg-base, #f8fafc);
        border: 1px solid var(--border-light, #e2e8f0);
        border-radius: 20px;
        padding: 5px 14px;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.15s;
    }
    .history-pill:hover {
        border-color: #f59e0b;
        background: rgba(245,158,11,0.06);
        color: #d97706;
    }
    .history-pill .dot {
        width: 8px; height: 8px;
        border-radius: 50%;
    }
    .history-pill .dot.valid   { background: #10b981; }
    .history-pill .dot.warning { background: #f59e0b; }
    .history-pill .dot.invalid { background: #ef4444; }

    .optional-label {
        font-size: 0.75rem;
        background: rgba(245,158,11,0.15);
        color: #b45309;
        border-radius: 6px;
        padding: 2px 8px;
        font-weight: 600;
        white-space: nowrap;
    }

    /* Order total input group */
    .order-input-group {
        position: relative;
        width: 210px;
    }
    .order-input-group .currency-icon {
        position: absolute;
        left: 12px; top: 50%;
        transform: translateY(-50%);
        color: rgba(253,230,138,0.6);
        font-size: 0.85rem;
        pointer-events: none;
        z-index: 2;
        font-weight: 700;
    }
    #orderTotalInput {
        padding-left: 32px;
        padding-right: 14px;
        background: rgba(255,255,255,0.08);
        border: 1.5px solid rgba(245,158,11,0.3);
        color: #fff;
        border-radius: 10px;
        height: 38px;
        font-size: 0.88rem;
        font-weight: 600;
        width: 100%;
        transition: all 0.2s;
        letter-spacing: 0.5px;
    }
    #orderTotalInput::placeholder { color: rgba(255,255,255,0.3); font-weight: 400; }
    #orderTotalInput:focus {
        outline: none;
        border-color: #f59e0b;
        background: rgba(255,255,255,0.12);
        box-shadow: 0 0 0 3px rgba(245,158,11,0.2);
    }
    #orderTotalInput.input-error {
        border-color: #f87171 !important;
        background: rgba(239,68,68,0.1) !important;
        box-shadow: 0 0 0 3px rgba(239,68,68,0.2) !important;
    }
    #orderTotalInput.input-ok {
        border-color: #34d399 !important;
        background: rgba(16,185,129,0.08) !important;
    }
    .order-error-msg {
        font-size: 0.72rem;
        color: #f87171;
        margin-top: 4px;
        display: none;
        align-items: center;
        gap: 4px;
    }
    .order-error-msg.show { display: flex; }
</style>
@endsection

@section('content')
<div class="container-fluid p-4" style="max-width: 820px; margin: 0 auto;">

    {{-- ── Hero Search Section ────────────────────────────────────────── --}}
    <div class="coupon-hero">
        <div class="position-relative" style="z-index: 1;">
            <div class="mb-3">
                <span class="badge fs-6 fw-bold px-3 py-2" style="background: rgba(245,158,11,0.2); color: #fde68a; border-radius: 10px;">
                    <i class="fas fa-shield-alt me-2"></i>Công Cụ Kiểm Tra Voucher
                </span>
            </div>
            <h2 class="fw-bold text-white font-sora mb-1" style="font-size: 1.6rem;">Tra Cứu Phiếu Giảm Giá</h2>
            <p class="mb-4" style="color: rgba(253,230,138,0.7); font-size: 0.9rem;">
                Nhập mã voucher của khách hàng để kiểm tra tình trạng hiệu lực ngay lập tức.
            </p>

            <div class="d-flex gap-3 align-items-stretch" id="searchForm">
                <div class="search-input-wrap flex-grow-1">
                    <i class="fas fa-ticket-alt search-icon"></i>
                    <input type="text" id="couponCodeInput"
                           class="form-control"
                           placeholder="Nhập mã voucher... (vd: SALE2026)"
                           autocomplete="off"
                           maxlength="100">
                </div>
                <button class="btn btn-check-coupon" id="btnCheck" onclick="checkCoupon()">
                    <i class="fas fa-search me-2"></i>Kiểm Tra
                </button>
            </div>

            {{-- Optional: order total --}}
            <div class="mt-3">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span style="color: rgba(253,230,138,0.6); font-size: 0.82rem;">
                        <i class="fas fa-info-circle me-1"></i>Tùy chọn:
                    </span>
                    <span class="optional-label">Không bắt buộc</span>
                    <div>
                        <div class="order-input-group">
                            <span class="currency-icon">₫</span>
                            <input type="text" id="orderTotalInput"
                                   inputmode="numeric"
                                   placeholder="Nhập giá trị đơn hàng"
                                   autocomplete="off"
                                   maxlength="15">
                        </div>
                        <div class="order-error-msg" id="orderTotalError">
                            <i class="fas fa-exclamation-circle"></i>
                            <span id="orderTotalErrorText"></span>
                        </div>
                    </div>
                    <span style="color: rgba(253,230,138,0.45); font-size: 0.75rem;">
                        Nhập để kiểm tra điều kiện đơn tối thiểu
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── History ────────────────────────────────────────────────────── --}}
    <div id="historySection" class="mb-4 d-none">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="text-muted small fw-bold"><i class="fas fa-history me-1"></i>Tra cứu gần đây:</span>
            <button class="btn btn-link btn-sm text-muted p-0" onclick="clearHistory()" style="font-size: 0.75rem;">Xoá</button>
        </div>
        <div id="historyList" class="d-flex flex-wrap gap-2"></div>
    </div>

    {{-- ── Loading ─────────────────────────────────────────────────────── --}}
    <div id="loadingState" class="text-center py-5 d-none">
        <div class="spinner-border text-warning mb-3" style="width: 3rem; height: 3rem;"></div>
        <p class="text-muted">Đang tra cứu mã giảm giá...</p>
    </div>

    {{-- ── Result Area ─────────────────────────────────────────────────── --}}
    <div id="resultArea"></div>

</div>
@endsection

@section('extra_js')
<script>
    const CHECK_URL = '{{ route('staff.coupon.check.post') }}';
    const CSRF     = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let recentHistory = JSON.parse(localStorage.getItem('staff_coupon_history') || '[]');
    renderHistory();

    // ── Order Total Input: Validation & Auto-format ────────────────────
    const orderTotalInput = document.getElementById('orderTotalInput');
    const orderTotalError = document.getElementById('orderTotalError');
    const orderTotalErrorText = document.getElementById('orderTotalErrorText');

    // Chỉ cho phép nhập chữ số và dấu chấm/phẩy
    orderTotalInput.addEventListener('keypress', function(e) {
        const allowed = /[0-9.,]/;
        if (!allowed.test(e.key) && !['Backspace','Delete','Tab','Enter','ArrowLeft','ArrowRight'].includes(e.key)) {
            e.preventDefault();
        }
    });

    // Paste: lọc bỏ ký tự không hợp lệ
    orderTotalInput.addEventListener('paste', function(e) {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text');
        const digits = pasted.replace(/[^0-9]/g, '');
        if (digits) {
            this.value = formatCurrency(parseInt(digits, 10));
            validateOrderTotal();
        }
    });

    // Input: auto-format, chặn ký tự lạ
    orderTotalInput.addEventListener('input', function() {
        const raw = this.value.replace(/[^0-9]/g, '');
        if (raw === '') {
            this.value = '';
            clearOrderError();
            return;
        }
        const num = parseInt(raw, 10);
        this.value = formatCurrency(num);
        validateOrderTotal();
    });

    // Blur: validate lần cuối
    orderTotalInput.addEventListener('blur', function() {
        if (this.value.trim()) validateOrderTotal(true);
    });

    // Enter trong ô giá trị → trigger kiểm tra
    orderTotalInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') checkCoupon();
    });

    function getRawOrderTotal() {
        const raw = orderTotalInput.value.replace(/[^0-9]/g, '');
        return raw ? parseInt(raw, 10) : 0;
    }

    function validateOrderTotal(strict = false) {
        const val = getRawOrderTotal();
        const inp = orderTotalInput;

        if (orderTotalInput.value.trim() === '') {
            clearOrderError();
            return true;
        }

        if (val <= 0) {
            showOrderError('Giá trị đơn hàng phải lớn hơn 0₫');
            return false;
        }

        if (val > 100_000_000) {
            showOrderError('Giá trị đơn hàng không được vượt quá 100.000.000₫');
            return false;
        }

        // Hợp lệ
        clearOrderError();
        inp.classList.add('input-ok');
        return true;
    }

    function showOrderError(msg) {
        orderTotalErrorText.textContent = msg;
        orderTotalError.classList.add('show');
        orderTotalInput.classList.add('input-error');
        orderTotalInput.classList.remove('input-ok');
    }

    function clearOrderError() {
        orderTotalError.classList.remove('show');
        orderTotalInput.classList.remove('input-error', 'input-ok');
    }

    function formatCurrency(num) {
        return new Intl.NumberFormat('vi-VN').format(num);
    }

    // ── Coupon Code: Enter shortcut ────────────────────────────────────
    document.getElementById('couponCodeInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') checkCoupon();
    });

    async function checkCoupon(codeOverride) {
        const codeInput = document.getElementById('couponCodeInput');
        const code = (codeOverride || codeInput.value).trim().toUpperCase();

        if (!code) {
            codeInput.classList.add('is-invalid');
            codeInput.focus();
            setTimeout(() => codeInput.classList.remove('is-invalid'), 1500);
            return;
        }
        if (codeOverride) codeInput.value = code;

        // Validate order total trước khi submit
        if (orderTotalInput.value.trim() !== '' && !validateOrderTotal(true)) {
            orderTotalInput.focus();
            return;
        }

        const orderTotal = getRawOrderTotal();

        document.getElementById('loadingState').classList.remove('d-none');
        document.getElementById('resultArea').innerHTML = '';
        document.getElementById('btnCheck').disabled = true;

        try {
            const res = await fetch(CHECK_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ code, order_total: orderTotal }),
            });
            const data = await res.json();
            renderResult(data, code);
            saveHistory(code, data);
        } catch (err) {
            renderError();
        } finally {
            document.getElementById('loadingState').classList.add('d-none');
            document.getElementById('btnCheck').disabled = false;
        }
    }

    function renderResult(data, code) {
        const area = document.getElementById('resultArea');

        if (!data.found) {
            area.innerHTML = `
                <div class="result-card">
                    <div class="result-header verdict-notfound">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-white opacity-75 small mb-1">Mã tra cứu</div>
                                <div class="text-white fw-bold font-sora" style="font-size:1.4rem; letter-spacing:3px;">${code}</div>
                            </div>
                            <span class="verdict-badge invalid">
                                <i class="fas fa-times-circle"></i> KHÔNG TÌM THẤY
                            </span>
                        </div>
                    </div>
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-search-minus fa-3x mb-3 opacity-25"></i>
                        <p class="mb-0">${data.message}</p>
                    </div>
                </div>`;
            return;
        }

        const c = data.coupon;
        const checks = data.checks;

        // Verdict config
        const vConf = {
            valid:   { cls: 'verdict-valid',   bdg: 'valid',   icon: 'fa-check-circle',    label: 'HỢP LỆ' },
            warning: { cls: 'verdict-warning',  bdg: 'warning', icon: 'fa-exclamation-circle', label: 'CẦN KIỂM THÊM' },
            invalid: { cls: 'verdict-invalid',  bdg: 'invalid', icon: 'fa-times-circle',    label: 'KHÔNG HỢP LỆ' },
        };
        const v = vConf[data.verdict] || vConf.invalid;

        // Coupon chips
        const typeLabel = c.type === 'PERCENT'
            ? `Giảm ${c.value}%`
            : `Giảm ${fmtNum(c.value)}₫`;
        const maxLabel = c.max_discount_amount > 0
            ? `<span class="coupon-chip"><span class="chip-label">Tối đa</span><span class="chip-val">${fmtNum(c.max_discount_amount)}₫</span></span>` : '';
        const minLabel = c.min_order_value > 0
            ? `<span class="coupon-chip"><span class="chip-label">Đơn tối thiểu</span><span class="chip-val">${fmtNum(c.min_order_value)}₫</span></span>` : '';
        const qtyLabel = c.quantity > 0
            ? `<span class="coupon-chip"><span class="chip-label">Lượt dùng</span><span class="chip-val">${c.used_count}/${c.quantity}</span></span>`
            : `<span class="coupon-chip"><span class="chip-label">Lượt dùng</span><span class="chip-val text-success">Không giới hạn</span></span>`;

        // Checks
        let checksHtml = '';
        for (const [key, item] of Object.entries(checks)) {
            const pass = item.pass;
            let iconClass, iconEl;
            if (pass === true)       { iconClass = 'pass';  iconEl = 'fa-check'; }
            else if (pass === false)  { iconClass = 'fail';  iconEl = 'fa-times'; }
            else                      { iconClass = 'warn';  iconEl = 'fa-exclamation'; }

            checksHtml += `
                <div class="check-item">
                    <div class="check-icon ${iconClass}"><i class="fas ${iconEl}"></i></div>
                    <div>
                        <div class="check-label">${item.label}</div>
                        <div class="check-detail">${item.detail}</div>
                    </div>
                </div>`;
        }

        // Discount block
        let discountHtml = '';
        if (data.discount_amount !== null && data.discount_amount > 0) {
            discountHtml = `
                <div class="discount-highlight mt-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-dark" style="font-size:0.88rem;">
                                <i class="fas fa-tag text-success me-2"></i>Số tiền được giảm (ước tính)
                            </div>
                            <div class="text-muted small mt-1">Dựa trên giá trị đơn hàng đã nhập</div>
                        </div>
                        <div class="fs-3 fw-bold text-success font-sora">-${fmtNum(data.discount_amount)}₫</div>
                    </div>
                </div>`;
        }

        area.innerHTML = `
            <div class="result-card">
                <div class="result-header ${v.cls}">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <div class="text-white opacity-70 small mb-1">Mã giảm giá</div>
                            <div class="text-white fw-bold font-sora" style="font-size:1.6rem; letter-spacing:4px;">${c.code}</div>
                            <div class="mt-2" style="color:rgba(255,255,255,0.7); font-size:0.9rem;">
                                <strong style="color:#fff; font-size:1rem;">${typeLabel}</strong>
                                ${c.start_date ? `&nbsp;·&nbsp; ${c.start_date}` : ''}
                                ${c.end_date   ? `&nbsp;→&nbsp; ${c.end_date}`   : ''}
                            </div>
                        </div>
                        <span class="verdict-badge ${v.bdg}">
                            <i class="fas ${v.icon}"></i> ${v.label}
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    {{-- Info chips --}}
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        ${qtyLabel}
                        ${minLabel}
                        ${maxLabel}
                    </div>

                    {{-- Checklist --}}
                    <div class="fw-bold small text-uppercase text-muted mb-2" style="letter-spacing:.06em;">
                        <i class="fas fa-list-check me-1"></i>Kiểm tra điều kiện
                    </div>
                    <div>${checksHtml}</div>

                    ${discountHtml}
                </div>
            </div>`;
    }

    function renderError() {
        document.getElementById('resultArea').innerHTML = `
            <div class="alert alert-danger rounded-3">
                <i class="fas fa-wifi-slash me-2"></i>Lỗi kết nối tới máy chủ. Vui lòng thử lại.
            </div>`;
    }

    function fmtNum(n) {
        return new Intl.NumberFormat('vi-VN').format(Math.round(n));
    }

    // ── History ────────────────────────────────────────────────────────
    function saveHistory(code, data) {
        const entry = {
            code,
            verdict: data.found ? data.verdict : 'invalid',
            time: new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' }),
        };
        recentHistory = recentHistory.filter(h => h.code !== code);
        recentHistory.unshift(entry);
        if (recentHistory.length > 5) recentHistory = recentHistory.slice(0, 5);
        localStorage.setItem('staff_coupon_history', JSON.stringify(recentHistory));
        renderHistory();
    }

    function renderHistory() {
        const section = document.getElementById('historySection');
        const list    = document.getElementById('historyList');
        if (!recentHistory.length) { section.classList.add('d-none'); return; }
        section.classList.remove('d-none');
        list.innerHTML = recentHistory.map(h => `
            <span class="history-pill" onclick="checkCoupon('${h.code}')" title="Tra cứu lại lúc ${h.time}">
                <span class="dot ${h.verdict}"></span>
                ${h.code}
            </span>`).join('');
    }

    function clearHistory() {
        recentHistory = [];
        localStorage.removeItem('staff_coupon_history');
        renderHistory();
    }
</script>
@endsection
