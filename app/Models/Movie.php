<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Movie Model
 * ========================================
 * Phim
 */
class Movie extends Model
{
    use SoftDeletes;

    public const STATUS_SCHEDULED = 'SCHEDULED';
    public const STATUS_PRE_ORDER = 'PRE_ORDER';
    public const STATUS_COMING_SOON = 'COMING_SOON';
    public const STATUS_NOW_SHOWING = 'NOW_SHOWING';
    public const STATUS_ENDED = 'ENDED';

    public const STATUS_LABELS = [
        self::STATUS_SCHEDULED   => 'Lên lịch',
        self::STATUS_PRE_ORDER   => 'Mở bán sớm',
        self::STATUS_COMING_SOON => 'Sắp chiếu',
        self::STATUS_NOW_SHOWING => 'Đang chiếu',
        self::STATUS_ENDED       => 'Ngưng chiếu',
    ];

    public const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_PRE_ORDER,
        self::STATUS_COMING_SOON,
        self::STATUS_NOW_SHOWING,
        self::STATUS_ENDED,
    ];

    protected $fillable = [
        'title',
        'description',
        'director',
        'cast',
        'poster_url',
        'trailer_url',
        'duration',
        'age_rating',
        'format',
        'status',
        'language',
        'country',
        'release_date',
        'presale_date',
    ];

    protected $casts = [
        'format' => 'array',
        'release_date' => 'datetime',
        'presale_date' => 'datetime',
    ];

    /**
     * Accessor: Ensure format is always returned as an array.
     */
    public function getFormatAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        // Check if it's already an array (due to Laravel's standard array cast retrieving it)
        if (is_array($value)) {
            return array_values(array_filter($value));
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return is_array($decoded) ? array_values(array_filter($decoded)) : [$decoded];
        }

        if (is_string($value)) {
            return array_values(array_filter(array_map('trim', explode(',', $value))));
        }

        return [$value];
    }

    /**
     * Mutator: Ensure format is always stored as a JSON array.
     */
    public function setFormatAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['format'] = json_encode(array_values(array_filter($value)));
        } elseif (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->attributes['format'] = json_encode(array_values(array_filter($decoded)));
            } else {
                $array = array_map('trim', explode(',', $value));
                $this->attributes['format'] = json_encode(array_values(array_filter($array)));
            }
        } else {
            $this->attributes['format'] = json_encode([]);
        }
    }


    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_movie');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Helper: Lấy thời lượng phim dạng "h:mm"
     */
    public function getDurationFormatted(): string
    {
        $hours = intdiv($this->duration, 60);
        $minutes = $this->duration % 60;
        return sprintf('%d:%02d', $hours, $minutes);
    }

    /**
     * Kiểm tra phim có suất chiếu hợp lệ (SCHEDULED, ONGOING và chưa kết thúc)
     */
    public function hasActiveShowtimes(): bool
    {
        return $this->showtimes()
            ->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
            ->where(function ($q) {
                $q->where('end_time', '>', now())
                  ->orWhere(function ($sub) {
                      $sub->whereNull('end_time')->where('start_time', '>', now()->subHours(3));
                  });
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
            ->where(function ($q) {
                $q->where('end_time', '>', now())
                  ->orWhere(function ($sub) {
                      $sub->whereNull('end_time')->where('start_time', '>', now()->subHours(3));
                  });
            })
            ->count();
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isPreOrder(): bool
    {
        return $this->status === self::STATUS_PRE_ORDER;
    }

    public function isNowShowing(): bool
    {
        return $this->status === self::STATUS_NOW_SHOWING;
    }

    public function isComingSoon(): bool
    {
        return $this->status === self::STATUS_COMING_SOON;
    }

    public function isEnded(): bool
    {
        return $this->status === self::STATUS_ENDED;
    }

    /**
     * Check if this movie has active bookings for upcoming showtimes.
     */
    public function hasActiveFutureBookings(): bool
    {
        return (new \App\Services\MovieStatusValidationService())->hasActiveFutureBookings($this);
    }

    /**
     * Check if this movie has any successful bookings.
     */
    public function hasSuccessfulBookings(): bool
    {
        return (new \App\Services\MovieStatusValidationService())->hasSuccessfulBookings($this);
    }

    /**
     * Check if this movie has any historical bookings.
     */
    public function hasHistoricalBookings(): bool
    {
        return (new \App\Services\MovieStatusValidationService())->hasHistoricalBookings($this);
    }

    /**
     * Cancel all upcoming showtimes for this movie.
     */
    public function cancelUpcomingShowtimes(): int
    {
        return (new \App\Services\MovieStatusValidationService())->cancelUpcomingShowtimes($this);
    }

    /**
     * Publish all pending showtimes for this movie.
     */
    public function publishPendingShowtimes(): int
    {
        return (new \App\Services\MovieStatusValidationService())->publishPendingShowtimes($this);
    }

    public function isTicketSalesOpen(): bool
    {
        return $this->status !== self::STATUS_SCHEDULED;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /**
     * Tự động chuyển đổi trạng thái phim và kích hoạt suất chiếu draft:
     * - Nếu có presale_date và now >= presale_date (và now < release_date) -> PRE_ORDER (Mở bán sớm)
     * - Nếu now >= release_date -> NOW_SHOWING (Đang chiếu)
     */
    public static function syncAllStatuses(): int
    {
        $now = now();
        $updatedCount = 0;

        // 1. Chuyển các phim SCHEDULED hoặc PRE_ORDER hoặc COMING_SOON có release_date <= now sang NOW_SHOWING
        $nowShowingMovies = self::whereIn('status', [self::STATUS_SCHEDULED, self::STATUS_PRE_ORDER, self::STATUS_COMING_SOON])
            ->whereNotNull('release_date')
            ->where('release_date', '<=', $now)
            ->get();

        foreach ($nowShowingMovies as $movie) {
            $movie->update(['status' => self::STATUS_NOW_SHOWING]);
            // Chuyển các suất chiếu PENDING của phim sang SCHEDULED
            $movie->showtimes()
                ->where('status', Showtime::STATUS_PENDING)
                ->where('start_time', '>', $now)
                ->update(['status' => Showtime::STATUS_SCHEDULED]);
            $updatedCount++;
        }

        // 2. Chuyển các phim SCHEDULED hoặc COMING_SOON có presale_date <= now (và chưa tới hoặc không có release_date) sang PRE_ORDER
        $preOrderMovies = self::whereIn('status', [self::STATUS_SCHEDULED, self::STATUS_COMING_SOON])
            ->whereNotNull('presale_date')
            ->where('presale_date', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('release_date')
                  ->orWhere('release_date', '>', $now);
            })
            ->get();

        foreach ($preOrderMovies as $movie) {
            $movie->update(['status' => self::STATUS_PRE_ORDER]);
            // Chuyển các suất chiếu PENDING của phim sang SCHEDULED để mở bán sớm
            $movie->showtimes()
                ->where('status', Showtime::STATUS_PENDING)
                ->where('start_time', '>', $now)
                ->update(['status' => Showtime::STATUS_SCHEDULED]);
            $updatedCount++;
        }

        return $updatedCount;
    }
}

