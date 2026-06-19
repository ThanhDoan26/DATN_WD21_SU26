<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingCombo extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'combo_id',
        'quantity',
        'price',
    ];

    /**
     * Get the booking that owns the booking combo.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the combo associated with the booking combo.
     */
    public function combo()
    {
        return $this->belongsTo(Combo::class);
    }
}
