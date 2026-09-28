<?php

namespace App\Events;

use App\Models\ReplenishmentRun;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Broadcast over Reverb when a replenishment run completes, fails, or is
 * approved/rejected, so the dashboard refreshes live (Pages/Replenishment).
 * ShouldBroadcastNow: it's already sent from inside the queued job.
 */
class ReplenishmentRunUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public ReplenishmentRun $run)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('replenishment')];
    }

    public function broadcastAs(): string
    {
        return 'run.updated';
    }

    public function broadcastWith(): array
    {
        return ['id' => $this->run->id, 'status' => $this->run->status];
    }
}
