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
        'printed_at',
        'print_count',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'printed_at' => 'datetime',
        'print_count' => 'integer',
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
        $updated = \Illuminate\Support\Facades\DB::table('booked_seats')
            ->where('id', $this->id)
            ->where('status', 'PAID')
            ->update([
                'status' => 'USED',
                'checked_in_at' => now(),
                'updated_at' => now(),
            ]);

        if ($updated > 0) {
            $this->status = 'USED';
            return true;
        }
        
        return false;
    }

    /**
     * Helper: Lấy code ghế
     */
    public function getSeatCode(): string
    {
        return $this->seat?->getSeatCode() ?? 'N/A';
    }
}
