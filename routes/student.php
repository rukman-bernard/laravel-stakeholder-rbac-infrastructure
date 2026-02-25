<?php

use App\Constants\Guards;
use App\Livewire\Student\Dashboard\Dashboard as StudentDashboard;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Student Portal Routes
|--------------------------------------------------------------------------
|
| Guard: student
| Middleware:
| - auth:student
| - email.verified:student
|
| All routes are:
| - Prefixed with /student
| - Named student.*
|
*/

Route::prefix(Guards::STUDENT)
    ->as(Guards::STUDENT . '.')
    ->middleware([
        'auth:' . Guards::STUDENT,
        'email.verified:' . Guards::STUDENT,
    ])
    ->group(function () {

        Route::get('/dashboard', StudentDashboard::class)
            ->name('dashboard');

        // Future student routes go here
    });