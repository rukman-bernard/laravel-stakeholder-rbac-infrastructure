<?php

use App\Http\Controllers\Auth\CommonLogoutController;
use App\Livewire\Shared\ChangePasswordForm;
use App\Livewire\Shared\UserProfile;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Shared Routes (All Session-Based Portals)
|--------------------------------------------------------------------------
|
| These routes are available to any authenticated session-based user.
| Guard resolution is handled by the custom Authenticate middleware,
| which uses GuardResolver to detect the active session guard.
|
*/

$sharedAuthMiddleware = [
    'web',
    'auth',
];

$sharedVerifiedMiddleware = [
    ...$sharedAuthMiddleware,
    'email.verified',
];

/*
|--------------------------------------------------------------------------
| Verified shared routes
|--------------------------------------------------------------------------
*/
Route::middleware($sharedVerifiedMiddleware)->group(function () {
    Route::get('/change-password', ChangePasswordForm::class)
        ->name('change-password');

    Route::get('/profile', UserProfile::class)
        ->name('profile');
});

/*
|--------------------------------------------------------------------------
| Authenticated shared routes
|--------------------------------------------------------------------------
|
| These routes require authentication but must remain accessible even if
| the user's email is not yet verified.
|
*/
Route::middleware($sharedAuthMiddleware)->group(function () {
    Route::post('/logout', CommonLogoutController::class)
        ->name('logout');

    Route::view('/auth/reset', 'errors.session-reset')
        ->name('auth.reset');
});

