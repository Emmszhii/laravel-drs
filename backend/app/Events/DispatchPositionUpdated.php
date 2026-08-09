<?php

namespace App\Events;

use App\Models\Dispatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast over Reverb whenever a dispatch's live GPS position is updated.
 * Frontend subscribes via the `dispatches` channel (see routes/channels.php).
 */
class DispatchPositionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Dispatch $dispatch)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('dispatches'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'dispatch.position-updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->dispatch->id,
            'disaster_id' => $this->dispatch->disaster_id,
            'status' => $this->dispatch->status,
            'current_lat' => $this->dispatch->current_lat,
            'current_lng' => $this->dispatch->current_lng,
            'last_position_at' => $this->dispatch->last_position_at?->toIso8601String(),
            'vehicle_label' => $this->dispatch->vehicle_label,
        ];
    }
}
