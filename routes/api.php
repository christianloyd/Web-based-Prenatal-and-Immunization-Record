<?php

use App\Http\Controllers\PrenatalRecordController;
use App\Http\Controllers\PrenatalCheckupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Prenatal Records API
Route::middleware('auth')->prefix('prenatal-records')->group(function () {
    Route::get('/', [PrenatalRecordController::class, 'index']);
    Route::post('/', [PrenatalRecordController::class, 'store']);
    Route::get('/{id}', [PrenatalRecordController::class, 'show']);
    Route::put('/{id}', [PrenatalRecordController::class, 'update']);
    Route::delete('/{id}', [PrenatalRecordController::class, 'destroy']);
});

// Prenatal Checkups API
Route::middleware('auth')->prefix('prenatal-checkups')->group(function () {
    Route::get('/', [PrenatalCheckupController::class, 'index']);
    Route::post('/', [PrenatalCheckupController::class, 'store']);
    Route::get('/{id}', [PrenatalCheckupController::class, 'show']);
    Route::put('/{id}', [PrenatalCheckupController::class, 'update']);
    Route::delete('/{id}', [PrenatalCheckupController::class, 'destroy']);
});