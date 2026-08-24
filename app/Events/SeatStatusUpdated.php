<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeatStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $showtimeId;
    public $seatIds;
    public $status;
    public $userId;
    public $userRole;

    /**
     * Create a new event instance.
     *
     * @param int|string $showtimeId
     * @param array $seatIds
     * @param string $status (HOLD, PENDING, PAID, AVAILABLE)
     * @param int|null $userId
     * @param string|null $userRole
     */
    public function __construct($showtimeId, array $seatIds, string $status, ?int $userId = null, ?string $userRole = null)
    {
        $this->showtimeId = (int) $showtimeId;
        $this->seatIds = array_values(array_unique(array_map('intval', $seatIds)));
        $this->status = strtoupper($status);
        $this->userId = $userId ?? auth()->id();
        $this->userRole = $userRole ?? (auth()->check() ? auth()->user()->role : 'CUSTOMER');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel|PresenceChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('showtime.' . $this->showtimeId),
            new Channel('showtime.' . $this->showtimeId), // Fallback for public listeners
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'SeatStatusUpdated';
    }

    /**
     * Get data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'showtimeId' => $this->showtimeId,
            'seatIds' => $this->seatIds,
            'status' => $this->status,
            'userId' => $this->userId,
            'userRole' => $this->userRole,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
