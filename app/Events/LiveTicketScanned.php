<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveTicketScanned implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $cinemaId;
    public $bookingCode;
    public $seatCode;
    public $movieTitle;
    public $roomName;
    public $showtime;
    public $scannedAt;
    public $status;
    public $staffName;
    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(
        int $cinemaId,
        string $bookingCode,
        string $seatCode,
        string $movieTitle,
        string $roomName,
        string $showtime,
        string $status = 'SUCCESS',
        ?string $staffName = null,
        ?string $message = null
    ) {
        $this->cinemaId = $cinemaId;
        $this->bookingCode = $bookingCode;
        $this->seatCode = $seatCode;
        $this->movieTitle = $movieTitle;
        $this->roomName = $roomName;
        $this->showtime = $showtime;
        $this->scannedAt = now()->format('H:i:s d/m/Y');
        $this->status = $status;
        $this->staffName = $staffName ?? (auth()->check() ? auth()->user()->name : 'Nhân viên');
        $this->message = $message ?? ($status === 'SUCCESS' ? 'Soát vé thành công' : 'Cảnh báo vé đã sử dụng');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('cinema.' . $this->cinemaId . '.staff'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'LiveTicketScanned';
    }

    /**
     * Get data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'cinemaId' => $this->cinemaId,
            'bookingCode' => $this->bookingCode,
            'seatCode' => $this->seatCode,
            'movieTitle' => $this->movieTitle,
            'roomName' => $this->roomName,
            'showtime' => $this->showtime,
            'scannedAt' => $this->scannedAt,
            'status' => $this->status,
            'staffName' => $this->staffName,
            'message' => $this->message,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
