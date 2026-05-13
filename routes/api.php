<?php

use App\Http\Controllers\AIController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Define the login route for Sanctum
Route::post('/login', [AuthController::class, 'login'])->name('login');

// ── Public ───────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
});

// ── Protected ────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::prefix('ai')->group(function () {
        Route::post('chat', [AIController::class, 'chat']);
        Route::post('tip', [AIController::class, 'tip']);
    });

    Route::prefix('tasks')->group(function () {
        Route::get('stats',              [TaskController::class, 'stats']);
        Route::get('/',                  [TaskController::class, 'index']);
        Route::post('/',                 [TaskController::class, 'store']);
        Route::get('{id}',               [TaskController::class, 'show']);
        Route::put('{id}',               [TaskController::class, 'update']);
        Route::delete('{id}',            [TaskController::class, 'destroy']);
        Route::patch('{id}/complete',    [TaskController::class, 'complete']);
        Route::patch('{id}/in-progress', [TaskController::class, 'inProgress']);
    });

    Route::prefix('users')->group(function () {
        Route::get('me',  [UserController::class, 'show']);
        Route::put('me',  [UserController::class, 'update']);
    });
});

// Add a fallback for unmatched API routes
Route::fallback(function () {
    return response()->json([
        'status' => 404,
        'error' => 'Not Found',
        'message' => 'API endpoint not found',
        'timestamp' => now()->toIso8601String(),
    ], 404);
})->prefix('api');