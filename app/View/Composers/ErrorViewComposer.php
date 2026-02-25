<?php

namespace App\View\Composers;

use App\Constants\Guards;
use App\Services\Auth\DashboardResolver;
use App\Services\Auth\GuardResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
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
     * - Provide a safe "Back to dashboard" link
     */
    public function compose(View $view): void
    {
        $identity = $this->guardResolver->identity();

        $guard = is_array($identity) && ! empty($identity['guard'])
            ? (string) $identity['guard']
            : Guards::WEB;

        $user = $identity['user'] ?? null;

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
     * Resolve dashboard route name safely.
     */
    private function resolveDashboardRoute(string $guard, mixed $user): string
    {
        if (! $user) {
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