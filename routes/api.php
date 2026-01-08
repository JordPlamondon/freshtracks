<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\TimeEntry;
use App\Models\Project;
use App\Events\TimerStarted;
use App\Events\TimerStopped;
use Carbon\Carbon;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);

Route::prefix('widget')->group(function () {
    $checkWidgetAuth = function (Request $request) {
        $secret = $request->header('X-Widget-Secret');
        $expectedSecret = config('app.widget_secret', 'freshtracks-widget-dev-secret');

        if ($secret !== $expectedSecret) {
            abort(401, 'Invalid widget secret');
        }

        $userId = config('app.widget_user_id', 1);
        return User::findOrFail($userId);
    };

    Route::get('/active-timer', function (Request $request) use ($checkWidgetAuth) {
        $user = $checkWidgetAuth($request);

        $activeEntry = $user->timeEntries()
            ->whereNull('stopped_at')
            ->with('project.client')
            ->first();

        return response()->json($activeEntry);
    });

    Route::get('/projects', function (Request $request) use ($checkWidgetAuth) {
        $user = $checkWidgetAuth($request);

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
    });

    Route::get('/time-entries', function (Request $request) use ($checkWidgetAuth) {
        $user = $checkWidgetAuth($request);

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $entries = $user->timeEntries()
            ->with('project.client')
            ->whereBetween('started_at', [$startOfWeek, $endOfWeek])
            ->orderByDesc('started_at')
            ->get();

        return response()->json($entries);
    });

    Route::post('/time-entries/{timeEntry}/restart', function (Request $request, TimeEntry $timeEntry) use ($checkWidgetAuth) {
        $user = $checkWidgetAuth($request);

        if ($timeEntry->user_id !== $user->id) {
            abort(403, 'Not authorized');
        }

        $activeEntry = $user->timeEntries()->whereNull('stopped_at')->first();
        if ($activeEntry && $activeEntry->id !== $timeEntry->id) {
            $stoppedAt = Carbon::now();
            $sessionStart = $activeEntry->resumed_at
                ? Carbon::parse($activeEntry->resumed_at)
                : Carbon::parse($activeEntry->started_at);
            $sessionDuration = abs($sessionStart->diffInMinutes($stoppedAt));
            $totalDuration = ($activeEntry->duration_minutes ?? 0) + $sessionDuration;

            $activeEntry->update([
                'stopped_at' => $stoppedAt,
                'duration_minutes' => $totalDuration
            ]);

            broadcast(new TimerStopped($activeEntry->fresh(), $user->id));
        }

        $timeEntry->update([
            'stopped_at' => null,
            'resumed_at' => Carbon::now(),
        ]);

        broadcast(new TimerStarted($timeEntry->fresh(), $user->id));

        return response()->json($timeEntry->load('project.client'));
    });

    Route::post('/time-entries', function (Request $request) use ($checkWidgetAuth) {
        $user = $checkWidgetAuth($request);

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'description' => 'nullable|string',
        ]);

        $activeEntry = $user->timeEntries()->whereNull('stopped_at')->first();
        if ($activeEntry) {
            $stoppedAt = Carbon::now();
            $sessionStart = $activeEntry->resumed_at
                ? Carbon::parse($activeEntry->resumed_at)
                : Carbon::parse($activeEntry->started_at);
            $sessionDuration = abs($sessionStart->diffInMinutes($stoppedAt));
            $totalDuration = ($activeEntry->duration_minutes ?? 0) + $sessionDuration;

            $activeEntry->update([
                'stopped_at' => $stoppedAt,
                'duration_minutes' => $totalDuration
            ]);

            broadcast(new TimerStopped($activeEntry->fresh(), $user->id));
        }

        $timeEntry = TimeEntry::create([
            'user_id' => $user->id,
            'project_id' => $validated['project_id'],
            'description' => $validated['description'] ?? null,
            'started_at' => Carbon::now(),
            'resumed_at' => Carbon::now(),
            'duration_minutes' => 0,
            'is_billable' => true,
        ]);

        broadcast(new TimerStarted($timeEntry, $user->id));

        return response()->json($timeEntry->load('project.client'), 201);
    });

    Route::post('/time-entries/{timeEntry}/stop', function (Request $request, TimeEntry $timeEntry) use ($checkWidgetAuth) {
        $user = $checkWidgetAuth($request);

        if ($timeEntry->user_id !== $user->id) {
            abort(403, 'Not authorized');
        }

        if ($timeEntry->stopped_at) {
            return response()->json(['message' => 'Timer already stopped'], 400);
        }

        $stoppedAt = Carbon::now();
        $sessionStart = $timeEntry->resumed_at
            ? Carbon::parse($timeEntry->resumed_at)
            : Carbon::parse($timeEntry->started_at);
        $sessionDuration = abs($sessionStart->diffInMinutes($stoppedAt));
        $totalDuration = ($timeEntry->duration_minutes ?? 0) + $sessionDuration;

        $timeEntry->update([
            'stopped_at' => $stoppedAt,
            'duration_minutes' => $totalDuration
        ]);

        broadcast(new TimerStopped($timeEntry->fresh(), $user->id));

        return response()->json($timeEntry->load('project.client'));
    });

    Route::delete('/time-entries/{timeEntry}', function (Request $request, TimeEntry $timeEntry) use ($checkWidgetAuth) {
        $user = $checkWidgetAuth($request);

        if ($timeEntry->user_id !== $user->id) {
            abort(403, 'Not authorized');
        }

        $entryId = $timeEntry->id;
        $timeEntry->delete();

        broadcast(new \App\Events\TimerDeleted($entryId, $user->id));

        return response()->json(['message' => 'Entry deleted'], 200);
    });

    Route::put('/time-entries/{timeEntry}', function (Request $request, TimeEntry $timeEntry) use ($checkWidgetAuth) {
        $user = $checkWidgetAuth($request);

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
    });

    Route::get('/clients', function (Request $request) use ($checkWidgetAuth) {
        $user = $checkWidgetAuth($request);

        $clients = \App\Models\Client::orderBy('name')->get();

        return response()->json($clients);
    });

    Route::get('/clients/{client}/projects', function (Request $request, \App\Models\Client $client) use ($checkWidgetAuth) {
        $user = $checkWidgetAuth($request);

        $projects = $client->projects()->orderBy('name')->get();

        return response()->json($projects);
    });

    Route::get('/settings', function (Request $request) use ($checkWidgetAuth) {
        $user = $checkWidgetAuth($request);

        $settings = $user->settings ?? [];

        return response()->json($settings);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);

    Route::apiResource('clients', App\Http\Controllers\ClientController::class);
    Route::apiResource('projects', App\Http\Controllers\ProjectController::class);

    Route::apiResource('time-entries', App\Http\Controllers\TimeEntryController::class);
    Route::post('/time-entries/{timeEntry}/stop', [App\Http\Controllers\TimeEntryController::class, 'stop']);
    Route::post('/time-entries/{timeEntry}/restart', [App\Http\Controllers\TimeEntryController::class, 'restart']);
    Route::get('/active-timer', [App\Http\Controllers\TimeEntryController::class, 'active']);

    Route::apiResource('invoices', App\Http\Controllers\InvoiceController::class);
    Route::post('/invoices/generate', [App\Http\Controllers\InvoiceController::class, 'generate']);
    Route::get('/settings', function (Request $request) {
        $user = $request->user();
        $settings = $user->settings ?? [];

        return response()->json($settings);
    });

    Route::put('/settings', function (Request $request) {
        $user = $request->user();

        $validated = $request->validate([
            'show_live_revenue' => 'sometimes|boolean',
        ]);

        $currentSettings = $user->settings ?? [];
        $newSettings = array_merge($currentSettings, $validated);

        $user->update(['settings' => $newSettings]);

        return response()->json($newSettings);
    });
});
