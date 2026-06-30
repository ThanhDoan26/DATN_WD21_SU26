<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Room Model
 * ========================================
 * Phòng chiếu
 */
class Room extends Model
{
    use SoftDeletes;

    protected $fillable = ['cinema_id', 'name', 'format', 'total_seats', 'status'];

    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class);
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class);
    }

    /**
     * Kiểm tra phòng có suất chiếu hợp lệ (SCHEDULED, ONGOING)
     */
    public function hasActiveShowtimes(): bool
    {
        return $this->showtimes()
            ->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
            ->exists();
    }

    /**
     * Lấy số lượng suất chiếu hợp lệ
     */
    public function getActiveShowtimesCount(): int
    {
        return $this->showtimes()
            ->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
            ->count();
    }

    /**
     * Lấy danh sách suất chiếu hợp lệ
     */
    public function getActiveShowtimes()
    {
        return $this->showtimes()
            ->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
            ->with('movie')
            ->orderBy('start_time')
            ->get();
    }
}