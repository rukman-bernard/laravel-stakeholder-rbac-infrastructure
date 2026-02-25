<?php

use App\Livewire\Shared\ChangePasswordForm;
use App\Livewire\Shared\UserProfile;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Shared Routes (All Session-Based Portals)
|--------------------------------------------------------------------------
|
| These routes are accessible to *any* authenticated user under the
| single-session model (web, student, employer, etc.).
|
| Important:
| - We do NOT use "auth:{guard}" here because "auth" accepts only ONE guard.
| - Instead, we rely on the application's guard-resolving middleware which
|   detects the currently active session guard and authenticates accordingly.
|
*/

Route::middleware([
        'web',

        // Your custom Authenticate middleware resolves the active session guard
        // (based on your GuardResolver + single-session behaviour)
        'auth',

        // Your custom verification middleware supports multiple guards internally
        // when no explicit guard is passed (defaults to session guards).
        'email.verified',
    ])
    ->group(function () {

        Route::get('/change-password', ChangePasswordForm::class)
            ->name('change-password');

        Route::get('/profile', UserProfile::class)
            ->name('profile');
    });