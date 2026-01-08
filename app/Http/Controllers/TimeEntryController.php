<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use App\Events\TimerStarted;
use App\Events\TimerStopped;
use App\Events\TimerDeleted;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TimeEntryController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $query = $request->user()->timeEntries()->with('project.client');

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        return $query->orderBy('started_at', 'desc')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'description' => 'nullable|string',
            'is_billable' => 'boolean'
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['started_at'] = Carbon::now();
        $validated['resumed_at'] = Carbon::now();
        $validated['duration_minutes'] = 0;

        // Default is_billable to true if not provided
        if (!isset($validated['is_billable'])) {
            $validated['is_billable'] = true;
        }

        $timeEntry = TimeEntry::create($validated);

        // Broadcast timer started
        broadcast(new TimerStarted($timeEntry, $request->user()->id));

        return response()->json($timeEntry->load('project.client'), 201);
    }

    public function show(TimeEntry $timeEntry)
    {
        $this->authorize('view', $timeEntry);

        return $timeEntry->load('project.client');
    }

    public function update(Request $request, TimeEntry $timeEntry)
    {
        $this->authorize('update', $timeEntry);

        try {
            $validated = $request->validate([
                'project_id' => 'nullable|exists:projects,id',
                'description' => 'nullable|string',
                'started_at' => 'nullable|date',
                'stopped_at' => 'nullable|date|after:started_at',
                'is_billable' => 'nullable|boolean'
            ]);

            if (isset($validated['started_at']) && isset($validated['stopped_at'])) {
                $start = Carbon::parse($validated['started_at']);
                $stop = Carbon::parse($validated['stopped_at']);
                $validated['duration_minutes'] = abs($start->diffInMinutes($stop));
            }

            $timeEntry->update($validated);

            return response()->json($timeEntry->load('project.client'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed for time entry update', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            throw $e;
        }
    }

    public function destroy(Request $request, TimeEntry $timeEntry)
    {
        $this->authorize('delete', $timeEntry);

        $entryId = $timeEntry->id;
        $userId = $request->user()->id;

        $timeEntry->delete();

        // Broadcast timer deleted
        broadcast(new TimerDeleted($entryId, $userId));

        return response()->json(['message' => 'Time entry deleted successfully']);
    }

    public function stop(Request $request, TimeEntry $timeEntry)
    {
        $this->authorize('update', $timeEntry);

        if ($timeEntry->stopped_at) {
            return response()->json(['message' => 'Timer already stopped'], 400);
        }

        $stoppedAt = Carbon::now();
        // Use resumed_at for session duration calculation (falls back to started_at for older entries)
        $sessionStart = $timeEntry->resumed_at ? Carbon::parse($timeEntry->resumed_at) : Carbon::parse($timeEntry->started_at);
        $sessionDuration = abs($sessionStart->diffInMinutes($stoppedAt));

        // Accumulate duration (add to existing duration from previous sessions)
        $totalDuration = ($timeEntry->duration_minutes ?? 0) + $sessionDuration;

        $timeEntry->update([
            'stopped_at' => $stoppedAt,
            'duration_minutes' => $totalDuration
        ]);

        // Broadcast timer stopped
        broadcast(new TimerStopped($timeEntry->fresh(), $request->user()->id));

        return response()->json($timeEntry->load('project.client'));
    }

    public function restart(Request $request, TimeEntry $timeEntry)
    {
        $this->authorize('update', $timeEntry);

        if (!$timeEntry->stopped_at) {
            return response()->json(['message' => 'Timer is already running'], 400);
        }

        // Restart the entry:
        // - Keep started_at unchanged (preserves which day the entry belongs to)
        // - Set resumed_at to now (for live timer calculation)
        // - Clear stopped_at
        // - duration_minutes already contains accumulated time from previous sessions
        $timeEntry->update([
            'resumed_at' => Carbon::now(),
            'stopped_at' => null
        ]);

        // Broadcast timer started (restart)
        broadcast(new TimerStarted($timeEntry->fresh(), $request->user()->id));

        return response()->json($timeEntry->load('project.client'));
    }

    public function active(Request $request)
    {
        $activeEntry = $request->user()
            ->timeEntries()
            ->whereNull('stopped_at')
            ->with('project.client')
            ->first();

        return response()->json($activeEntry);
    }
}
