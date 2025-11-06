<?php

use App\Http\Controllers\PrenatalRecordController;
use App\Http\Controllers\PrenatalCheckupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ============================================================================
// API v1 Routes (Recommended)
// ============================================================================
Route::prefix('v1')->name('api.v1.')->group(function () {

    Route::middleware(['auth:sanctum', 'throttle:60,1'])->get('/user', function (Request $request) {
        return $request->user();
    });

    // Prenatal Records API with rate limiting
    Route::middleware(['auth', 'throttle:60,1'])->prefix('prenatal-records')->name('prenatal-records.')->group(function () {
        Route::get('/', [PrenatalRecordController::class, 'index'])->name('index');
        Route::post('/', [PrenatalRecordController::class, 'store'])->name('store');
        Route::get('/{id}', [PrenatalRecordController::class, 'show'])->name('show');
        Route::put('/{id}', [PrenatalRecordController::class, 'update'])->name('update');
        Route::delete('/{id}', [PrenatalRecordController::class, 'destroy'])->name('destroy');
    });

    // Prenatal Checkups API with rate limiting
    Route::middleware(['auth', 'throttle:60,1'])->prefix('prenatal-checkups')->name('prenatal-checkups.')->group(function () {
        Route::get('/', [PrenatalCheckupController::class, 'index'])->name('index');
        Route::post('/', [PrenatalCheckupController::class, 'store'])->name('store');
        Route::get('/{id}', [PrenatalCheckupController::class, 'show'])->name('show');
        Route::put('/{id}', [PrenatalCheckupController::class, 'update'])->name('update');
        Route::delete('/{id}', [PrenatalCheckupController::class, 'destroy'])->name('destroy');
    });
});

// ============================================================================
// Legacy API Routes (Backwards Compatibility)
// ============================================================================
// DEPRECATED: Use /api/v1/* endpoints instead
// These routes will be removed in a future version
Route::middleware(['auth:sanctum', 'throttle:60,1'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth', 'throttle:60,1'])->prefix('prenatal-records')->group(function () {
    Route::get('/', [PrenatalRecordController::class, 'index']);
    Route::post('/', [PrenatalRecordController::class, 'store']);
    Route::get('/{id}', [PrenatalRecordController::class, 'show']);
    Route::put('/{id}', [PrenatalRecordController::class, 'update']);
    Route::delete('/{id}', [PrenatalRecordController::class, 'destroy']);
});

Route::middleware(['auth', 'throttle:60,1'])->prefix('prenatal-checkups')->group(function () {
    Route::get('/', [PrenatalCheckupController::class, 'index']);
    Route::post('/', [PrenatalCheckupController::class, 'store']);
    Route::get('/{id}', [PrenatalCheckupController::class, 'show']);
    Route::put('/{id}', [PrenatalCheckupController::class, 'update']);
    Route::delete('/{id}', [PrenatalCheckupController::class, 'destroy']);
});