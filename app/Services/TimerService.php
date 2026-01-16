<?php

namespace App\Services;

use App\Models\TimeEntry;
use App\Models\User;
use App\Events\TimerStarted;
use App\Events\TimerStopped;
use Carbon\Carbon;

class TimerService
{
    public function startTimer(User $user, int $projectId, ?string $description = null, bool $isBillable = true): TimeEntry
    {
        $timeEntry = TimeEntry::create([
            'user_id' => $user->id,
            'project_id' => $projectId,
            'description' => $description,
            'is_billable' => $isBillable,
            'started_at' => Carbon::now(),
            'resumed_at' => Carbon::now(),
            'duration_minutes' => 0,
        ]);

        broadcast(new TimerStarted($timeEntry, $user->id));

        return $timeEntry;
    }

    public function stopTimer(TimeEntry $timeEntry, User $user): TimeEntry
    {
        if ($timeEntry->stopped_at) {
            throw new \InvalidArgumentException('Timer already stopped');
        }

        $stoppedAt = Carbon::now();
        $sessionStart = $timeEntry->resumed_at
            ? Carbon::parse($timeEntry->resumed_at)
            : Carbon::parse($timeEntry->started_at);
        $sessionDuration = abs($sessionStart->diffInMinutes($stoppedAt));

        $totalDuration = ($timeEntry->duration_minutes ?? 0) + $sessionDuration;

        $timeEntry->update([
            'stopped_at' => $stoppedAt,
            'duration_minutes' => $totalDuration,
        ]);

        broadcast(new TimerStopped($timeEntry->fresh(), $user->id));

        return $timeEntry->fresh();
    }

    public function restartTimer(TimeEntry $timeEntry, User $user): TimeEntry
    {
        if (!$timeEntry->stopped_at) {
            throw new \InvalidArgumentException('Timer is already running');
        }

        $timeEntry->update([
            'resumed_at' => Carbon::now(),
            'stopped_at' => null,
        ]);

        broadcast(new TimerStarted($timeEntry->fresh(), $user->id));

        return $timeEntry->fresh();
    }

    public function getActiveTimer(User $user): ?TimeEntry
    {
        return $user->timeEntries()
            ->whereNull('stopped_at')
            ->with('project.client')
            ->first();
    }
}
