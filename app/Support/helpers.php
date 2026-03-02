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