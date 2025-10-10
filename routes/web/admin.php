<?php

use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

// Panel administratora
Route::middleware(['auth', 'role:admin'])->group(function () {

});

// Panel moderatora
Route::middleware(['auth', 'role:admin,moderator'])->group(function () {

});