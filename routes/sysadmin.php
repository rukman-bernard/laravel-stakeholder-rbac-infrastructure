<?php

use App\Constants\Guards;
use App\Constants\Roles;
use App\Livewire\Sysadmin\Dashboard\Dashboard as SysadminDashboard;
use App\Livewire\Sysadmin\Spatie\PermissionIndex;
use App\Livewire\Sysadmin\Spatie\RoleForm;
use App\Livewire\Sysadmin\Spatie\RoleIndex;
use App\Livewire\Sysadmin\Spatie\UserForm;
use App\Livewire\Sysadmin\Spatie\UserIndex;
use App\Livewire\Sysadmin\Spatie\UserPermissionsForm;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sysadmin Portal Routes
|--------------------------------------------------------------------------
|
| Guard: web (internal users)
| Role:  sysadmin
|
| Middleware:
| - auth:web
| - email.verified:web
| - role:sysadmin
|
| All routes are:
| - Prefixed with /sysadmin
| - Named sysadmin.*
|
*/

Route::prefix('sysadmin')
    ->as('sysadmin.')
    ->middleware([
        'auth:' . Guards::WEB,
        'email.verified:' . Guards::WEB,
        'role:' . Roles::SYSTEM_ADMIN,
    ])
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', SysadminDashboard::class)
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */
        Route::get('/users', UserIndex::class)
            ->name('users');

        Route::get('/users/create', UserForm::class)
            ->name('users.create');

        // Showing an edit form must be GET (NOT PUT)
        Route::get('/users/{user}/edit', UserForm::class)
            ->name('users.edit');

        Route::get('/users/{user}/permissions', UserPermissionsForm::class)
            ->name('users.permissions');

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */
        Route::get('/roles', RoleIndex::class)
            ->name('roles');

        Route::get('/roles/create', RoleForm::class)
            ->name('roles.create');

        Route::get('/roles/{role}/edit', RoleForm::class)
            ->name('roles.edit');

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */
        Route::get('/permissions', PermissionIndex::class)
            ->name('permissions');
    });