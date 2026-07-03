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

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
