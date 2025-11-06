<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// RFID Attendance API Routes (no auth required for hardware integration)
Route::prefix('rfid')->group(function () {
    Route::post('/scan', [\App\Http\Controllers\Api\RFIDAttendanceController::class, 'scan']);
    Route::post('/status', [\App\Http\Controllers\Api\RFIDAttendanceController::class, 'status']);
    Route::get('/health', [\App\Http\Controllers\Api\RFIDAttendanceController::class, 'health']);
});
