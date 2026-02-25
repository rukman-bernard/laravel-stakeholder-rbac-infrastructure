<?php

namespace App\Services\Auth;

use App\Constants\Guards;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

final class GuardRedirectService
{
    public function __construct(
        private readonly GuardResolver $guardResolver,
    ) {}

    /**
     * Redirect an unauthenticated request to the most appropriate login page.
     *
     * Decision order:
     * 1) If the URL begins with a portal segment (e.g. /student/*), use that guard.
     * 2) Otherwise, use the currently active authenticated guard (single-session model).
     * 3) Otherwise, fall back to the default guard.
     */
    public function redirectToLogin(Request $request): RedirectResponse
    {
        $guard = $this->resolveGuardFromRequest($request);

        return $this->safeGuestRedirectToRoute(
            $this->loginRouteNameForGuard($guard),
            $this->fallbackUrl()
        );
    }

    /**
     * Resolve which guard should own this request for login redirection.
     *
     * Public because middleware may call it directly for visibility/debugging.
     */
    public function resolveGuardFromRequest(Request $request): string
    {
        // 1) Prefer explicit portal segment in the URL.
        $segmentGuard = Guards::normalize($request->segment(1));
        if ($segmentGuard && Guards::isPortal($segmentGuard)) {
            return $segmentGuard;
        }

        // 2) Otherwise, detect the active authenticated guard (if any).
        $detected = $this->guardResolver->detect();
        if (is_string($detected) && $detected !== '' && Guards::isValid($detected)) {
            return $detected;
        }

        // 3) Final fallback.
        return Guards::default();
    }

    /**
     * Resolve the login route name for a given guard.
     *
     * web     -> login
     * portals -> {guard}.login
     * unknown -> fallback route name from config
     */
    public function loginRouteNameForGuard(string $guard): string
    {
        $guard = Guards::normalize($guard) ?? Guards::default();

        if ($guard === Guards::WEB) {
            return 'login';
        }

        if (Guards::isPortal($guard)) {
            return "{$guard}.login";
        }

        return $this->fallbackRouteName();
    }

    /**
     * Route name used when we cannot determine a specific login route.
     * Defaults to 'portal.hub'.
     */
    public function fallbackRouteName(): string
    {
        $route = config('nka.auth.unauthenticated_redirect_route', 'portal.hub');

        return is_string($route) && trim($route) !== ''
            ? trim($route)
            : 'portal.hub';
    }

    /**
     * Fallback URL when the computed route does not exist.
     */
    public function fallbackUrl(): string
    {
        $fallbackRoute = $this->fallbackRouteName();

        return Route::has($fallbackRoute)
            ? route($fallbackRoute)
            : url('/');
    }

    /**
     * Redirect as a "guest" to a route if it exists, otherwise to a safe fallback URL.
     */
    private function safeGuestRedirectToRoute(string $routeName, string $fallbackUrl): RedirectResponse
    {
        if (Route::has($routeName)) {
            return redirect()->guest(route($routeName));
        }

        return redirect()->guest($fallbackUrl);
    }
}