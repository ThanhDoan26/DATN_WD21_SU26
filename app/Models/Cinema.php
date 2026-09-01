<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Cinema Model
 * ========================================
 * Rạp chiếu phim
 */
class Cinema extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'address', 'city', 'phone', 'email', 'status'];

    /**
     * Mutator to sanitize and normalize cinema name
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = is_string($value) ? preg_replace('/\s+/', ' ', trim($value)) : $value;
    }

    /**
     * Mutator to sanitize and normalize cinema address
     */
    public function setAddressAttribute($value): void
    {
        $this->attributes['address'] = is_string($value) ? preg_replace('/\s+/', ' ', trim($value)) : $value;
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function cinemaReviews(): HasMany
    {
        return $this->hasMany(\App\Models\CinemaReview::class);
    }
}
