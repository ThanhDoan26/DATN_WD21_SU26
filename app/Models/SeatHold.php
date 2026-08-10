<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * SeatHold Model
 * ========================================
 * TRACKING ONLY — ghi nhận hold events cho abuse detection.
 *
 * KHÔNG phải source of truth cho seat availability.
 * Source of truth: Booking (status=Pending) + BookedSeat (status=RESERVED).
 *
 * Status transitions:
 *   active    → completed  (payment successful)
 *   active    → expired    (hold hết hạn, no payment)
 *   active    → released   (user cancel hoặc auto-cancel khi tạo booking mới)
 */
class SeatHold extends Model
{
    const STATUS_ACTIVE    = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_EXPIRED   = 'expired';
    const STATUS_RELEASED  = 'released';

    protected $fillable = [
        'user_id',
        'showtime_id',
        'booking_id',
        'seat_count',
        'status',
        'ip_address',
        'held_at',
        'expires_at',
        'released_at',
    ];

    protected $casts = [
        'held_at'     => 'datetime',
        'expires_at'  => 'datetime',
        'released_at' => 'datetime',
        'seat_count'  => 'integer',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function showtime(): BelongsTo
    {
        return $this->belongsTo(Showtime::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope: Chỉ các hold đang active (chưa hết hạn)
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->where('expires_at', '>', now());
    }

    /**
     * Scope: Các hold đã expired
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    /**
     * Scope: Các hold active nhưng đã quá hạn (cần xử lý)
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->where('expires_at', '<=', now());
    }

    /**
     * Scope: Theo user
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
