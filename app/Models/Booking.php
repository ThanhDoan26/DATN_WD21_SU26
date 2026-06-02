<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Booking Model
 * ========================================
 * Đơn hàng mua vé
 * Status: Pending, Paid, Cancelled, Used
 */
class Booking extends Model
{
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
        'notes',
    ];

    protected $casts = [
        'booking_time' => 'datetime',
        'payment_time' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function showtime(): BelongsTo
    {
        return $this->belongsTo(Showtime::class);
    }

    public function bookedSeats(): HasMany
    {
        return $this->hasMany(BookedSeat::class);
    }

    /**
     * Helper: Kiểm tra booking đã thanh toán
     */
    public function isPaid(): bool
    {
        return $this->status === 'Paid';
    }

    /**
     * Helper: Kiểm tra booking bị hủy
     */
    public function isCancelled(): bool
    {
        return $this->status === 'Cancelled';
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
