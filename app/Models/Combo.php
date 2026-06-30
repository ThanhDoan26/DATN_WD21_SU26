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
}
