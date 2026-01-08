<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimerDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $entryId,
        public int $userId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('timers.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'timer.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'entry_id' => $this->entryId,
        ];
    }
}
