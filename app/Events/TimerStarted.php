<?php

namespace App\Events;

use App\Models\TimeEntry;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimerStarted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public TimeEntry $entry,
        public int $userId
    ) {
        // Load relationships
        $this->entry->load('project.client');
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('timers.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'timer.started';
    }

    public function broadcastWith(): array
    {
        // Explicitly format dates as ISO 8601 for JavaScript compatibility
        $entry = $this->entry->toArray();

        // Ensure dates are in ISO 8601 format
        if ($this->entry->started_at) {
            $entry['started_at'] = $this->entry->started_at->toIso8601String();
        }
        if ($this->entry->stopped_at) {
            $entry['stopped_at'] = $this->entry->stopped_at->toIso8601String();
        }
        if ($this->entry->resumed_at) {
            $entry['resumed_at'] = $this->entry->resumed_at->toIso8601String();
        }

        return [
            'entry' => $entry,
        ];
    }
}
