<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Services\Auth\GuardResolver;
use App\Services\Auth\GuardRedirectService;
use App\Services\AdminLTE\AdminLTESettingsService;

class Authenticate
{
    public function __construct(
        private readonly GuardResolver $guardResolver,
        private readonly GuardRedirectService $redirectService,
        private readonly AdminLTESettingsService $adminLteService
    ) {}

    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        $activeGuard = $this->guardResolver->detect($guards);

        // -------------------------------------------------------------
        // 1) Authenticated branch
        // -------------------------------------------------------------
        if (is_string($activeGuard)) {

            // Apply AdminLTE settings only for GET requests
            if ($request->isMethod('get')) {
                $this->adminLteService->apply($request, $activeGuard);
            }

            return $next($request);
        }

        // -------------------------------------------------------------
        // 2) Unauthenticated API request
        // -------------------------------------------------------------
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Unauthenticated.',
            ], 401);
        }

        // -------------------------------------------------------------
        // 3) Unauthenticated Web request
        // -------------------------------------------------------------
        return $this->redirectService->redirectToLogin($request);
    }
}