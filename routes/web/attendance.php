<?php

use App\Http\Controllers\Admin\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function(){
    Route::get('/attendance', [AttendanceController::class,'index'])->name('attendance.index');
    Route::get('/attendance/print', [AttendanceController::class,'print'])->name('attendance.print');
});

