<?php

use App\Constants\Guards;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/**
|--------------------------------------------------------------------------
| Global Helpers (Keep Minimal)
|--------------------------------------------------------------------------
| Global helper functions become "public API" inside the application.
| Keep them generic, stable, and side-effect free where possible.
|
| Rule of thumb:
| - Pure utility helpers 
| - Anything policy/auth-flow heavy -> move to a service class
|--------------------------------------------------------------------------
*/

if (! function_exists('nka_active_session_guard')) {
    /**
     * Return the first authenticated guard from the configured session guards.
     *
     * Notes:
     * - Uses Guards::session() as the single source of truth
     * - Returns null when no session guard is authenticated
     */
    function nka_active_session_guard(): ?string
    {
        foreach (Guards::session() as $guard) {
            if (Auth::guard($guard)->check()) {
                return $guard;
            }
        }

        return null;
    }
}

if (! function_exists('nka_verification_resend_route_name')) {
    /**
     * Resolve the email verification resend route name for the current session guard.
     *
     * Convention:
     * - web      => verification.resend
     * - student  => student.verification.resend
     * - employer => employer.verification.resend
     *
     * Safety:
     * - If the guard-specific route doesn't exist, falls back to web.
     */
    function nka_verification_resend_route_name(): string
    {
        $guard = nka_active_session_guard();

        // Default to web when no guard is active or when web is active
        if (! $guard || $guard === Guards::WEB) {
            return 'verification.resend';
        }

        $routeName = "{$guard}.verification.resend";

        return Route::has($routeName)
            ? $routeName
            : 'verification.resend';
    }
}

if (! function_exists('nka_auth_debug')) {
    /**
     * Lightweight auth debugging logger (non-production only).
     *
     * Why this exists:
     * - Debugging multi-guard edge cases can be painful during redirects/middleware.
     * - This helper provides a safe "no-op unless enabled" logger that never breaks execution.
     *
     * Enable via:
     * - APP_ENV != production
     * - config('logging.nka_auth_debug_enabled') = true
     * - channel "nka_auth_debug" configured in logging.php
     */
    function nka_auth_debug(string $message, array $context = []): void
    {
        // Debug helper must never interfere with boot/exception rendering
        if (! app()->bound('log')) {
            return;
        }

        if (app()->environment('production')) {
            return;
        }

        if (! config('logging.nka_auth_debug_enabled', false)) {
            return;
        }

        try {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0] ?? [];
            $source = ($trace['file'] ?? 'unknown') . ':' . ($trace['line'] ?? '0');

            Log::channel('nka_auth_debug')->debug(
                "[NKA AUTH DEBUG] {$message} @ {$source}",
                $context
            );
        } catch (Throwable) {
            // Swallow silently — debug must never break real flows
        }
    }
}