<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * BookingAbuseLog Model
 * ========================================
 * Ghi nhận abuse events và booking restrictions.
 *
 * abuse_type:
 *   'warning'     → 3 expired holds trong window (chỉ log, không block)
 *   'restriction' → 5 expired holds trong window (block booking tạm thời)
 *
 * blocked_until:
 *   NULL      → cho warning (không block)
 *   timestamp → cho restriction (block đến thời điểm này)
 */
class BookingAbuseLog extends Model
{
    const TYPE_WARNING     = 'warning';
    const TYPE_RESTRICTION = 'restriction';

    protected $fillable = [
        'user_id',
        'abuse_type',
        'expired_count',
        'window_minutes',
        'ip_address',
        'details',
        'blocked_until',
    ];

    protected $casts = [
        'details'       => 'array',
        'blocked_until' => 'datetime',
        'expired_count' => 'integer',
        'window_minutes' => 'integer',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope: Restrictions đang active (blocked_until > now)
     */
    public function scopeActiveRestriction(Builder $query): Builder
    {
        return $query->where('abuse_type', self::TYPE_RESTRICTION)
                     ->where('blocked_until', '>', now());
    }

    /**
     * Scope: Theo user
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
