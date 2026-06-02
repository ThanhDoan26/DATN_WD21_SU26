<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Showtime Model
 * ========================================
 * Suất chiếu
 */
class Showtime extends Model
{
    protected $fillable = ['movie_id', 'room_id', 'start_time', 'end_time', 'status'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function ticketPrices(): HasMany
    {
        return $this->hasMany(TicketPrice::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Helper: Lấy cinema qua room
     */
    public function getCinema(): ?Cinema
    {
        return $this->room?->cinema;
    }

    /**
     * Helper: Lấy giá vé theo loại ghế
     */
    public function getPriceByType(string $seatType): ?float
    {
        return $this->ticketPrices()
            ->where('seat_type', $seatType)
            ->where('status', 'ACTIVE')
            ->value('price');
    }
}
