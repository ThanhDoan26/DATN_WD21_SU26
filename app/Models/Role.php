<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Role Model
 * ========================================
 * Vai trò trong hệ thống: USER, STAFF, MANAGER, ADMIN
 */
class Role extends Model
{
    protected $fillable = ['role_name', 'description'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
