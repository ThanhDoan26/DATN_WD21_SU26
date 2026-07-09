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
        'status',
        'language',
        'country',
    ];

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
}
