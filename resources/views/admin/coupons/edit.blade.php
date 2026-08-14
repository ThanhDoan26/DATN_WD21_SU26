@extends('admin.layouts.app')

@section('title', 'Sửa Mã Giảm Giá')
@section('page_title', 'Sửa Mã Giảm Giá: ' . $coupon->code)

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Thông tin mã giảm giá</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Mã code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $coupon->code) }}" required>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Trạng thái</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="ACTIVE" {{ old('status', $coupon->status) === 'ACTIVE' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="INACTIVE" {{ old('status', $coupon->status) === 'INACTIVE' ? 'selected' : '' }}>Khóa</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Loại giảm giá <span class="text-danger">*</span></label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="fixed" {{ old('type', $coupon->type) === 'fixed' ? 'selected' : '' }}>Giảm số tiền cố định (VNĐ)</option>
                        <option value="percent" {{ old('type', $coupon->type) === 'percent' ? 'selected' : '' }}>Giảm theo phần trăm (%)</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Giá trị giảm <span class="text-danger">*</span></label>
                    <input type="text" name="value" id="value" class="form-control format-number @error('value') is-invalid @enderror" value="{{ old('value', number_format(floatval($coupon->value), 0, '', '')) }}" required>
                    @error('value')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Nhập số tiền hoặc số % tương ứng với loại giảm giá.</small>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Giá trị đơn tối thiểu (VNĐ)</label>
                    <input type="text" name="min_order_value" id="min_order_value" class="form-control format-number @error('min_order_value') is-invalid @enderror" value="{{ old('min_order_value', number_format(floatval($coupon->min_order_value), 0, '', '')) }}">
                    @error('min_order_value')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Giảm tối đa (VNĐ) - Dành cho loại %</label>
                    <input type="text" name="max_discount_amount" id="max_discount_amount" class="form-control format-number @error('max_discount_amount') is-invalid @enderror" value="{{ old('max_discount_amount', $coupon->max_discount_amount ? number_format(floatval($coupon->max_discount_amount), 0, '', '') : '') }}">
                    @error('max_discount_amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Bỏ trống nếu không giới hạn.</small>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Số lượng giới hạn <span class="text-danger">*</span></label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="unlimited_quantity" name="unlimited_quantity" value="1" {{ old('quantity', $coupon->quantity) == 0 ? 'checked' : '' }}>
                        <label class="form-check-label" for="unlimited_quantity">
                            Vô hạn (Không giới hạn lượt sử dụng)
                        </label>
                    </div>
                    <input type="number" name="quantity" id="quantity_input" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', $coupon->quantity) }}" required min="0">
                    @error('quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Đã sử dụng</label>
                    <input type="number" class="form-control" value="{{ $coupon->used_count }}" readonly disabled>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Thời gian bắt đầu</label>
                    <input type="datetime-local" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $coupon->start_date ? $coupon->start_date->format('Y-m-d\TH:i') : '') }}">
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Thời gian kết thúc</label>
                    <input type="datetime-local" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $coupon->end_date ? $coupon->end_date->format('Y-m-d\TH:i') : '') }}">
                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">Hủy bỏ</a>
                <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Cập Nhật Mã Giảm Giá</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const formatNumber = (num) => {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    };

    const cleanNumber = (str) => {
        return str.replace(/\./g, '');
    };

    const inputs = document.querySelectorAll('.format-number');

    inputs.forEach(input => {
        // Format on load if there's a value
        if (input.value) {
            let val = cleanNumber(input.value);
            if (!isNaN(val) && val.length > 0) {
                input.value = formatNumber(val);
            }
        }

        // Format on input
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d]/g, '');
            if (value !== '') {
                e.target.value = formatNumber(value);
            } else {
                e.target.value = '';
            }
        });
    });

    // Clean dots before submit
    const forms = document.querySelectorAll('.card-body form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            inputs.forEach(input => {
                if (input.value) {
                    input.value = cleanNumber(input.value);
                }
            });
            if (document.getElementById('unlimited_quantity').checked) {
                document.getElementById('quantity_input').value = 0;
            }
        });
    });

    const unlimitedCheckbox = document.getElementById('unlimited_quantity');
    const quantityInput = document.getElementById('quantity_input');
    
    function toggleQuantity() {
        if (unlimitedCheckbox.checked) {
            quantityInput.style.display = 'none';
            if (quantityInput.value != 0) {
                quantityInput.setAttribute('data-old-value', quantityInput.value);
            }
            quantityInput.value = 0;
        } else {
            quantityInput.style.display = 'block';
            if (quantityInput.value == 0) {
                quantityInput.value = quantityInput.getAttribute('data-old-value') || 100;
            }
        }
    }
    
    unlimitedCheckbox.addEventListener('change', toggleQuantity);
    toggleQuantity();
});
</script>
@endsection
