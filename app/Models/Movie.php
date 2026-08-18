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
    ];

    protected $casts = [
        'format' => 'array',
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
}
