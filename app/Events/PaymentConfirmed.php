<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bookingCode;
    public $bookingId;
    public $status;
    public $redirectUrl;
    public $message;
    public $paymentMethod;

    /**
     * Create a new event instance.
     */
    public function __construct(
        string $bookingCode,
        int $bookingId,
        string $redirectUrl,
        string $status = 'PAID',
        string $message = 'Thanh toán thành công! Đang chuyển hướng...',
        ?string $paymentMethod = null
    ) {
        $this->bookingCode = $bookingCode;
        $this->bookingId = $bookingId;
        $this->redirectUrl = $redirectUrl;
        $this->status = $status;
        $this->message = $message;
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('order.' . $this->bookingCode),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'PaymentConfirmed';
    }

    /**
     * Get data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'bookingCode' => $this->bookingCode,
            'bookingId' => $this->bookingId,
            'status' => $this->status,
            'redirectUrl' => $this->redirectUrl,
            'message' => $this->message,
            'paymentMethod' => $this->paymentMethod,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
