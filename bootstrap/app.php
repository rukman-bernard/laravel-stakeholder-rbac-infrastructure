<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\EnsureGuardEmailIsVerified;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;


// Import Spatie middleware classes
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:[
            __DIR__.'/../routes/web.php',
            __DIR__.'/../routes/auth.php',
            __DIR__.'/../routes/shared.php',
            __DIR__.'/../routes/sysadmin.php',
            __DIR__.'/../routes/admin.php',
            __DIR__.'/../routes/student.php',
            __DIR__.'/../routes/employer.php',

        
        ], 
        health: '/up',
    ) ->withSchedule(function (Schedule $schedule) {


    })->withMiddleware(function (Middleware $middleware) {
        
        $middleware->alias([
            'auth' => Authenticate::class, // Register your Authenticate middleware
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'email.verified' => EnsureGuardEmailIsVerified::class,
            'redirect.loggedin' => \App\Http\Middleware\RedirectLoggedInToDashboard::class,
        ]);

        $middleware->web(append: [

        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Routing\Exceptions\InvalidSignatureException $e, \Illuminate\Http\Request $request) {
            $guard = \App\Constants\Guards::normalize($request->segment(1));

            if (! $guard || ! \App\Constants\Guards::isPortal($guard)) {
                $guard = \App\Constants\Guards::WEB;
            }

            $loginRoute = $guard === \App\Constants\Guards::WEB
                ? 'login'
                : "{$guard}.login";

            $resendRoute = $guard === \App\Constants\Guards::WEB
                ? 'verification.notice'
                : "{$guard}.verification.notice";

            return response()->view('auth.verification-link-invalid', [
                'guard' => $guard,
                'loginRoute' => $loginRoute,
                'resendNoticeRoute' => $resendRoute,
            ], 403);
        });
    })->withCommands([

    
    ])
->create();
