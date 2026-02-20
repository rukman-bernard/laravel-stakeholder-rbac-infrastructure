<?php

use App\Constants\Guards;
use App\Constants\Roles;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| These routes are accessible only to authenticated and verified users
| under the WEB guard who hold either ADMIN or SUPER_ADMIN roles.
|
| This section represents the administrative control layer of the system.
|
*/

Route::middleware([
    'auth:' . Guards::WEB,
    'email.verified:' . Guards::WEB,
    'role:' . Roles::ADMIN . '|' . Roles::SUPER_ADMIN,
])
->prefix('admin')
->as('admin.')
->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Admin Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', \App\Livewire\Admin\Dashboard\Dashboard::class)
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Additional Admin Routes
    |--------------------------------------------------------------------------
    |
    | Register new administrative Livewire components here.
    | Example:
    |
    | Route::get('/users', UserManager::class)->name('users.index');
    |
    */
});
