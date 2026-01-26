<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);

Route::prefix('widget')->group(function () {
    Route::get('/active-timer', [App\Http\Controllers\WidgetController::class, 'activeTimer']);
    Route::get('/projects', [App\Http\Controllers\WidgetController::class, 'projects']);
    Route::get('/time-entries', [App\Http\Controllers\WidgetController::class, 'timeEntries']);
    Route::post('/time-entries', [App\Http\Controllers\WidgetController::class, 'startTimer']);
    Route::post('/time-entries/{timeEntry}/stop', [App\Http\Controllers\WidgetController::class, 'stopTimer']);
    Route::post('/time-entries/{timeEntry}/restart', [App\Http\Controllers\WidgetController::class, 'restartTimer']);
    Route::put('/time-entries/{timeEntry}', [App\Http\Controllers\WidgetController::class, 'updateEntry']);
    Route::delete('/time-entries/{timeEntry}', [App\Http\Controllers\WidgetController::class, 'deleteEntry']);
    Route::get('/clients', [App\Http\Controllers\WidgetController::class, 'clients']);
    Route::get('/clients/{client}/projects', [App\Http\Controllers\WidgetController::class, 'clientProjects']);
    Route::get('/settings', [App\Http\Controllers\WidgetController::class, 'settings']);
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

    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'show']);
    Route::put('/settings', [App\Http\Controllers\SettingsController::class, 'update']);
});
