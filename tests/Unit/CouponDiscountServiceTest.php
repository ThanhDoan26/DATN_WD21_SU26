<?php

use App\Models\Coupon;
use App\Services\CouponDiscountService;
use Carbon\Carbon;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->service = new CouponDiscountService();
});

// ==========================================
// 1. KIỂM THỬ TÍNH HỢP LỆ (VALIDATION)
// ==========================================

it('rejects validation when coupon is null', function () {
    $result = $this->service->validateCoupon(null, 150000);

    expect($result['valid'])->toBeFalse()
        ->and($result['message'])->toContain('không tồn tại');
});

it('rejects validation when coupon is inactive', function () {
    $coupon = new Coupon(['status' => 'INACTIVE']);
    $result = $this->service->validateCoupon($coupon, 150000);

    expect($result['valid'])->toBeFalse()
        ->and($result['message'])->toContain('không hoạt động');
});

it('rejects validation when current date is before start_date', function () {
    $coupon = new Coupon([
        'status' => 'ACTIVE',
        'start_date' => Carbon::now()->addDays(2),
    ]);

    $result = $this->service->validateCoupon($coupon, 150000);

    expect($result['valid'])->toBeFalse()
        ->and($result['message'])->toContain('chưa đến thời gian');
});

it('rejects validation when coupon has expired', function () {
    $coupon = new Coupon([
        'status' => 'ACTIVE',
        'end_date' => Carbon::now()->subMinute(),
    ]);

    $result = $this->service->validateCoupon($coupon, 150000);

    expect($result['valid'])->toBeFalse()
        ->and($result['message'])->toContain('đã hết hạn');
});

it('rejects validation when usage limit is reached', function () {
    $coupon = new Coupon([
        'status' => 'ACTIVE',
        'quantity' => 50,
        'used_count' => 50,
    ]);

    $result = $this->service->validateCoupon($coupon, 150000);

    expect($result['valid'])->toBeFalse()
        ->and($result['message'])->toContain('hết lượt sử dụng');
});

it('rejects validation when subtotal does not reach min_order_value', function () {
    $coupon = new Coupon([
        'status' => 'ACTIVE',
        'min_order_value' => 200000,
    ]);

    $result = $this->service->validateCoupon($coupon, 150000);

    expect($result['valid'])->toBeFalse()
        ->and($result['message'])->toContain('chưa đạt giá trị tối thiểu');
});

it('approves validation when all conditions are satisfied', function () {
    $coupon = new Coupon([
        'status' => 'ACTIVE',
        'start_date' => Carbon::now()->subDay(),
        'end_date' => Carbon::now()->addDays(5),
        'quantity' => 100,
        'used_count' => 10,
        'min_order_value' => 100000,
    ]);

    $result = $this->service->validateCoupon($coupon, 150000);

    expect($result['valid'])->toBeTrue()
        ->and($result['message'])->toContain('hợp lệ');
});

// ==========================================
// 2. KIỂM THỬ TÍNH TOÁN (CALCULATION)
// ==========================================

it('calculates percentage discount accurately', function () {
    $coupon = new Coupon([
        'type' => 'percent',
        'value' => 10, // 10%
        'max_discount_amount' => 0,
    ]);

    $result = $this->service->calculateDiscount($coupon, 200000);

    expect($result['discount_amount'])->toEqual(20000.0)
        ->and($result['final_total'])->toEqual(180000.0);
});

it('caps percentage discount at max_discount_amount', function () {
    $coupon = new Coupon([
        'type' => 'percent',
        'value' => 50, // 50% của 200.000 là 100.000 nhưng trần là 40.000
        'max_discount_amount' => 40000,
    ]);

    $result = $this->service->calculateDiscount($coupon, 200000);

    expect($result['discount_amount'])->toEqual(40000.0)
        ->and($result['final_total'])->toEqual(160000.0);
});

it('calculates fixed amount discount correctly', function () {
    $coupon = new Coupon([
        'type' => 'fixed',
        'value' => 50000,
    ]);

    $result = $this->service->calculateDiscount($coupon, 120000);

    expect($result['discount_amount'])->toEqual(50000.0)
        ->and($result['final_total'])->toEqual(70000.0);
});

it('does not allow discount to exceed subtotal', function () {
    $coupon = new Coupon([
        'type' => 'fixed',
        'value' => 300000,
    ]);

    $result = $this->service->calculateDiscount($coupon, 200000);

    expect($result['discount_amount'])->toEqual(200000.0)
        ->and($result['final_total'])->toEqual(0.0);
});

it('throws exception if subtotal is negative', function () {
    $coupon = new Coupon(['type' => 'fixed', 'value' => 50000]);

    $this->service->calculateDiscount($coupon, -10000);
})->throws(InvalidArgumentException::class);
