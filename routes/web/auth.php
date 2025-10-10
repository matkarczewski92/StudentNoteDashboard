<?php

use Illuminate\Support\Facades\Route;
// np. dodatkowe widoki logowania/rejestracji albo ratelimit:
Route::prefix('auth')->name('auth.')->group(function () {
    // Route::get('login', ...)->name('login');  // jeśli masz własne widoki
});
