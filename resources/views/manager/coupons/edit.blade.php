@extends('layouts.manager')

@section('title', 'Chỉnh Sửa Mã Giảm Giá - Manager')
@section('page_title', 'Chỉnh Sửa Mã Giảm Giá')

@section('content')
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-edit text-warning me-2"></i>Chỉnh Sửa Mã Giảm Giá: {{ $coupon->code }}</h5>
        <a href="{{ route('manager.coupons.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('manager.coupons.update', $coupon->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Mã Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control text-uppercase @error('code') is-invalid @enderror" value="{{ old('code', $coupon->code) }}" required>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Trạng Thái</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="ACTIVE" {{ old('status', $coupon->status) === 'ACTIVE' ? 'selected' : '' }}>Hoạt động (ACTIVE)</option>
                        <option value="INACTIVE" {{ old('status', $coupon->status) === 'INACTIVE' ? 'selected' : '' }}>Khóa (INACTIVE)</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Loại Giảm Giá <span class="text-danger">*</span></label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="fixed" {{ old('type', $coupon->type) === 'fixed' ? 'selected' : '' }}>Giảm số tiền cố định (VNĐ)</option>
                        <option value="percent" {{ old('type', $coupon->type) === 'percent' ? 'selected' : '' }}>Giảm theo phần trăm (%)</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Giá Trị Giảm <span class="text-danger">*</span></label>
                    <input type="number" step="any" name="value" class="form-control @error('value') is-invalid @enderror" value="{{ old('value', $coupon->value) }}" required>
                    @error('value')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Giá Trị Đơn Tối Thiểu (VNĐ)</label>
                    <input type="number" step="any" name="min_order_value" class="form-control @error('min_order_value') is-invalid @enderror" value="{{ old('min_order_value', $coupon->min_order_value) }}">
                    @error('min_order_value')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Giảm Tối Đa (VNĐ) - Dành cho loại %</label>
                    <input type="number" step="any" name="max_discount_amount" class="form-control @error('max_discount_amount') is-invalid @enderror" value="{{ old('max_discount_amount', $coupon->max_discount_amount) }}">
                    @error('max_discount_amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Số Lượng Giới Hạn <span class="text-danger">*</span></label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="unlimited_quantity" onchange="toggleUnlimited(this)" {{ $coupon->quantity == 0 || $coupon->quantity === null ? 'checked' : '' }}>
                        <label class="form-check-label" for="unlimited_quantity">
                            Vô hạn (Không giới hạn lượt dùng)
                        </label>
                    </div>
                    <input type="number" name="quantity" id="quantity_input" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', $coupon->quantity) }}" required min="0" {{ $coupon->quantity == 0 || $coupon->quantity === null ? 'readonly' : '' }}>
                    @error('quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Số lượt đã sử dụng</label>
                    <input type="text" class="form-control bg-light" value="{{ number_format($coupon->used_count) }}" readonly>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Thời Gian Bắt Đầu <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $coupon->start_date ? $coupon->start_date->format('Y-m-d\TH:i') : '') }}" required>
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Thời Gian Kết Thúc <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $coupon->end_date ? $coupon->end_date->format('Y-m-d\TH:i') : '') }}" required>
                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('manager.coupons.index') }}" class="btn btn-secondary">Hủy bỏ</a>
                <button type="submit" class="btn btn-warning px-4"><i class="fas fa-save me-1"></i> Cập nhật thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleUnlimited(checkbox) {
    const input = document.getElementById('quantity_input');
    if (checkbox.checked) {
        input.value = 0;
        input.readOnly = true;
    } else {
        input.readOnly = false;
        input.value = 100;
    }
}
</script>
@endsection
