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
        return $this->belongsTo(Cinema::class)->withTrashed();
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
<<<<<<< HEAD
     * Kiểm tra phòng có suất chiếu hợp lệ
     * - ONGOING: luôn chặn xóa (đang chiếu)
     * - SCHEDULED: chỉ chặn nếu chưa qua giờ chiếu (end_time >= now)
=======
     * Kiểm tra phòng có suất chiếu hợp lệ (SCHEDULED, ONGOING và chưa kết thúc)
>>>>>>> a20636686d436015cb97f84dc0f14733a4a58fc2
     */
    public function hasActiveShowtimes(): bool
    {
        return $this->showtimes()
            ->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
<<<<<<< HEAD
            ->where(function ($query) {
                $query->where('status', Showtime::STATUS_ONGOING)
                      ->orWhereNull('end_time')
                      ->orWhere('end_time', '>=', now());
=======
            ->where(function ($q) {
                $q->where('end_time', '>', now())
                  ->orWhere(function ($sub) {
                      $sub->whereNull('end_time')->where('start_time', '>', now()->subHours(3));
                  });
>>>>>>> a20636686d436015cb97f84dc0f14733a4a58fc2
            })
            ->exists();
    }

    /**
     * Lấy số lượng suất chiếu hợp lệ
     */
    public function getActiveShowtimesCount(): int
    {
        return $this->showtimes()
            ->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
<<<<<<< HEAD
            ->where(function ($query) {
                $query->where('status', Showtime::STATUS_ONGOING)
                      ->orWhereNull('end_time')
                      ->orWhere('end_time', '>=', now());
=======
            ->where(function ($q) {
                $q->where('end_time', '>', now())
                  ->orWhere(function ($sub) {
                      $sub->whereNull('end_time')->where('start_time', '>', now()->subHours(3));
                  });
>>>>>>> a20636686d436015cb97f84dc0f14733a4a58fc2
            })
            ->count();
    }

    /**
     * Lấy danh sách suất chiếu hợp lệ
     */
    public function getActiveShowtimes()
    {
        return $this->showtimes()
            ->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
<<<<<<< HEAD
            ->where(function ($query) {
                $query->where('status', Showtime::STATUS_ONGOING)
                      ->orWhereNull('end_time')
                      ->orWhere('end_time', '>=', now());
=======
            ->where(function ($q) {
                $q->where('end_time', '>', now())
                  ->orWhere(function ($sub) {
                      $sub->whereNull('end_time')->where('start_time', '>', now()->subHours(3));
                  });
>>>>>>> a20636686d436015cb97f84dc0f14733a4a58fc2
            })
            ->with('movie')
            ->orderBy('start_time')
            ->get();
    }
}