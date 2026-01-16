<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WidgetController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);

Route::prefix('widget')->group(function () {
    Route::get('/active-timer', [WidgetController::class, 'activeTimer']);
    Route::get('/projects', [WidgetController::class, 'projects']);
    Route::get('/time-entries', [WidgetController::class, 'timeEntries']);
    Route::post('/time-entries', [WidgetController::class, 'startTimer']);
    Route::post('/time-entries/{timeEntry}/stop', [WidgetController::class, 'stopTimer']);
    Route::post('/time-entries/{timeEntry}/restart', [WidgetController::class, 'restartTimer']);
    Route::put('/time-entries/{timeEntry}', [WidgetController::class, 'updateEntry']);
    Route::delete('/time-entries/{timeEntry}', [WidgetController::class, 'deleteEntry']);
    Route::get('/clients', [WidgetController::class, 'clients']);
    Route::get('/clients/{client}/projects', [WidgetController::class, 'clientProjects']);
    Route::get('/settings', [WidgetController::class, 'settings']);
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
