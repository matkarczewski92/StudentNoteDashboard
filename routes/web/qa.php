<?php

use App\Http\Controllers\{QuestionController, AnswerController, AnswerCommentController};
use Illuminate\Support\Facades\Route;

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/questions', [QuestionController::class,'index'])->name('questions.index');
    Route::get('/questions/{question}', [QuestionController::class,'show'])->name('questions.show');
    Route::post('/questions', [QuestionController::class,'store'])->name('questions.store');
    Route::put('/questions/{question}', [QuestionController::class,'update'])->name('questions.update');
    Route::delete('/questions/{question}', [QuestionController::class,'destroy'])->name('questions.destroy');

    Route::post('/questions/{question}/answers', [AnswerController::class,'store'])->name('answers.store');
    Route::put('/answers/{answer}', [AnswerController::class,'update'])->name('answers.update');
    Route::delete('/answers/{answer}', [AnswerController::class,'destroy'])->name('answers.destroy');
    Route::post('/answers/{answer}/vote', [AnswerController::class,'vote'])->name('answers.vote');

    // Komentarze do odpowiedzi (z odpowiedziami)
    Route::post('/answers/{answer}/comments', [AnswerCommentController::class,'store'])->name('answer-comments.store');
    Route::delete('/answer-comments/{comment}', [AnswerCommentController::class,'destroy'])->name('answer-comments.destroy');



});
