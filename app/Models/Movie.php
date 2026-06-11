<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Movie Model
 * ========================================
 * Phim
 */
class Movie extends Model
{
    public const STATUS_COMING_SOON = 'COMING_SOON';
    public const STATUS_NOW_SHOWING = 'NOW_SHOWING';
    public const STATUS_ENDED = 'ENDED';

    public const STATUSES = [
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
        'status',
        'language',
        'country',
    ];

    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class);
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
}
