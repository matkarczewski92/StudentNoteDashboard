<?php

use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\SemesterAdminController;
use App\Http\Controllers\Admin\SubjectAdminController;
use App\Http\Controllers\Admin\GroupAdminController;
use App\Http\Controllers\Admin\AttendanceController;
use Illuminate\Support\Facades\Route;

// Panel administratora
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Users
    Route::get('/users', [UserAdminController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserAdminController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserAdminController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserAdminController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/reset-password', [UserAdminController::class, 'resetPassword'])->name('users.reset');

    // Dictionaries
    Route::get('/semesters', [SemesterAdminController::class,'index'])->name('semesters.index');
    Route::post('/semesters', [SemesterAdminController::class,'store'])->name('semesters.store');
    Route::put('/semesters/{semester}', [SemesterAdminController::class,'update'])->name('semesters.update');
    Route::delete('/semesters/{semester}', [SemesterAdminController::class,'destroy'])->name('semesters.destroy');

    Route::get('/subjects', [SubjectAdminController::class,'index'])->name('subjects.index');
    Route::post('/subjects', [SubjectAdminController::class,'store'])->name('subjects.store');
    Route::put('/subjects/{subject}', [SubjectAdminController::class,'update'])->name('subjects.update');
    Route::delete('/subjects/{subject}', [SubjectAdminController::class,'destroy'])->name('subjects.destroy');

    Route::get('/groups', [GroupAdminController::class,'index'])->name('groups.index');
    Route::post('/groups', [GroupAdminController::class,'store'])->name('groups.store');
    Route::put('/groups/{group}', [GroupAdminController::class,'update'])->name('groups.update');
    Route::delete('/groups/{group}', [GroupAdminController::class,'destroy'])->name('groups.destroy');
    Route::post('/users/bulk-add-to-group', [UserAdminController::class, 'bulkAddToGroup'])->name('users.bulk_add');
});

// Panel moderatora
Route::middleware(['auth', 'role:admin,moderator'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/attendance', [AttendanceController::class,'index'])->name('attendance.index');
    Route::get('/attendance/print', [AttendanceController::class,'print'])->name('attendance.print');
});
