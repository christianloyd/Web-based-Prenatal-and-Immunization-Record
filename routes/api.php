<?php

use App\Http\Controllers\PrenatalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Simplified API routes - just use auth middleware
Route::middleware('auth')->prefix('prenatal')->group(function () {
    Route::get('/', [PrenatalController::class, 'apiIndex']);
    Route::post('/', [PrenatalController::class, 'apiStore']);
    Route::get('/stats', [PrenatalController::class, 'getDashboardStats']);
    Route::get('/{id}', [PrenatalController::class, 'apiShow']);
    Route::put('/{id}', [PrenatalController::class, 'apiUpdate']);
    Route::delete('/{id}', [PrenatalController::class, 'apiDestroy']);
    Route::patch('/{id}/status', [PrenatalController::class, 'updateStatus']);
});