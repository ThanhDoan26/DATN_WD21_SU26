<?php

namespace App\Services;

use App\Models\Coupon;
use Carbon\Carbon;
use InvalidArgumentException;

class CouponDiscountService
{
    /**
     * Kiểm tra tính hợp lệ của Coupon đối với đơn hàng
     *
     * @param Coupon|null $coupon
     * @param float $subtotal Tổng tiền vé và combo trước khi giảm giá
     * @param int|null $userId ID người dùng (dùng để check hạn mức per-user)
     * @param int|null $ignoreBookingId ID booking đang cập nhật (tùy chọn)
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validateCoupon(?Coupon $coupon, float $subtotal, ?int $userId = null, ?int $ignoreBookingId = null): array
    {
        if (!$coupon) {
            return ['valid' => false, 'message' => 'Mã giảm giá không tồn tại.'];
        }

        if ($coupon->status !== 'ACTIVE') {
            return ['valid' => false, 'message' => 'Mã giảm giá hiện không hoạt động hoặc đã bị vô hiệu hóa.'];
        }

        $now = Carbon::now();

        // Kiểm tra thời gian bắt đầu
        if ($coupon->start_date && $now->lt($coupon->start_date)) {
            return ['valid' => false, 'message' => 'Mã giảm giá chưa đến thời gian áp dụng.'];
        }

        // Kiểm tra thời gian kết thúc
        if ($coupon->end_date && $now->gt($coupon->end_date)) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết hạn sử dụng.'];
        }

        // Kiểm tra số lượng lượt dùng
        if ($coupon->quantity > 0 && $coupon->used_count >= $coupon->quantity) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng.'];
        }

        // Kiểm tra giá trị đơn hàng tối thiểu
        if ($coupon->min_order_value > 0 && $subtotal < (float)$coupon->min_order_value) {
            return [
                'valid' => false,
                'message' => 'Đơn hàng chưa đạt giá trị tối thiểu ' . number_format($coupon->min_order_value) . 'đ để sử dụng mã này.'
            ];
        }

        // Kiểm tra mỗi khách hàng chỉ được dùng 1 lần cho các booking hợp lệ (Pending, PROCESSING, Paid, Used)
        if ($userId) {
            $hasUsedQuery = \Illuminate\Support\Facades\DB::table('bookings')
                ->where('user_id', $userId)
                ->where('coupon_id', $coupon->id)
                ->whereIn('status', ['Pending', 'PROCESSING', 'Paid', 'Used']);

            if ($ignoreBookingId) {
                $hasUsedQuery->where('id', '!=', $ignoreBookingId);
            }

            if ($hasUsedQuery->exists()) {
                return ['valid' => false, 'message' => 'Bạn đã sử dụng hoặc đang chờ thanh toán với mã giảm giá này.'];
            }
        }

        return ['valid' => true, 'message' => 'Mã giảm giá hợp lệ.'];
    }

    /**
     * Tính toán số tiền được giảm và tổng thanh toán cuối cùng
     *
     * @param Coupon $coupon
     * @param float $subtotal
     * @return array ['discount_amount' => float, 'final_total' => float]
     */
    public function calculateDiscount(Coupon $coupon, float $subtotal): array
    {
        if ($subtotal < 0) {
            throw new InvalidArgumentException('Tổng tiền đơn hàng không thể âm.');
        }

        $type = strtolower($coupon->type ?? '');
        $value = (float) ($coupon->value ?? 0);
        $discount = 0.0;

        if ($type === 'percent' || $type === 'percentage') {
            // Giảm theo %
            $discount = ($subtotal * $value) / 100.0;

            // Áp trần giảm tối đa nếu có
            if ($coupon->max_discount_amount > 0 && $discount > (float)$coupon->max_discount_amount) {
                $discount = (float)$coupon->max_discount_amount;
            }
        } elseif ($type === 'fixed' || $type === 'amount') {
            // Giảm số tiền cố định
            $discount = $value;
        }

        // Đảm bảo số tiền giảm không vượt quá tổng đơn hàng
        $discount = min($discount, $subtotal);
        $discount = max(0.0, round($discount, 2));

        $finalTotal = max(0.0, round($subtotal - $discount, 2));

        return [
            'discount_amount' => $discount,
            'final_total' => $finalTotal,
        ];
    }
}
