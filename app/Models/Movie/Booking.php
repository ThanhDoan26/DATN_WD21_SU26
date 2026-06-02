<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Role Model
 * ========================================
 * Mô tả: Vai trò trong hệ thống
 * Vai trò: USER, STAFF, MANAGER, ADMIN
 */
class Role extends Model
{
    protected $fillable = ['role_name', 'description'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}

/**
 * User Model
 * ========================================
 * Mô tả: Tài khoản người dùng
 *
 * Attributes:
 *   - role_id: Vai trò
 *   - cinema_id: Rạp (nullable - chỉ staff/manager có)
 *   - password_hash: Phải được hash bằng bcrypt
 *   - loyalty_points: Điểm tích lũy
 */
class User extends Model
{
    protected $fillable = [
        'role_id',
        'cinema_id',
        'full_name',
        'email',
        'phone',
        'password_hash',
        'loyalty_points',
        'status',
        'email_verified_at',
    ];

    protected $hidden = ['password_hash'];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Helper: Kiểm tra user có vai trò admin
     */
    public function isAdmin(): bool
    {
        return $this->role?->role_name === 'ADMIN';
    }

    /**
     * Helper: Kiểm tra user có vai trò manager
     */
    public function isManager(): bool
    {
        return $this->role?->role_name === 'MANAGER';
    }

    /**
     * Helper: Kiểm tra user có vai trò staff
     */
    public function isStaff(): bool
    {
        return $this->role?->role_name === 'STAFF';
    }
}

/**
 * Cinema Model
 * ========================================
 * Mô tả: Rạp chiếu phim
 */
class Cinema extends Model
{
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

/**
 * Room Model
 * ========================================
 * Mô tả: Phòng chiếu
 */
class Room extends Model
{
    protected $fillable = ['cinema_id', 'name', 'format', 'total_seats', 'status'];

    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class);
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class);
    }
}

/**
 * Seat Model
 * ========================================
 * Mô tả: Ghế ngồi
 *
 * Composite key: (room_id, row_name, seat_number)
 */
class Seat extends Model
{
    protected $fillable = ['room_id', 'row_name', 'seat_number', 'seat_type', 'status'];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bookedSeats(): HasMany
    {
        return $this->hasMany(BookedSeat::class);
    }

    /**
     * Helper: Lấy code ghế (e.g., "A1", "F10")
     */
    public function getSeatCode(): string
    {
        return $this->row_name . $this->seat_number;
    }
}

/**
 * Movie Model
 * ========================================
 * Mô tả: Phim
 */
class Movie extends Model
{
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

/**
 * Showtime Model
 * ========================================
 * Mô tả: Suất chiếu
 */
class Showtime extends Model
{
    protected $fillable = ['movie_id', 'room_id', 'start_time', 'end_time', 'status'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function ticketPrices(): HasMany
    {
        return $this->hasMany(TicketPrice::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Helper: Lấy cinema qua room
     */
    public function getCinema(): ?Cinema
    {
        return $this->room?->cinema;
    }

    /**
     * Helper: Lấy giá vé theo loại ghế
     */
    public function getPriceByType(string $seatType): ?float
    {
        return $this->ticketPrices()
            ->where('seat_type', $seatType)
            ->where('status', 'ACTIVE')
            ->value('price');
    }
}

/**
 * TicketPrice Model
 * ========================================
 * Mô tả: Giá vé (linh hoạt theo suất chiếu + loại ghế)
 */
class TicketPrice extends Model
{
    protected $fillable = ['showtime_id', 'seat_type', 'price', 'status'];

    public function showtime(): BelongsTo
    {
        return $this->belongsTo(Showtime::class);
    }
}

/**
 * Booking Model
 * ========================================
 * Mô tả: Đơn hàng mua vé
 *
 * Status: Pending, Paid, Cancelled, Used
 */
class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'showtime_id',
        'total_price',
        'status',
        'payment_method',
        'booking_time',
        'payment_time',
        'cancelled_at',
        'cancellation_reason',
        'booking_code',
        'notes',
    ];

    protected $casts = [
        'booking_time' => 'datetime',
        'payment_time' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function showtime(): BelongsTo
    {
        return $this->belongsTo(Showtime::class);
    }

    public function bookedSeats(): HasMany
    {
        return $this->hasMany(BookedSeat::class);
    }

    /**
     * Helper: Kiểm tra booking đã thanh toán
     */
    public function isPaid(): bool
    {
        return $this->status === 'Paid';
    }

    /**
     * Helper: Kiểm tra booking bị hủy
     */
    public function isCancelled(): bool
    {
        return $this->status === 'Cancelled';
    }

    /**
     * Helper: Lấy chi tiết các ghế đã đặt
     */
    public function getSeatsInfo(): array
    {
        return $this->bookedSeats()
            ->join('seats', 'booked_seats.seat_id', '=', 'seats.id')
            ->select('seats.row_name', 'seats.seat_number', 'seats.seat_type', 'booked_seats.price_at_booking')
            ->get()
            ->map(fn($seat) => [
                'code' => $seat->row_name . $seat->seat_number,
                'type' => $seat->seat_type,
                'price' => $seat->price_at_booking,
            ])
            ->toArray();
    }
}

/**
 * BookedSeat Model
 * ========================================
 * Mô tả: Chi tiết vé (ghế đã đặt trong booking)
 *
 * ⚠️ CRITICAL: Bảng này có race condition protection bằng SELECT FOR UPDATE
 * Status: RESERVED, PAID, USED, CANCELLED
 */
class BookedSeat extends Model
{
    protected $fillable = [
        'booking_id',
        'seat_id',
        'price_at_booking',
        'status',
        'qr_code',
        'checked_in_at',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }

    /**
     * Helper: Checkin vé (mark as USED)
     */
    public function checkin(): bool
    {
        return $this->update([
            'status' => 'USED',
            'checked_in_at' => now(),
        ]);
    }

    /**
     * Helper: Lấy code ghế
     */
    public function getSeatCode(): string
    {
        return $this->seat?->getSeatCode() ?? 'N/A';
    }
}
