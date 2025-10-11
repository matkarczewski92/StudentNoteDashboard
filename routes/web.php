<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', \App\Http\Controllers\DashboardController::class)->name('dashboard');
});

// Ładowanie plików routes/web/*.php automatycznie
foreach (glob(__DIR__.'/web/*.php') as $routeFile) {
    require $routeFile;
}

require __DIR__.'/auth.php';
    

