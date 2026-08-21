<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Showtime Model
 * ========================================
 * Suất chiếu
 */
class Showtime extends Model
{
    use SoftDeletes;
    public const STATUS_SCHEDULED = 'SCHEDULED';
    public const STATUS_ONGOING = 'ONGOING';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_CANCELLED = 'CANCELLED';

    public const STATUS_LABELS = [
        self::STATUS_SCHEDULED => 'Lên lịch',
        self::STATUS_ONGOING   => 'Đang chiếu',
        self::STATUS_COMPLETED => 'Đã chiếu',
        self::STATUS_CANCELLED => 'Đã hủy',
    ];

    public const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_ONGOING,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = ['movie_id', 'room_id', 'start_time', 'end_time', 'status', 'surcharge'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'surcharge' => 'decimal:2',
    ];

    /**
     * Tự động đồng bộ trạng thái thực tế của tất cả các suất chiếu dựa trên thời gian hiện tại:
     * - Đã qua giờ kết thúc (end_time <= now) hoặc quá 3 tiếng nếu chưa có end_time -> COMPLETED
     * - Đang trong giờ chiếu (start_time <= now < end_time) -> ONGOING
     */
    public static function syncAllStatuses(): void
    {
        // 1. Chuyển các suất đã kết thúc sang COMPLETED
        self::where('status', '!=', self::STATUS_CANCELLED)
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('end_time')
                        ->where('end_time', '<=', now());
                })->orWhere(function ($sub) {
                    $sub->whereNull('end_time')
                        ->where('start_time', '<=', now()->subHours(3));
                });
            })
            ->where('status', '!=', self::STATUS_COMPLETED)
            ->update(['status' => self::STATUS_COMPLETED]);

        // 2. Chuyển các suất đang chiếu sang ONGOING
        self::where('status', '!=', self::STATUS_CANCELLED)
            ->where('start_time', '<=', now())
            ->where(function ($q) {
                $q->where('end_time', '>', now())
                  ->orWhere(function ($sub) {
                      $sub->whereNull('end_time')
                          ->where('start_time', '>', now()->subHours(3));
                  });
            })
            ->where('status', '!=', self::STATUS_ONGOING)
            ->update(['status' => self::STATUS_ONGOING]);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /**
     * Kiểm tra suất chiếu có được phép đặt vé Trực tuyến (Online) không:
     * - Trạng thái phải là SCHEDULED
     * - Phải đặt TRƯỚC giờ chiếu tối thiểu 15 phút (khóa online khi còn <= 15 phút)
     */
    public function isOnlineBookable(): bool
    {
        if ($this->status !== self::STATUS_SCHEDULED) {
            return false;
        }

        if (!$this->start_time) {
            return false;
        }

        // Khóa đặt online nếu thời gian hiện tại cách giờ chiếu dưới 15 phút hoặc đã qua giờ chiếu
        return now()->addMinutes(15)->lt($this->start_time);
    }

    /**
     * Kiểm tra suất chiếu có được phép bán vé Trực tiếp (Tại quầy / Walk-in) không:
     * - Trạng thái phải là SCHEDULED hoặc ONGOING
     * - Cho phép bán trong vòng 30 phút đầu kể từ khi bắt đầu chiếu (0 - 30 phút sau start_time)
     * - Đã quá 30 phút hoặc đã kết thúc (end_time <= now) -> Khóa hoàn toàn
     */
    public function isWalkInBookable(): bool
    {
        if (!in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_ONGOING])) {
            return false;
        }

        if (!$this->start_time) {
            return false;
        }

        // Suất chiếu đã kết thúc -> Không bán
        if ($this->end_time && now()->gte($this->end_time)) {
            return false;
        }

        // Cho phép bán nếu chưa tới giờ chiếu hoặc mới chiếu không quá 30 phút
        return now()->lte($this->start_time->copy()->addMinutes(30));
    }

    /**
     * Kiểm tra tổng quát theo nguồn đặt vé
     */
    public function isBookable(string $source = 'online'): bool
    {
        if ($source === 'walk_in' || $source === 'pos' || $source === 'counter') {
            return $this->isWalkInBookable();
        }

        return $this->isOnlineBookable();
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class)->withTrashed();
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class)->withTrashed();
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
     * Query scope: Chỉ các suất chiếu sắp tới đang mở bán
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SCHEDULED)
            ->where('start_time', '>=', now());
    }

    /**
     * Query scope: Chỉ các suất chiếu theo phòng
     */
    public function scopeForRoom(Builder $query, int $roomId): Builder
    {
        return $query->where('room_id', $roomId);
    }

    /**
     * Query scope: Chỉ các suất chiếu theo phim
     */
    public function scopeForMovie(Builder $query, int $movieId): Builder
    {
        return $query->where('movie_id', $movieId);
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isOngoing(): bool
    {
        return $this->status === self::STATUS_ONGOING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function durationMinutes(): ?int
    {
        if (! $this->start_time || ! $this->end_time) {
            return null;
        }

        return $this->end_time->diffInMinutes($this->start_time);
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

    /**
     * Thống kê: Lấy số lượng ghế đã được đặt (không tính các booking đã bị hủy)
     */
    public function getBookedSeatsCount(): int
    {
        return $this->bookings()
            ->where('bookings.status', '!=', 'Cancelled')
            ->join('booked_seats', 'bookings.id', '=', 'booked_seats.booking_id')
            ->count('booked_seats.id');
    }

    /**
     * Thống kê: Tính tỷ lệ lấp đầy phòng chiếu (%)
     */
    public function getOccupancyRate(): float
    {
        $totalSeats = $this->room?->total_seats ?? 0;
        if ($totalSeats == 0) {
            return 0;
        }
        
        $bookedCount = $this->getBookedSeatsCount();
        return round(($bookedCount / $totalSeats) * 100, 2);
    }
}
