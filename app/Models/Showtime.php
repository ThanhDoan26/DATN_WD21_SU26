<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Showtime Model
 * ========================================
 * Suất chiếu
 */
class Showtime extends Model
{
    use SoftDeletes;
    public const STATUS_SCHEDULED = 'SCHEDULED';
    public const STATUS_ONGOING = 'ONGOING';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_CANCELLED = 'CANCELLED';

    public const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_ONGOING,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = ['movie_id', 'room_id', 'start_time', 'end_time', 'status', 'surcharge'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'surcharge' => 'decimal:2',
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
     * Query scope: Chỉ các suất chiếu sắp tới đang mở bán
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SCHEDULED)
            ->where('start_time', '>=', now());
    }

    /**
     * Query scope: Chỉ các suất chiếu theo phòng
     */
    public function scopeForRoom(Builder $query, int $roomId): Builder
    {
        return $query->where('room_id', $roomId);
    }

    /**
     * Query scope: Chỉ các suất chiếu theo phim
     */
    public function scopeForMovie(Builder $query, int $movieId): Builder
    {
        return $query->where('movie_id', $movieId);
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isOngoing(): bool
    {
        return $this->status === self::STATUS_ONGOING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function durationMinutes(): ?int
    {
        if (! $this->start_time || ! $this->end_time) {
            return null;
        }

        return $this->end_time->diffInMinutes($this->start_time);
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

    /**
     * Thống kê: Lấy số lượng ghế đã được đặt (không tính các booking đã bị hủy)
     */
    public function getBookedSeatsCount(): int
    {
        return $this->bookings()
            ->where('status', '!=', 'Cancelled')
            ->join('booked_seats', 'bookings.id', '=', 'booked_seats.booking_id')
            ->count('booked_seats.id');
    }

    /**
     * Thống kê: Tính tỷ lệ lấp đầy phòng chiếu (%)
     */
    public function getOccupancyRate(): float
    {
        $totalSeats = $this->room?->total_seats ?? 0;
        if ($totalSeats == 0) {
            return 0;
        }
        
        $bookedCount = $this->getBookedSeatsCount();
        return round(($bookedCount / $totalSeats) * 100, 2);
    }
}
