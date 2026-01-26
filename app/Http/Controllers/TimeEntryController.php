<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use App\Events\TimerDeleted;
use App\Services\TimerService;
use App\Http\Requests\StoreTimeEntryRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TimeEntryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected TimerService $timerService
    ) {}

    public function index(Request $request)
    {
        $query = $request->user()->timeEntries()->with('project.client');

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        return $query->orderBy('started_at', 'desc')->get();
    }

    public function store(StoreTimeEntryRequest $request)
    {
        $timeEntry = $this->timerService->startTimer(
            $request->user(),
            $request->validated('project_id'),
            $request->validated('description'),
            $request->validated('is_billable') ?? true
        );

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
                $validated['duration_minutes'] = TimerService::calculateDuration(
                    $validated['started_at'],
                    $validated['stopped_at']
                );
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

        try {
            $timeEntry = $this->timerService->stopTimer($timeEntry, $request->user());
            return response()->json($timeEntry->load('project.client'));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function restart(Request $request, TimeEntry $timeEntry)
    {
        $this->authorize('update', $timeEntry);

        try {
            $timeEntry = $this->timerService->restartTimer($timeEntry, $request->user());
            return response()->json($timeEntry->load('project.client'));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function active(Request $request)
    {
        $activeEntry = $this->timerService->getActiveTimer($request->user());
        return response()->json($activeEntry);
    }
}
