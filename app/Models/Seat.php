<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Seat Model
 * ========================================
 * Ghế ngồi
 * Composite key: (room_id, row_name, seat_number)
 */
class Seat extends Model
{
    const STATUS_AVAILABLE = 'AVAILABLE';
    const STATUS_BOOKED = 'BOOKED';
    const STATUS_BROKEN = 'BROKEN';

    protected $fillable = ['room_id', 'row_name', 'seat_number', 'seat_type', 'status'];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bookedSeats(): HasMany
    {
        return $this->hasMany(BookedSeat::class);
    }

    /**
     * Helper: Lấy code ghế (e.g., "A1", "F10")
     */
    public function getSeatCode(): string
    {
        return $this->row_name . $this->seat_number;
    }
}
