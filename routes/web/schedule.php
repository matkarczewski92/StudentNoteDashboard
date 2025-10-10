<?php

use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::prefix('schedule')->name('schedule.')->group(function () {
    Route::get('/', [ScheduleController::class, 'index'])->name('index');
    Route::post('/', [ScheduleController::class, 'store'])->name('store');
    Route::get('{event}', [ScheduleController::class, 'show'])->name('show');
    Route::put('{event}', [ScheduleController::class, 'update'])->name('update');
    Route::delete('{event}', [ScheduleController::class, 'destroy'])->name('destroy');
});
