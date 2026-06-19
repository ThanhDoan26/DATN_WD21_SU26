<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'phone', 'role_id', 'cinema_id', 'loyalty_points', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Người dùng thuộc một role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Người dùng thuộc một cinema (nếu là staff/manager)
     */
    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class);
    }

    /**
     * Người dùng có nhiều bookings
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Kiểm tra user có vai trò Admin
     */
    public function isAdmin(): bool
    {
        return $this->role?->role_name === 'ADMIN';
    }

    /**
     * Kiểm tra user có vai trò Manager
     */
    public function isManager(): bool
    {
        return $this->role?->role_name === 'MANAGER';
    }

    /**
     * Kiểm tra user có vai trò Staff
     */
    public function isStaff(): bool
    {
        return $this->role?->role_name === 'STAFF';
    }

    /**
     * Kiểm tra user có vai trò User (khách hàng)
     */
    public function isCustomer(): bool
    {
        return $this->role?->role_name === 'USER';
    }

    /**
     * Kiểm tra user hoạt động
     */
    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }

    /**
     * Override default password reset notification for demo purposes.
     */
    public function sendPasswordResetNotification($token): void
    {
        session()->flash('demo_reset_token', $token);
    }
}
