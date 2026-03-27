<?php

namespace App\Constants;

/**
 * Guards
 * ======
 *
 * Centralised application guard registry and helper methods for authentication
 * guard names used by the infrastructure.
 *
 * Architectural Intent
 * --------------------
 * This class is intentionally not a thin alias around config('auth.guards').
 *
 * A guard becomes fully usable within the infrastructure only when it is:
 *
 * 1. configured in Laravel authentication configuration (`config/auth.php`)
 * 2. recognised by this application guard registry
 *
 * This two-step model is deliberate.
 *
 * Why explicit registration exists
 * --------------------------------
 * In this infrastructure, guards influence more than authentication. They also
 * affect:
 *
 * - validation and UI selection
 * - human-readable labels
 * - portal classification
 * - deterministic multi-guard resolution policy
 * - broader application semantics
 *
 * For that reason, adding a new guard is treated as an application-level
 * architectural change, not just a framework configuration change.
 *
 * Design Notes
 * ------------
 * - `all()` defines the application's known guard vocabulary
 * - `configured()` returns known guards currently enabled in `config/auth.php`
 * - runtime guard classification (session vs non-session) is derived from config
 * - `resolutionOrder()` expresses application policy, filtered by configured
 *   session guards at runtime
 * - this class must not reference models, roles, or broader application logic
 *
 * Typical Usage
 * -------------
 * - Validation:
 *     Guards::configured()
 *     Guards::isConfigured($guard)
 *
 * - Classification:
 *     Guards::session()
 *     Guards::nonSession()
 *     Guards::portal()
 *
 * - Middleware / auth resolution:
 *     Guards::resolutionOrder()
 *
 * - UI / logs:
 *     Guards::label($guard)
 *
 * - Hard rules:
 *     Guards::WEB
 */
final class Guards
{
    /**
     * Canonical application guard identifiers.
     *
     * These constants represent guards intentionally supported by the
     * infrastructure. Adding a new guard should be reflected here explicitly.
     */
    public const WEB      = 'web';
    public const STUDENT  = 'student';
    public const EMPLOYER = 'employer';
    public const API      = 'api';

    /**
     * Preferred deterministic resolution order for session-based guards.
     *
     * This expresses application policy rather than raw framework
     * configuration. The final runtime order is filtered through configured
     * session guards.
     */
    private const RESOLUTION_ORDER = [
        self::WEB,
        self::STUDENT,
        self::EMPLOYER,
    ];

    /**
     * Human-readable labels for known guards.
     */
    private const LABELS = [
        self::WEB      => 'Web',
        self::STUDENT  => 'Student',
        self::EMPLOYER => 'Employer',
        self::API      => 'API',
    ];

    /**
     * Return all guards explicitly recognised by the application.
     *
     * This is the infrastructure's application-level guard vocabulary.
     * A guard configured in Laravel but absent here is not treated as usable
     * by the application abstraction until it is explicitly registered.
     */
    public static function all(): array
    {
        return [
            self::WEB,
            self::STUDENT,
            self::EMPLOYER,
            self::API,
        ];
    }

    /**
     * Return all configured guards recognised by the application.
     *
     * This method intersects framework configuration with the application's
     * supported guard vocabulary.
     */
    public static function configured(): array
    {
        $configured = array_keys(config('auth.guards', []));

        return array_values(array_intersect(self::all(), $configured));
    }

    /**
     * Return configured session-based guards.
     *
     * Session classification is derived from auth configuration rather than
     * hardcoded separately in this class.
     */
    public static function session(): array
    {
        return array_values(array_filter(
            self::configured(),
            static fn (string $guard): bool => self::driver($guard) === 'session'
        ));
    }

    /**
     * Return configured non-session guards.
     */
    public static function nonSession(): array
    {
        return array_values(array_filter(
            self::configured(),
            static fn (string $guard): bool => self::driver($guard) !== 'session'
        ));
    }

    /**
     * Return configured portal guards.
     *
     * Portal guards are session-based guards excluding the internal `web` guard.
     */
    public static function portal(): array
    {
        return array_values(array_diff(self::session(), [self::WEB]));
    }

    /**
     * Return the configured driver for a guard, or null if unavailable.
     */
    public static function driver(string $guard): ?string
    {
        return config("auth.guards.{$guard}.driver");
    }

    /**
     * Return guard resolution order for deterministic multi-guard authentication.
     *
     * The preferred application policy order is filtered to configured
     * session guards so that only runtime-available session guards participate
     * in resolution.
     */
    public static function resolutionOrder(): array
    {
        return array_values(array_intersect(
            self::RESOLUTION_ORDER,
            self::session()
        ));
    }

    /**
     * Return the default guard for the application.
     *
     * Falls back to WEB if config is missing or resolves to a guard not
     * recognised by the application registry.
     */
    public static function default(): string
    {
        $default = config('auth.defaults.guard', self::WEB);

        return self::isConfigured($default) ? $default : self::WEB;
    }

    /**
     * Determine whether a guard name is recognised by the application.
     *
     * This checks membership in the application registry only.
     */
    public static function isValid(?string $guard): bool
    {
        if ($guard === null || $guard === '') {
            return false;
        }

        return in_array($guard, self::all(), true);
    }

    /**
     * Determine whether a guard is both recognised by the application and
     * configured in Laravel authentication configuration.
     */
    public static function isConfigured(?string $guard): bool
    {
        if ($guard === null || $guard === '') {
            return false;
        }

        return in_array($guard, self::configured(), true);
    }

    /**
     * Determine whether a configured guard uses session-based authentication.
     */
    public static function isSession(string $guard): bool
    {
        return self::isConfigured($guard) && self::driver($guard) === 'session';
    }

    /**
     * Determine whether a configured guard uses non-session authentication.
     */
    public static function isNonSession(string $guard): bool
    {
        return self::isConfigured($guard) && self::driver($guard) !== 'session';
    }

    /**
     * Determine whether a configured guard represents a portal user.
     */
    public static function isPortal(string $guard): bool
    {
        return self::isSession($guard) && $guard !== self::WEB;
    }

    /**
     * Return all human-readable guard labels.
     */
    public static function labels(): array
    {
        return self::LABELS;
    }

    /**
     * Return a human-readable label for a given guard.
     */
    public static function label(string $guard): string
    {
        return self::LABELS[$guard] ?? ucfirst($guard);
    }

    /**
     * Normalize a guard value from user input or route parameters.
     *
     * Returns null if the guard is not recognised by the application registry.
     */
    public static function normalize(?string $guard): ?string
    {
        if ($guard === null) {
            return null;
        }

        $guard = strtolower(trim($guard));

        return self::isValid($guard) ? $guard : null;
    }

    /**
     * Normalize a guard value and ensure it is configured.
     *
     * Returns null if the guard is not configured in auth.php or not recognised
     * by the application registry.
     */
    public static function normalizeConfigured(?string $guard): ?string
    {
        $guard = self::normalize($guard);

        return self::isConfigured($guard) ? $guard : null;
    }
}