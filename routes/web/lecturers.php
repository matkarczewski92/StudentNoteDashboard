<?php

use App\Http\Controllers\LecturerMailController;
use App\Models\{Semester};
use Illuminate\Support\Facades\Route;

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/lecturers', [LecturerMailController::class,'index'])->name('lecturers.index');
    Route::get('/lecturers/{mail}', [LecturerMailController::class,'show'])->name('lecturers.show');
    Route::post('/lecturers', [LecturerMailController::class,'store'])->name('lecturers.store');
    Route::put('/lecturers/{mail}', [LecturerMailController::class,'update'])->name('lecturers.update');
    Route::delete('/lecturers/{mail}', [LecturerMailController::class,'destroy'])->name('lecturers.destroy');
});

