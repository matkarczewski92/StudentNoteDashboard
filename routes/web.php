<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    require __DIR__.'/web/profile.php';
    require __DIR__.'/web/schedule.php';
});

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});


require __DIR__.'/web/admin.php';
require __DIR__.'/auth.php';
