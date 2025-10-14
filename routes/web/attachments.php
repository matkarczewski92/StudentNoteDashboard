<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttachmentController;

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/attachments/notes/{att}', [AttachmentController::class, 'note'])->name('attachments.notes.show');
    Route::get('/attachments/lecturers/{att}', [AttachmentController::class, 'lecturer'])->name('attachments.lecturers.show');
});

