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
     * Scope lọc danh sách Coupon hợp lệ cho khách hàng tại màn hình Checkout ngay tại Query CSDL
     */
    public function scopeValidForCheckout($query)
    {
        $now = now();
        return $query->where('status', 'ACTIVE')
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $now);
            })
            ->where(function ($q) {
                $q->where('quantity', 0)
                  ->orWhereNull('quantity')
                  ->orWhereColumn('used_count', '<', 'quantity');
            });
    }

    /**
     * Tự động chuyển type về chữ thường khi lấy dữ liệu để tránh lỗi case-sensitive
     */
    public function getTypeAttribute($value)
    {
        return $value ? strtolower($value) : $value;
    }

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
            return ['valid' => false, 'message' => 'Mã giảm giá cảu bạn không thể hoạt động hoặc có thể bị khoá'];
        }

        $now = now();
        if ($this->start_date && $now->lt($this->start_date)) {
            return ['valid' => false, 'message' => 'Mã giam giá của bạn vẫn chưa đến thời gian sử dụng!'];
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return ['valid' => false, 'message' => 'Mã giảm giá của bạn đã hết hạn sử dụng!'];
        }

        if ($this->quantity > 0 && $this->used_count >= $this->quantity) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng.'];
        }

        if ($orderTotal < $this->min_order_value) {
            return ['valid' => false, 'message' => 'Gia trị đơn hàng cảu bạn chưa đạt mức tối thiểu (' . number_format($this->min_order_value, 0, ',', '.') . ' VNĐ) để sử dụng mã này.'];
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

        return ['valid' => true, 'message' => 'Mã giảm của bạn hợp lệ!'];
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

    /**
     * Scope a query to only include active and valid coupons.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActiveAndValid($query)
    {
        $now = now();
        return $query->where('status', 'ACTIVE')
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $now);
            })
            ->where(function ($q) {
                $q->where('quantity', '<=', 0)
                  ->orWhereColumn('used_count', '<', 'quantity');
            });
    }

    /**
     * Scope sắp xếp thứ tự danh sách Coupon:
     * 1. Ưu tiên mã hợp lệ & còn hoạt động (ACTIVE, chưa hết hạn, còn lượt) lên TRÊN CÙNG (0).
     * 2. Mã đã hết hạn, bị khóa (INACTIVE) hoặc hết số lượng bị đẩy xuống DƯỚI (1).
     * 3. Thứ tự phụ: Ngày hết hạn (end_date) tăng dần ASC (sắp hết hạn đứng trước, NULL đứng sau).
     * 4. Id giảm dần DESC làm fallback.
     */
    public function scopeOrderByAvailabilityAndExpiration($query)
    {
        $now = now()->toDateTimeString();

        return $query->orderByRaw("
            CASE 
                WHEN status = 'ACTIVE' 
                     AND (end_date IS NULL OR end_date >= ?) 
                     AND (quantity = 0 OR quantity IS NULL OR used_count < quantity) 
                THEN 0 
                ELSE 1 
            END ASC
        ", [$now])
        ->orderByRaw("CASE WHEN end_date IS NULL THEN 1 ELSE 0 END ASC")
        ->orderBy('end_date', 'asc')
        ->orderBy('id', 'desc');
    }
}
