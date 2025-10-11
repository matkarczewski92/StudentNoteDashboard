<?php

use App\Http\Controllers\{NoteController, NoteCommentController};
use App\Models\{Subject, Semester};
use Illuminate\Support\Facades\Route;

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/notes', [NoteController::class,'index'])->name('notes.index');
    Route::get('/notes/{note}', [NoteController::class,'show'])->name('notes.show');
    Route::post('/notes', [NoteController::class,'store'])->name('notes.store');
    Route::put('/notes/{note}', [NoteController::class,'update'])->name('notes.update');
    Route::delete('/notes/{note}', [NoteController::class,'destroy'])->name('notes.destroy');
    Route::post('/notes/{note}/vote', [NoteController::class,'vote'])->name('notes.vote');
    Route::patch('/notes/{note}/toggle', [NoteController::class,'toggleHide'])->name('notes.toggle');

    // Comments
    Route::post('/notes/{note}/comments', [NoteCommentController::class,'store'])->name('note-comments.store');
    Route::delete('/note-comments/{comment}', [NoteCommentController::class,'destroy'])->name('note-comments.destroy');

    // AJAX: subjects by semester
    Route::get('/ajax/semesters/{semester}/subjects', function (Semester $semester) {
        return $semester->subjects()->select('id','name')->orderBy('name')->get();
    })->name('ajax.semester.subjects');
});

