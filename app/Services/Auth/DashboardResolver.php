<?php

namespace App\Services\Auth;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

final class DashboardResolver
{
    private const FALLBACK_ROUTE = 'auth.reset';

    /**
     * Resolve dashboard route name for a given guard and optional role.
     *
     * Supported config shapes:
     * - nka.auth.dashboard_routes.<guard> = "route.name"
     * - nka.auth.dashboard_routes.<guard> = [
     *       "default" => "route.name",
     *       "roles"   => ["role" => "route.name"]
     *   ]
     */
    public function routeName(string $guard, ?string $role = null): string
    {
        $config = config("nka.auth.dashboard_routes.$guard");

        if (is_string($config)) {
            return $this->cleanRouteName($config);
        }

        if (is_array($config)) {
            $roleRoute = $role ? ($config['roles'][$role] ?? null) : null;

            if (is_string($roleRoute) && $roleRoute !== '') {
                return $this->cleanRouteName($roleRoute);
            }

            return $this->cleanRouteName($config['default'] ?? self::FALLBACK_ROUTE);
        }

        return self::FALLBACK_ROUTE;
    }

    /**
     * Resolve dashboard URL from the resolved route name.
     *
     * Safety:
     * - If the resolved route does not exist, falls back to auth.reset.
     * - If auth.reset is also missing, falls back to "/".
     */
    public function url(string $guard, ?string $role = null): string
    {
        $routeName = $this->routeName($guard, $role);

        if (Route::has($routeName)) {
            return route($routeName);
        }

        // Config drift safety-net
        if (Route::has(self::FALLBACK_ROUTE)) {
            return route(self::FALLBACK_ROUTE);
        }

        return url('/');
    }

    /**
     * Resolve highest priority role for a user under a given guard.
     *
     * Priority source:
     * - config('nka.auth.dashboard_role_priority.<guard>') (explicit order)
     * - fallback: deterministic alphabetical order (case-insensitive)
     */
    public function highestPriorityRole(string $guard, mixed $user): ?string
    {
        $roles = $this->getUserRoleNames($user);

        if ($roles->isEmpty()) {
            return null;
        }

        $priority = $this->normalizedPriorityList($guard);

        foreach ($priority as $prio) {
            $match = $roles->first(fn (string $r) => Str::lower($r) === $prio);
            if ($match !== null) {
                return $match;
            }
        }

        return $roles->sortBy(fn (string $r) => Str::lower($r))->first();
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function getUserRoleNames(mixed $user): Collection
    {
        if (! $user || ! method_exists($user, 'getRoleNames')) {
            return collect();
        }

        return collect($user->getRoleNames())
            ->filter(fn ($r) => is_string($r) && $r !== '')
            ->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, string> Lower-cased role keys in priority order.
     */
    private function normalizedPriorityList(string $guard): Collection
    {
        return collect(config("nka.auth.dashboard_role_priority.$guard", []))
            ->filter(fn ($r) => is_string($r) && $r !== '')
            ->map(fn (string $r) => Str::lower(trim($r)))
            ->values();
    }

    private function cleanRouteName(string $route): string
    {
        $route = trim($route);

        return $route !== '' ? $route : self::FALLBACK_ROUTE;
    }
}