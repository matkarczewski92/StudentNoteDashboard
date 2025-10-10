<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    require __DIR__.'/web/profile.php';
    require __DIR__.'/web/schedule.php';
    require __DIR__.'/web/qa.php';
});



require __DIR__.'/web/admin.php';
require __DIR__.'/auth.php';
    

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
