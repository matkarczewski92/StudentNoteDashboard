<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Livewire\Notes\NotesManager;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Panel administratora
Route::middleware(['auth', 'role:admin'])->group(function () {

});

// Panel moderatora
Route::middleware(['auth', 'role:admin,moderator'])->group(function () {

});
// Dostęp dla wszystkich zalogowanych użytkowników
Route::middleware(['auth'])->group(function () {
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/schedule', [ScheduleController::class, 'store'])->name('schedule.store');
    Route::put('/schedule/{event}', [ScheduleController::class, 'update'])->name('schedule.update');
    Route::delete('/schedule/{event}', [ScheduleController::class, 'destroy'])->name('schedule.destroy');
});


require __DIR__.'/auth.php';
