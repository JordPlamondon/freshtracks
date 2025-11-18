<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TimeEntryController extends Controller
{
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

        $timeEntry = TimeEntry::create($validated);

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

        $validated = $request->validate([
            'project_id' => 'exists:projects,id',
            'description' => 'nullable|string',
            'started_at' => 'date',
            'stopped_at' => 'date|after:started_at',
            'is_billable' => 'boolean'
        ]);

        if (isset($validated['started_at']) && isset($validated['stopped_at'])) {
            $start = Carbon::parse($validated['started_at']);
            $stop = Carbon::parse($validated['stopped_at']);
            $validated['duration_minutes'] = $stop->diffInMinutes($start);
        }

        $timeEntry->update($validated);

        return response()->json($timeEntry->load('project.client'));
    }

    public function destroy(TimeEntry $timeEntry)
    {
        $this->authorize('delete', $timeEntry);

        $timeEntry->delete();

        return response()->json(['message' => 'Time entry deleted successfully']);
    }

    public function stop(Request $request, TimeEntry $timeEntry)
    {
        $this->authorize('update', $timeEntry);

        if ($timeEntry->stopped_at) {
            return response()->json(['message' => 'Timer already stopped'], 400);
        }

        $stoppedAt = Carbon::now();
        $duration = $stoppedAt->diffInMinutes($timeEntry->started_at);

        $timeEntry->update([
            'stopped_at' => $stoppedAt,
            'duration_minutes' => $duration
        ]);

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
