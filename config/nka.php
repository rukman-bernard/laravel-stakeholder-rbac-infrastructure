<?php

use App\Constants\Roles;


return [

    
    /*
    |--------------------------------------------------------------------------
    | Authentication & Access
    |--------------------------------------------------------------------------
    */
    'auth' => [

        // Default fallback route for unauthenticated web requests
        'unauthenticated_redirect_route' => 'portal.hub',

        /*
        |----------------------------------------------------------------------
        | Dashboard Routes
        |----------------------------------------------------------------------
        | Supports:
        | - string route name for simple guards
        | - role-based map for guards like 'web'
        */
        'dashboard_routes' => [
            'web' => [
                'roles' => [
                    Roles::SYSTEM_ADMIN     => 'sysadmin.dashboard',
                    Roles::SUPER_ADMIN      => 'admin.dashboard',
                    Roles::ADMIN            => 'admin.dashboard',
                ],
                'default' => 'auth.reset',
            ],

            'student'  => 'student.dashboard',
            'employer' => 'employer.dashboard',
        ],

        // Role priority per guard (used when resolving a dashboard role)
        'dashboard_role_priority' => [
            'web' => ['sysadmin', 'superadmin', 'admin'],
        ],

        // Password broker name per guard (must exist in config/auth.php -> passwords)
        'password_brokers' => [
            'web'      => 'users',
            'student'  => 'students',
            'employer' => 'employers',
        ],

        'reset_password' => [
            'labels' => [
                'student'  => 'NKA Student',
                'employer' => 'NKA Employer',
                'web'      => 'NKA Internal User',
            ],
            'subjects' => [
                'student'  => 'Reset Your NKA Student Account Password',
                'employer' => 'Reset Your NKA Employer Account Password',
                'web'      => 'Reset Your NKA Internal User Account Password',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | UI / AdminLTE
    |--------------------------------------------------------------------------
    */
    'ui' => [

        // Theme/skin asset mapping per guard (paths are project-specific)
        'skins' => [
            'web'      => '', // Internal users use default AdminLTE styles
            'student'  => 'resources/scss/skins/student/student.scss',
            'employer' => 'resources/css/skins/employer.css',
        ],

        'js'=> [
            'web' => 'resources/js/shared/library.js',
        ],

        /*
        |----------------------------------------------------------------------
        | AdminLTE Overrides by Guard
        |----------------------------------------------------------------------
        | These are applied at runtime (e.g. AdminLTESettingsService).
        | Keep them as AdminLTE config keys => values.
        */
        'adminlte_overrides_by_guard' => [
            'student' => [
                'adminlte.layout_topnav' => true,
                'adminlte.classes_body'  => 'glassmorphism-theme',
            ],
            'employer' => [
                'adminlte.layout_topnav' => true,
            ],
        ],
    ],

];
