<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
    
    // Clients
    Route::apiResource('clients', App\Http\Controllers\ClientController::class);
    
    // Projects
    Route::apiResource('projects', App\Http\Controllers\ProjectController::class);
    
    // Time Entries
    Route::apiResource('time-entries', App\Http\Controllers\TimeEntryController::class);
    Route::post('/time-entries/{timeEntry}/stop', [App\Http\Controllers\TimeEntryController::class, 'stop']);
    Route::get('/active-timer', [App\Http\Controllers\TimeEntryController::class, 'active']);
    
    // Invoices
    Route::apiResource('invoices', App\Http\Controllers\InvoiceController::class);
    Route::post('/invoices/generate', [App\Http\Controllers\InvoiceController::class, 'generate']);
});
