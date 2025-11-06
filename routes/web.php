<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UnifiedLoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

// Teacher exports (scoped via middleware)
Route::middleware(['web', 'auth', 'role:teacher'])->group(function () {
    Route::prefix('teacher/exports')->name('teacher.exports.')->group(function () {
        Route::get('attendance.csv', [\App\Http\Controllers\Teacher\AttendanceExportController::class, 'allCsv'])->name('attendance.csv');
        Route::get('attendance.pdf', [\App\Http\Controllers\Teacher\AttendanceExportController::class, 'allPdf'])->name('attendance.pdf');
        Route::get('incidents.csv', [\App\Http\Controllers\Teacher\IncidentExportController::class, 'allCsv'])->name('incidents.csv');
        Route::get('incidents.pdf', [\App\Http\Controllers\Teacher\IncidentExportController::class, 'allPdf'])->name('incidents.pdf');
    });
});
Route::get('/login', [UnifiedLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UnifiedLoginController::class, 'login']);
Route::post('/logout', [UnifiedLoginController::class, 'logout'])->name('logout');
Route::get('/logout', [UnifiedLoginController::class, 'logout']);
