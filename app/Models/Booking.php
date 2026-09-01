<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Booking Model
 * ========================================
 * Đơn hàng mua vé
 * Status: pending, paid, cancelled, expired, used
 */
class Booking extends Model
{
    public const STATUS_PENDING    = 'pending';
    public const STATUS_PAID       = 'paid';
    public const STATUS_CANCELLED  = 'cancelled';
    public const STATUS_EXPIRED    = 'expired';
    public const STATUS_USED       = 'used';
    public const STATUS_PROCESSING = 'processing';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_PAID,
        self::STATUS_CANCELLED,
        self::STATUS_EXPIRED,
        self::STATUS_USED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING   => 'Chờ thanh toán',
        self::STATUS_PAID      => 'Đã thanh toán',
        self::STATUS_CANCELLED => 'Đã hủy',
        self::STATUS_EXPIRED   => 'Hết hạn',
        self::STATUS_USED      => 'Đã sử dụng',
    ];

    protected $fillable = [
        'user_id',
        'showtime_id',
        'total_price',
        'status',
        'payment_method',
        'booking_time',
        'payment_time',
        'cancelled_at',
        'cancellation_reason',
        'booking_code',
        'ticket_token',
        'ticket_email_sent_at',
        'notes',
    ];

    protected static function booted()
    {
        static::creating(function ($booking) {
            if ($booking->isPaid() && empty($booking->ticket_token)) {
                $booking->ticket_token = (string) Str::uuid();
            }
        });

        static::updating(function ($booking) {
            if ($booking->isPaid() && empty($booking->ticket_token)) {
                $booking->ticket_token = (string) Str::uuid();
            }
        });
    }

    /**
     * Mutator: Luôn chuẩn hóa trạng thái về chữ thường (lowercase)
     */
    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = is_string($value) ? strtolower(trim($value)) : $value;
    }

    /**
     * Accessor: Luôn trả về trạng thái dạng chữ thường (lowercase)
     */
    public function getStatusAttribute($value): ?string
    {
        return is_string($value) ? strtolower(trim($value)) : $value;
    }

    protected $casts = [
        'booking_time' => 'datetime',
        'payment_time' => 'datetime',
        'cancelled_at' => 'datetime',
        'ticket_email_sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class)->withTrashed();
    }

    public function showtime(): BelongsTo
    {
        return $this->belongsTo(Showtime::class)->withTrashed();
    }

    public function bookedSeats(): HasMany
    {
        return $this->hasMany(BookedSeat::class);
    }

    public function combos()
    {
        return $this->belongsToMany(Combo::class, 'booking_combos')->withPivot('quantity', 'price')->withTimestamps();
    }

    public function comboReviews(): HasMany
    {
        return $this->hasMany(ComboReview::class);
    }

    /**
     * Helper: Kiểm tra booking đã thanh toán
     */
    public function isPaid(): bool
    {
        return strtolower($this->status ?? '') === self::STATUS_PAID;
    }

    /**
     * Helper: Kiểm tra booking đang chờ thanh toán
     */
    public function isPending(): bool
    {
        return strtolower($this->status ?? '') === self::STATUS_PENDING;
    }

    /**
     * Helper: Kiểm tra booking bị hủy
     */
    public function isCancelled(): bool
    {
        return strtolower($this->status ?? '') === self::STATUS_CANCELLED;
    }

    /**
     * Helper: Kiểm tra booking đã hết hạn
     */
    public function isExpired(): bool
    {
        return strtolower($this->status ?? '') === self::STATUS_EXPIRED;
    }

    /**
     * Helper: Kiểm tra booking đã sử dụng vé
     */
    public function isUsed(): bool
    {
        return strtolower($this->status ?? '') === self::STATUS_USED;
    }

    /**
     * Helper: Lấy nhãn tiếng Việt của trạng thái
     */
    public function getStatusLabelAttribute(): string
    {
        $st = strtolower($this->status ?? '');
        return self::STATUS_LABELS[$st] ?? ucfirst($st);
    }

    /**
     * Helper: Lấy chi tiết các ghế đã đặt
     */
    public function getSeatsInfo(): array
    {
        return $this->bookedSeats()
            ->join('seats', 'booked_seats.seat_id', '=', 'seats.id')
            ->select('seats.row_name', 'seats.seat_number', 'seats.seat_type', 'booked_seats.price_at_booking')
            ->get()
            ->map(fn($seat) => [
                'code' => $seat->row_name . $seat->seat_number,
                'type' => $seat->seat_type,
                'price' => $seat->price_at_booking,
            ])
            ->toArray();
    }
}
