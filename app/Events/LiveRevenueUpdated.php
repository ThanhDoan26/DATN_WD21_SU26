<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveRevenueUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $cinemaId;
    public $amount;
    public $totalToday;
    public $bookingsTodayCount;
    public $showtimeId;
    public $movieTitle;
    public $newOccupancyRate;
    public $isHighOccupancy;

    /**
     * Create a new event instance.
     */
    public function __construct(
        int $cinemaId,
        float $amount,
        float $totalToday,
        int $bookingsTodayCount,
        ?int $showtimeId = null,
        ?string $movieTitle = null,
        float $newOccupancyRate = 0.0
    ) {
        $this->cinemaId = $cinemaId;
        $this->amount = $amount;
        $this->totalToday = $totalToday;
        $this->bookingsTodayCount = $bookingsTodayCount;
        $this->showtimeId = $showtimeId;
        $this->movieTitle = $movieTitle;
        $this->newOccupancyRate = round($newOccupancyRate, 1);
        $this->isHighOccupancy = $this->newOccupancyRate >= 90.0;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('cinema.' . $this->cinemaId . '.admin'),
            new PrivateChannel('admin.dashboard'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'LiveRevenueUpdated';
    }

    /**
     * Get data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'cinemaId' => $this->cinemaId,
            'amount' => $this->amount,
            'totalToday' => $this->totalToday,
            'bookingsTodayCount' => $this->bookingsTodayCount,
            'showtimeId' => $this->showtimeId,
            'movieTitle' => $this->movieTitle,
            'newOccupancyRate' => $this->newOccupancyRate,
            'isHighOccupancy' => $this->isHighOccupancy,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
