<?php

namespace App\View\Composers;

use App\Constants\Guards;
use App\Services\Auth\DashboardResolver;
use App\Services\Auth\GuardResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class ErrorViewComposer
{
    public function __construct(
        private readonly GuardResolver $guardResolver,
        private readonly DashboardResolver $dashboardResolver,
    ) {}

    /**
     * Inject identity + dashboard context into error views.
     *
     * This allows 403/404/500 pages to:
     * - Show the current user (if authenticated)
     * - Show the most accurate guard context available
     * - Provide a safe "Back to dashboard" or "Reset session" link
     */
    public function compose(View $view): void
    {
        $identity = $this->guardResolver->identity();

        $user = $identity['user'] ?? null;

        $guard = is_array($identity) && ! empty($identity['guard'])
            ? (string) $identity['guard']
            : $this->resolveGuardFromRouteContext();

        $roles = $this->resolveRoles($user);

        $dashboardRoute = $this->resolveDashboardRoute($guard, $user);

        $view->with([
            'identity_guard'      => $guard,
            'identity_user'       => $user,
            'identity_user_name'  => $this->resolveUserName($user),
            'identity_roles'      => $roles,
            'dashboard_route'     => $dashboardRoute,
            'dashboard_url'       => $this->safeRouteUrl($dashboardRoute),
        ]);
    }

    /**
     * Resolve user display name safely.
     */
    private function resolveUserName(mixed $user): string
    {
        return $user?->name
            ?? $user?->email
            ?? 'Unknown User';
    }

    /**
     * Resolve user roles safely (Spatie-compatible).
     */
    private function resolveRoles(mixed $user): Collection
    {
        if (is_object($user) && method_exists($user, 'getRoleNames')) {
            return $user->getRoleNames();
        }

        return collect();
    }

    /**
     * Resolve the most accurate guard from the current route context
     * when there is no authenticated identity.
     */
    private function resolveGuardFromRouteContext(): ?string
    {
        $route = Request::route();

        if (! $route) {
            return null;
        }

        $routeName = $route->getName();

        if (! is_string($routeName) || $routeName === '') {
            return null;
        }

        // Default web verification routes
        if (Str::startsWith($routeName, 'verification.')) {
            return Guards::WEB;
        }

        // Portal guard routes such as:
        // student.verification.verify
        // employer.verification.verify
        // student.login
        // employer.password.request
        foreach (Guards::portal() as $portalGuard) {
            if (Str::startsWith($routeName, "{$portalGuard}.")) {
                return $portalGuard;
            }
        }

        // If this is some other web-namespaced route, leave it null
        // rather than forcing an incorrect guard label.
        return null;
    }

    /**
     * Resolve dashboard route name safely.
     */
    private function resolveDashboardRoute(?string $guard, mixed $user): string
    {
        if (! $user || ! $guard) {
            return 'auth.reset';
        }

        $role = $this->dashboardResolver->highestPriorityRole($guard, $user);

        $route = $this->dashboardResolver->routeName($guard, $role);

        return is_string($route) && $route !== ''
            ? $route
            : 'auth.reset';
    }

    /**
     * Convert route name to URL safely.
     */
    private function safeRouteUrl(string $routeName): string
    {
        if (Route::has($routeName)) {
            return route($routeName);
        }

        if (Route::has('auth.reset')) {
            return route('auth.reset');
        }

        return url('/');
    }
}