<?php

use App\Http\Controllers\PollController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/polls', [PollController::class,'index'])->name('polls.index');
    Route::get('/polls/{poll}', [PollController::class,'show'])->name('polls.show');
    Route::post('/polls', [PollController::class,'store'])->name('polls.store');
    Route::put('/polls/{poll}', [PollController::class,'update'])->name('polls.update');
    Route::delete('/polls/{poll}', [PollController::class,'destroy'])->name('polls.destroy');

    // Opcje ankiet
    Route::post('/polls/{poll}/options', [PollController::class,'addOption'])->name('poll_options.store');
    Route::delete('/polls/{poll}/options/{option}', [PollController::class,'removeOption'])->name('poll_options.destroy');

    // GĹ‚osowanie i statystyki (live)
    Route::post('/polls/{poll}/vote', [PollController::class,'vote'])->name('polls.vote');
    Route::get('/polls/{poll}/stats', [PollController::class,'stats'])->name('polls.stats');
    Route::get('/polls/{poll}/me', [PollController::class,'me'])->name('polls.me');
});


