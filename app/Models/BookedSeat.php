<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BookedSeat Model
 * ========================================
 * Chi tiết vé (ghế đã đặt trong booking)
 * Status: RESERVED, PAID, USED, CANCELLED
 */
class BookedSeat extends Model
{
    protected $fillable = [
        'booking_id',
        'seat_id',
        'price_at_booking',
        'status',
        'qr_code',
        'checked_in_at',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }

    /**
     * Helper: Checkin vé (mark as USED)
     */
    public function checkin(): bool
    {
        return $this->update([
            'status' => 'USED',
            'checked_in_at' => now(),
        ]);
    }

    /**
     * Helper: Lấy code ghế
     */
    public function getSeatCode(): string
    {
        return $this->seat?->getSeatCode() ?? 'N/A';
    }
}
