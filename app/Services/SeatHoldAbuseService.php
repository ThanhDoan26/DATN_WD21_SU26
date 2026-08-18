<?php

namespace App\Services;

use App\Models\SeatHold;
use App\Models\BookingAbuseLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ========================================
 * SeatHoldAbuseService
 * ========================================
 * Service xử lý TRACKING hold events và phát hiện abuse.
 *
 * 🚨 IMPORTANT:
 * - Service này KHÔNG tham gia vào seat availability / seat locking.
 * - Source of truth cho seat reservation: Booking + BookedSeat.
 * - seat_holds chỉ là tracking layer cho abuse detection.
 *
 * Gọi recordHold() SAU khi DB::transaction thành công trong BookingService.
 * Nếu transaction rollback → không có tracking record → đúng behavior.
 */
class SeatHoldAbuseService
{
    /**
     * Ghi nhận hold event mới (TRACKING ONLY).
     * PHẢI được gọi SAU khi BookingService::createBooking() transaction thành công.
     *
     * @param int $userId
     * @param int $showtimeId
     * @param int $bookingId
     * @param int $seatCount
     * @param string|null $ipAddress
     * @param \Carbon\Carbon|null $customExpiresAt
     * @return SeatHold
     */
    public function recordHold(
        int $userId,
        int $showtimeId,
        int $bookingId,
        int $seatCount,
        ?string $ipAddress = null,
        ?\Carbon\Carbon $customExpiresAt = null
    ): SeatHold {
        $durationMinutes = (int) config('booking.seat_hold.duration_minutes', 10);

        $hold = SeatHold::create([
            'user_id'     => $userId,
            'showtime_id' => $showtimeId,
            'booking_id'  => $bookingId,
            'seat_count'  => $seatCount,
            'status'      => SeatHold::STATUS_ACTIVE,
            'ip_address'  => $ipAddress,
            'held_at'     => now(),
            'expires_at'  => $customExpiresAt ?? now()->addMinutes($durationMinutes),
        ]);

        Log::info('seat_hold_created', [
            'user_id'     => $userId,
            'showtime_id' => $showtimeId,
            'booking_id'  => $bookingId,
            'seat_count'  => $seatCount,
            'expires_at'  => $hold->expires_at->toDateTimeString(),
        ]);

        return $hold;
    }

    /**
     * Đếm tổng số ghế đang được hold (active) bởi user cho một suất chiếu.
     * Đây là SOFT CHECK — chạy ngoài transaction, không lock seat_holds.
     *
     * @param int $userId
     * @param int $showtimeId
     * @return int
     */
    public function countActiveHeldSeats(int $userId, int $showtimeId): int
    {
        return (int) SeatHold::forUser($userId)
            ->where('showtime_id', $showtimeId)
            ->active()
            ->sum('seat_count');
    }

    /**
     * Đánh dấu hold là completed (payment thành công).
     * Gọi khi phát hiện booking đã chuyển sang Paid.
     *
     * @param int $bookingId
     * @return void
     */
    public function markCompleted(int $bookingId): void
    {
        $updated = SeatHold::where('booking_id', $bookingId)
            ->where('status', SeatHold::STATUS_ACTIVE)
            ->update([
                'status'      => SeatHold::STATUS_COMPLETED,
                'released_at' => now(),
            ]);

        if ($updated > 0) {
            Log::info('seat_hold_completed', ['booking_id' => $bookingId]);
        }
    }

    /**
     * Đánh dấu hold là expired (hết hạn, không có payment).
     *
     * @param int $bookingId
     * @return void
     */
    public function markExpired(int $bookingId): void
    {
        $updated = SeatHold::where('booking_id', $bookingId)
            ->where('status', SeatHold::STATUS_ACTIVE)
            ->update([
                'status' => SeatHold::STATUS_EXPIRED,
            ]);

        if ($updated > 0) {
            Log::info('seat_hold_expired', ['booking_id' => $bookingId]);
        }
    }

    /**
     * Đánh dấu hold là released (user cancel hoặc auto-cancel khi tạo booking mới).
     * Cancel bình thường KHÔNG tính là abuse.
     *
     * @param int $bookingId
     * @return void
     */
    public function markReleased(int $bookingId): void
    {
        $updated = SeatHold::where('booking_id', $bookingId)
            ->where('status', SeatHold::STATUS_ACTIVE)
            ->update([
                'status'      => SeatHold::STATUS_RELEASED,
                'released_at' => now(),
            ]);

        if ($updated > 0) {
            Log::info('seat_hold_released', ['booking_id' => $bookingId]);
        }
    }

    /**
     * Xử lý các hold đã quá hạn (batch).
     * Gọi từ scheduled cleanup command hoặc manual artisan.
     *
     * Flow:
     * 1. Tìm seat_holds status='active' và expires_at <= now()
     * 2. Cũng tìm seat_holds status='active' mà booking đã Paid → mark completed
     * 3. Update status → expired
     * 4. Cho mỗi user bị ảnh hưởng → checkAndApplyAbuse()
     *
     * @return int Số holds đã xử lý
     */
    public function processExpiredHolds(): int
    {
        // 1. Mark completed: holds có booking đã Paid nhưng chưa update tracking
        $completedCount = SeatHold::where('status', SeatHold::STATUS_ACTIVE)
            ->whereNotNull('booking_id')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('bookings')
                    ->whereColumn('bookings.id', 'seat_holds.booking_id')
                    ->where('bookings.status', 'Paid');
            })
            ->update([
                'status'      => SeatHold::STATUS_COMPLETED,
                'released_at' => now(),
            ]);

        if ($completedCount > 0) {
            Log::info('seat_holds_batch_completed', ['count' => $completedCount]);
        }

        // 2. Tìm holds quá hạn (overdue)
        $overdueHolds = SeatHold::overdue()->get();

        if ($overdueHolds->isEmpty()) {
            return $completedCount;
        }

        // Lấy danh sách user bị ảnh hưởng
        $affectedUserIds = $overdueHolds->pluck('user_id')->unique()->toArray();

        // Batch update status → expired
        SeatHold::overdue()->update([
            'status' => SeatHold::STATUS_EXPIRED,
        ]);

        Log::info('seat_holds_batch_expired', ['count' => $overdueHolds->count()]);

        // 3. Check abuse cho mỗi user bị ảnh hưởng
        foreach ($affectedUserIds as $userId) {
            $this->checkAndApplyAbuse($userId);
        }

        return $completedCount + $overdueHolds->count();
    }

    /**
     * Kiểm tra và áp dụng abuse detection cho user.
     *
     * Rules:
     * - 0-2 expired holds / 30 min → NORMAL
     * - 3-4 expired holds / 30 min → WARNING (log only, không block)
     * - 5+  expired holds / 30 min → RESTRICTION (block booking tạm thời)
     *
     * @param int $userId
     * @return string|null 'warning', 'restriction', hoặc null
     */
    public function checkAndApplyAbuse(int $userId): ?string
    {
        $windowMinutes    = (int) config('booking.abuse.window_minutes', 30);
        $warningThreshold = (int) config('booking.abuse.warning_threshold', 3);
        $blockThreshold   = (int) config('booking.abuse.block_threshold', 5);

        // Đếm expired holds trong sliding window
        $expiredCount = SeatHold::forUser($userId)
            ->expired()
            ->where('created_at', '>=', now()->subMinutes($windowMinutes))
            ->count();

        if ($expiredCount >= $blockThreshold) {
            return $this->applyRestriction($userId, $expiredCount, $windowMinutes);
        }

        if ($expiredCount >= $warningThreshold) {
            return $this->applyWarning($userId, $expiredCount, $windowMinutes);
        }

        return null;
    }

    /**
     * Kiểm tra user có đang bị restriction không.
     *
     * @param int $userId
     * @return bool
     */
    public function isRestricted(int $userId): bool
    {
        return BookingAbuseLog::forUser($userId)
            ->activeRestriction()
            ->exists();
    }

    /**
     * Lấy thời gian restriction còn lại (phút).
     *
     * @param int $userId
     * @return int|null Số phút còn lại, hoặc null nếu không bị restrict
     */
    public function getRemainingRestrictionMinutes(int $userId): ?int
    {
        $restriction = BookingAbuseLog::forUser($userId)
            ->activeRestriction()
            ->orderBy('blocked_until', 'desc')
            ->first();

        if (!$restriction) {
            return null;
        }

        return (int) max(0, now()->diffInMinutes($restriction->blocked_until, false));
    }

    // ========================================
    // PRIVATE METHODS
    // ========================================

    /**
     * Áp dụng warning (chỉ log, không block).
     */
    private function applyWarning(int $userId, int $expiredCount, int $windowMinutes): string
    {
        BookingAbuseLog::create([
            'user_id'        => $userId,
            'abuse_type'     => BookingAbuseLog::TYPE_WARNING,
            'expired_count'  => $expiredCount,
            'window_minutes' => $windowMinutes,
            'blocked_until'  => null, // Warning = không block
        ]);

        Log::warning('abuse_warning', [
            'user_id'       => $userId,
            'expired_count' => $expiredCount,
            'window'        => "{$windowMinutes} minutes",
        ]);

        return 'warning';
    }

    /**
     * Áp dụng restriction (block booking tạm thời).
     */
    private function applyRestriction(int $userId, int $expiredCount, int $windowMinutes): string
    {
        $firstBlockMinutes  = (int) config('booking.abuse.first_block_minutes', 30);
        $repeatBlockMinutes = (int) config('booking.abuse.repeat_block_minutes', 60);

        // Check nếu đã bị restrict trong 24h qua → dùng repeat duration
        $recentRestriction = BookingAbuseLog::forUser($userId)
            ->where('abuse_type', BookingAbuseLog::TYPE_RESTRICTION)
            ->where('created_at', '>=', now()->subHours(24))
            ->exists();

        $blockMinutes = $recentRestriction ? $repeatBlockMinutes : $firstBlockMinutes;
        $blockedUntil = now()->addMinutes($blockMinutes);

        BookingAbuseLog::create([
            'user_id'        => $userId,
            'abuse_type'     => BookingAbuseLog::TYPE_RESTRICTION,
            'expired_count'  => $expiredCount,
            'window_minutes' => $windowMinutes,
            'blocked_until'  => $blockedUntil,
            'details'        => [
                'block_minutes'      => $blockMinutes,
                'is_repeat'          => $recentRestriction,
            ],
        ]);

        Log::warning('booking_restriction_created', [
            'user_id'       => $userId,
            'expired_count' => $expiredCount,
            'blocked_until' => $blockedUntil->toDateTimeString(),
            'block_minutes' => $blockMinutes,
            'is_repeat'     => $recentRestriction,
        ]);

        return 'restriction';
    }
}
