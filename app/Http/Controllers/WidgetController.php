<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TimeEntry;
use App\Models\Project;
use App\Models\Client;
use App\Services\TimerService;
use App\Events\TimerStarted;
use App\Events\TimerStopped;
use App\Events\TimerDeleted;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WidgetController extends Controller
{
    public function __construct(
        protected TimerService $timerService
    ) {}

    protected function getWidgetUser(Request $request): User
    {
        $secret = $request->header('X-Widget-Secret');
        $expectedSecret = config('app.widget_secret', 'freshtracks-widget-dev-secret');

        if ($secret !== $expectedSecret) {
            abort(401, 'Invalid widget secret');
        }

        $userId = config('app.widget_user_id', 1);
        return User::findOrFail($userId);
    }

    public function activeTimer(Request $request)
    {
        $user = $this->getWidgetUser($request);
        return response()->json($this->timerService->getActiveTimer($user));
    }

    public function projects(Request $request)
    {
        $user = $this->getWidgetUser($request);

        $projects = Project::whereHas('timeEntries', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with('client')
            ->orderByDesc(function ($q) use ($user) {
                $q->select('started_at')
                    ->from('time_entries')
                    ->whereColumn('project_id', 'projects.id')
                    ->where('user_id', $user->id)
                    ->orderByDesc('started_at')
                    ->limit(1);
            })
            ->take(5)
            ->get();

        return response()->json($projects);
    }

    public function timeEntries(Request $request)
    {
        $user = $this->getWidgetUser($request);

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $entries = $user->timeEntries()
            ->with('project.client')
            ->whereBetween('started_at', [$startOfWeek, $endOfWeek])
            ->orderByDesc('started_at')
            ->get();

        return response()->json($entries);
    }

    public function restartTimer(Request $request, TimeEntry $timeEntry)
    {
        $user = $this->getWidgetUser($request);

        if ($timeEntry->user_id !== $user->id) {
            abort(403, 'Not authorized');
        }

        $activeEntry = $user->timeEntries()->whereNull('stopped_at')->first();
        if ($activeEntry && $activeEntry->id !== $timeEntry->id) {
            $this->timerService->stopTimer($activeEntry, $user);
        }

        return response()->json(
            $this->timerService->restartTimer($timeEntry, $user)->load('project.client')
        );
    }

    public function startTimer(Request $request)
    {
        $user = $this->getWidgetUser($request);

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'description' => 'nullable|string',
        ]);

        $activeEntry = $user->timeEntries()->whereNull('stopped_at')->first();
        if ($activeEntry) {
            $this->timerService->stopTimer($activeEntry, $user);
        }

        $timeEntry = $this->timerService->startTimer(
            $user,
            $validated['project_id'],
            $validated['description'] ?? null
        );

        return response()->json($timeEntry->load('project.client'), 201);
    }

    public function stopTimer(Request $request, TimeEntry $timeEntry)
    {
        $user = $this->getWidgetUser($request);

        if ($timeEntry->user_id !== $user->id) {
            abort(403, 'Not authorized');
        }

        try {
            $timeEntry = $this->timerService->stopTimer($timeEntry, $user);
            return response()->json($timeEntry->load('project.client'));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function deleteEntry(Request $request, TimeEntry $timeEntry)
    {
        $user = $this->getWidgetUser($request);

        if ($timeEntry->user_id !== $user->id) {
            abort(403, 'Not authorized');
        }

        $entryId = $timeEntry->id;
        $timeEntry->delete();

        broadcast(new TimerDeleted($entryId, $user->id));

        return response()->json(['message' => 'Entry deleted'], 200);
    }

    public function updateEntry(Request $request, TimeEntry $timeEntry)
    {
        $user = $this->getWidgetUser($request);

        if ($timeEntry->user_id !== $user->id) {
            abort(403, 'Not authorized');
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'description' => 'nullable|string',
            'started_at' => 'required|date',
            'stopped_at' => 'nullable|date',
            'is_billable' => 'required|boolean',
        ]);

        $durationMinutes = null;
        if ($validated['stopped_at']) {
            $startedAt = Carbon::parse($validated['started_at']);
            $stoppedAt = Carbon::parse($validated['stopped_at']);
            $durationMinutes = abs($startedAt->diffInMinutes($stoppedAt));
        }

        $timeEntry->update([
            'project_id' => $validated['project_id'],
            'description' => $validated['description'],
            'started_at' => $validated['started_at'],
            'stopped_at' => $validated['stopped_at'],
            'duration_minutes' => $durationMinutes,
            'is_billable' => $validated['is_billable'],
        ]);

        if ($timeEntry->stopped_at) {
            broadcast(new TimerStopped($timeEntry->fresh(), $user->id));
        } else {
            broadcast(new TimerStarted($timeEntry->fresh(), $user->id));
        }

        return response()->json($timeEntry->load('project.client'));
    }

    public function clients(Request $request)
    {
        $this->getWidgetUser($request);
        return response()->json(Client::orderBy('name')->get());
    }

    public function clientProjects(Request $request, Client $client)
    {
        $this->getWidgetUser($request);
        return response()->json($client->projects()->orderBy('name')->get());
    }

    public function settings(Request $request)
    {
        $user = $this->getWidgetUser($request);
        return response()->json($user->settings ?? []);
    }
}
