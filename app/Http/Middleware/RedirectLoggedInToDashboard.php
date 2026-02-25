<?php

namespace App\Http\Middleware;

use App\Services\Auth\DashboardResolver;
use App\Services\Auth\GuardResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

final class RedirectLoggedInToDashboard
{
    public function __construct(
        private readonly GuardResolver $guardResolver,
        private readonly DashboardResolver $dashboardResolver,
    ) {}

    /**
     * If a session-authenticated user hits a guest-only page (e.g., login),
     * redirect them to the correct dashboard for their active guard/role.
     *
     * This supports the single-session model: first authenticated guard (by priority) wins.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow the recovery route to load without redirect loops.
        if ($request->routeIs('auth.reset')) {
            return $next($request);
        }

        foreach ($this->guardResolver->configuredSessionGuardsInPriorityOrder() as $guard) {
            $auth = Auth::guard($guard);

            if ($auth->check()) {
                return $this->redirectToDashboard($guard, $auth->user());
            }
        }

        return $next($request);
    }

    /**
     * Resolve the best dashboard route for the authenticated identity.
     *
     * @param  string  $guard  The active guard we detected.
     * @param  mixed   $user   The authenticated user instance for that guard (may be null defensively).
     */
    private function redirectToDashboard(string $guard, mixed $user): Response
    {
        // Defensive: if check() was true but user retrieval failed for some reason.
        if (! $user) {
            return redirect()->route('auth.reset');
        }

        // Role-aware for guards that support roles; otherwise null.
        $role = $this->dashboardResolver->highestPriorityRole($guard, $user);

        // Resolve route name from config/mapping.
        $routeName = $this->dashboardResolver->routeName($guard, $role);

        // Fail-safe if config drift occurs (route removed/renamed).
        if (! Route::has($routeName)) {
            return redirect()->route('auth.reset');
        }

        return redirect()->route($routeName);
    }
}