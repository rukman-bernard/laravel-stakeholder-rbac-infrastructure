<?php

use App\Constants\Guards;
use App\Livewire\Employer\Dashboard\Dashboard as EmployerDashboard;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Employer Portal Routes
|--------------------------------------------------------------------------
|
| Guard: employer
| Middleware:
| - auth:employer
| - email.verified:employer
|
| All routes are:
| - Prefixed with /employer
| - Named employer.*
|
*/

Route::prefix(Guards::EMPLOYER)
    ->as(Guards::EMPLOYER . '.')
    ->middleware([
        'auth:' . Guards::EMPLOYER,
        'email.verified:' . Guards::EMPLOYER,
    ])
    ->group(function () {

        Route::get('/dashboard', EmployerDashboard::class)
            ->name('dashboard');

        // Future employer routes go here
    });