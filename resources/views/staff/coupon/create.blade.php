@extends('layouts.staff')

@section('title', 'Thêm Mã Giảm Giá')
@section('page_title', 'Thêm Mã Giảm Giá Mới')

@section('extra_css')
<style>
    .form-card {
        background: var(--bg-surface);
        border-radius: 18px;
        border: 1px solid var(--border-light);
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    .form-card-header {
        background: linear-gradient(135deg, #1c1408 0%, #0f0b04 100%);
        padding: 18px 28px;
    }
    .form-section-title {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--text-muted);
        padding: 16px 0 8px;
        border-bottom: 1px solid var(--border-light);
        margin-bottom: 20px;
    }
    .form-label { font-weight: 600; font-size: 0.875rem; color: var(--text-ink); }
    .type-toggle { display: flex; gap: 8px; }
    .type-btn {
        flex: 1; padding: 10px; border: 2px solid var(--border-light);
        border-radius: 10px; background: var(--bg-base); cursor: pointer;
        text-align: center; font-weight: 600; font-size: 0.85rem;
        transition: all 0.2s; color: var(--text-muted);
    }
    .type-btn.active {
        border-color: #f59e0b; background: rgba(245,158,11,0.08);
        color: #d97706;
    }
    .type-btn:hover { border-color: #f59e0b; color: #d97706; }
    .auto-code-btn {
        cursor: pointer; font-size: 0.75rem; color: #d97706;
        text-decoration: underline;
    }
    .auto-code-btn:hover { color: #b45309; }
</style>
@endsection

@section('content')
<div class="container-fluid p-4" style="max-width: 820px; margin: 0 auto;">

    {{-- Breadcrumb --}}
    <div class="d-flex align-items-center gap-2 mb-4 text-muted small">
        <a href="{{ route('staff.coupons.index') }}" class="text-decoration-none text-muted">
            <i class="fas fa-tag me-1"></i>Mã Giảm Giá
        </a>
        <i class="fas fa-chevron-right" style="font-size:0.65rem;"></i>
        <span class="text-dark fw-bold">Thêm mới</span>
    </div>

    <div class="form-card">
        <div class="form-card-header">
            <h5 class="text-white fw-bold font-sora mb-0">
                <i class="fas fa-plus-circle text-warning me-2"></i>Thêm Mã Giảm Giá Mới
            </h5>
        </div>

        <div class="p-4">
            <form action="{{ route('staff.coupons.store') }}" method="POST" id="couponForm">
                @csrf

                {{-- Section 1: Thông tin cơ bản --}}
                <div class="form-section-title"><i class="fas fa-info-circle me-2 text-warning"></i>Thông tin cơ bản</div>

                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <label class="form-label">Mã code <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-tag text-warning"></i></span>
                            <input type="text" name="code" id="codeInput"
                                   class="form-control text-uppercase fw-bold @error('code') is-invalid @enderror"
                                   value="{{ old('code', $autoCode ?? '') }}" required
                                   placeholder="VD: SUMMER2026" style="letter-spacing:2px;">
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <small class="text-muted">
                            <span class="auto-code-btn" onclick="generateCode()">
                                <i class="fas fa-random me-1"></i>Tạo mã ngẫu nhiên
                            </span>
                        </small>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="ACTIVE"   {{ old('status','ACTIVE') === 'ACTIVE'   ? 'selected' : '' }}>✅ Hoạt động</option>
                            <option value="INACTIVE" {{ old('status') === 'INACTIVE' ? 'selected' : '' }}>🔒 Bị khoá</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Section 2: Loại và giá trị --}}
                <div class="form-section-title"><i class="fas fa-percentage me-2 text-warning"></i>Loại & Giá trị giảm</div>

                <div class="mb-3">
                    <label class="form-label">Loại giảm giá <span class="text-danger">*</span></label>
                    <div class="type-toggle">
                        <div class="type-btn {{ old('type','fixed') === 'fixed' ? 'active' : '' }}" onclick="setType('fixed')">
                            <i class="fas fa-coins d-block mb-1"></i>Giảm tiền cố định (₫)
                        </div>
                        <div class="type-btn {{ old('type') === 'percent' ? 'active' : '' }}" onclick="setType('percent')">
                            <i class="fas fa-percent d-block mb-1"></i>Giảm theo phần trăm (%)
                        </div>
                    </div>
                    <input type="hidden" name="type" id="typeInput" value="{{ old('type','fixed') }}">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Giá trị giảm <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="value" id="valueInput"
                                   class="form-control format-number @error('value') is-invalid @enderror"
                                   value="{{ old('value') }}" required placeholder="0">
                            <span class="input-group-text" id="valueUnit">₫</span>
                            @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <small class="text-muted">Nhập số tiền hoặc số % tuỳ loại đã chọn.</small>
                    </div>
                    <div class="col-md-6" id="maxDiscountWrap">
                        <label class="form-label">Giảm tối đa (₫) <small class="text-muted fw-normal">- Chỉ cho loại %</small></label>
                        <div class="input-group">
                            <input type="text" name="max_discount_amount" id="max_discount_amount"
                                   class="form-control format-number @error('max_discount_amount') is-invalid @enderror"
                                   value="{{ old('max_discount_amount') }}" placeholder="Bỏ trống = không giới hạn">
                            <span class="input-group-text">₫</span>
                            @error('max_discount_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Section 3: Điều kiện --}}
                <div class="form-section-title"><i class="fas fa-sliders-h me-2 text-warning"></i>Điều kiện áp dụng</div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Giá trị đơn tối thiểu (₫)</label>
                        <div class="input-group">
                            <input type="text" name="min_order_value" id="min_order_value"
                                   class="form-control format-number @error('min_order_value') is-invalid @enderror"
                                   value="{{ old('min_order_value','0') }}">
                            <span class="input-group-text">₫</span>
                            @error('min_order_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Số lượt sử dụng <span class="text-danger">*</span></label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="unlimited_quantity"
                                   {{ old('quantity') == 0 && old('quantity') !== null ? 'checked' : '' }}>
                            <label class="form-check-label small" for="unlimited_quantity">
                                Vô hạn (không giới hạn lượt)
                            </label>
                        </div>
                        <input type="number" name="quantity" id="quantity_input"
                               class="form-control @error('quantity') is-invalid @enderror"
                               value="{{ old('quantity', 100) }}" required min="0" placeholder="Số lượt">
                        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Section 4: Thời gian --}}
                <div class="form-section-title"><i class="fas fa-calendar-alt me-2 text-warning"></i>Thời gian hiệu lực</div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Bắt đầu <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="start_date"
                               class="form-control @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date') }}" required>
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kết thúc <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="end_date"
                               class="form-control @error('end_date') is-invalid @enderror"
                               value="{{ old('end_date') }}" required>
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('staff.coupons.index') }}" class="btn btn-outline-secondary px-4 rounded-3">
                        <i class="fas fa-times me-1"></i>Huỷ
                    </a>
                    <button type="submit" class="btn btn-warning px-5 rounded-3 fw-bold text-dark">
                        <i class="fas fa-save me-2"></i>Lưu Mã Giảm Giá
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('extra_js')
<script>
@include('staff.coupon._form_js')
</script>
@endsection
