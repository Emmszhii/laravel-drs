<?php

namespace App\Events;

use App\Models\Disaster;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast over Reverb whenever an incident is logged or its signal level changes.
 * Frontend subscribes via the `disasters` channel (see routes/channels.php).
 */
class DisasterAlertEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Disaster $disaster)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('disasters'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'disaster.alert';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->disaster->id,
            'name' => $this->disaster->name,
            'signal' => $this->disaster->signal,
            'status' => $this->disaster->status,
            'zone' => $this->disaster->zone,
            'lat' => $this->disaster->lat,
            'lng' => $this->disaster->lng,
        ];
    }
}
