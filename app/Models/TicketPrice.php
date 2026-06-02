<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TicketPrice Model
 * ========================================
 * Giá vé (linh hoạt theo suất chiếu + loại ghế)
 */
class TicketPrice extends Model
{
    protected $fillable = ['showtime_id', 'seat_type', 'price', 'status'];

    public function showtime(): BelongsTo
    {
        return $this->belongsTo(Showtime::class);
    }
}
