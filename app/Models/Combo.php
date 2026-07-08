<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Combo extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'image',
        'description',
        'status',
    ];

    /**
     * Get the booking combos for the combo.
     */
    public function bookingCombos()
    {
        return $this->hasMany(BookingCombo::class);
    }

    /**
     * Get the reviews for the combo.
     */
    public function comboReviews()
    {
        return $this->hasMany(ComboReview::class);
    }

    /**
     * Helper: Get average rating.
     */
    public function getAverageRatingAttribute()
    {
        return round($this->comboReviews()->avg('rating') ?? 0, 1);
    }

    /**
     * Helper: Get total reviews.
     */
    public function getTotalReviewsAttribute()
    {
        return $this->comboReviews()->count();
    }
}
