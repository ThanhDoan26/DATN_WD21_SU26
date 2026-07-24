<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_value',
        'max_discount_amount',
        'quantity',
        'used_count',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'value' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
    ];

    /**
     * Kiểm tra xem mã giảm giá có hợp lệ cho đơn hàng hiện tại không.
     *
     * @param float $orderTotal Giá trị đơn hàng tạm tính
     * @param int|null $userId ID của người dùng (tùy chọn)
     * @return array ['valid' => bool, 'message' => string]
     */
    public function isValid($orderTotal, $userId = null)
    {
        if ($this->status !== 'ACTIVE') {
            return ['valid' => false, 'message' => 'Mã giảm giá không hoạt động hoặc đã bị khoá.'];
        }

        $now = now();
        if ($this->start_date && $now->lt($this->start_date)) {
            return ['valid' => false, 'message' => 'Mã giảm giá chưa đến thời gian sử dụng.'];
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết hạn sử dụng.'];
        }

        if ($this->quantity > 0 && $this->used_count >= $this->quantity) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng.'];
        }

        if ($orderTotal < $this->min_order_value) {
            return ['valid' => false, 'message' => 'Giá trị đơn hàng chưa đạt mức tối thiểu (' . number_format($this->min_order_value, 0, ',', '.') . ' VNĐ) để sử dụng mã này.'];
        }

        // Kiểm tra xem User này đã sử dụng mã này chưa (nếu có truyền userId)
        if ($userId) {
            $hasUsed = \Illuminate\Support\Facades\DB::table('bookings')
                ->where('user_id', $userId)
                ->where('coupon_id', $this->id)
                ->whereIn('status', ['Pending', 'Paid', 'Used'])
                ->exists();

            if ($hasUsed) {
                return ['valid' => false, 'message' => 'Bạn đã sử dụng hoặc đang chờ thanh toán với mã giảm giá này.'];
            }
        }

        return ['valid' => true, 'message' => 'Mã giảm giá hợp lệ.'];
    }

    /**
     * Tính toán số tiền được giảm
     *
     * @param float $orderTotal Giá trị đơn hàng tạm tính
     * @return float Số tiền được giảm
     */
    public function calculateDiscount($orderTotal)
    {
        if ($this->type === 'percent') {
            $discount = ($orderTotal * $this->value) / 100;
            if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
                return $this->max_discount_amount;
            }
            return $discount;
        }

        return min($this->value, $orderTotal); // Giảm tối đa bằng giá trị đơn hàng
    }
}
