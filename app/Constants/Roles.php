<?php

namespace App\Constants;

/**
 * Roles (Spatie)
 * --------------
 * Single source of truth for role keys used in:
 * - seeders
 * - authorization checks
 * - UI labels (AdminLTE user menu, badges, logs)
 *
 * Notes:
 * - Role names must match values stored in the `roles` table.
 * - Roles are NOT guards (guards are defined in auth.php).
 * - Keep string values stable unless you also migrate existing DB rows.
 */
final class Roles
{
    // =====================================================================
    // Web-guard roles (staff)
    // =====================================================================

    public const SYSTEM_ADMIN = 'sysadmin';
    public const SUPER_ADMIN  = 'superadmin';
    public const ADMIN        = 'admin';

    /**
     * Canonical registry of roles and UI labels.
     * Keep this as the single authoritative map to avoid duplication.
     *
     * @var array<string, string>
     */
    private const REGISTRY = [
        self::SYSTEM_ADMIN => 'System Administrator',
        self::SUPER_ADMIN  => 'Super Admin',
        self::ADMIN        => 'Admin',
    ];

    /**
     * Get all role keys in deterministic order.
     *
     * @return array<int, string>
     */
    public static function all(): array
    {
        return array_keys(self::REGISTRY);
    }

    /**
     * Get all labels keyed by role key.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return self::REGISTRY;
    }

    /**
     * Resolve a UI label for a role key.
     * Falls back to a safe title-cased string for unknown roles.
     */
    public static function label(?string $role): string
    {
        $role = self::normalize($role);

        if ($role === null) {
            return 'User';
        }

        return self::REGISTRY[$role] ?? self::fallbackLabel($role);
    }

    /**
     * True if the given role key is one of the known roles.
     */
    public static function isValid(?string $role): bool
    {
        $role = self::normalize($role);

        return $role !== null && array_key_exists($role, self::REGISTRY);
    }

    /**
     * Normalize input (route params, request values, query strings).
     * Returns null if empty.
     */
    public static function normalize(?string $role): ?string
    {
        if ($role === null) {
            return null;
        }

        $role = strtolower(trim($role));

        return $role !== '' ? $role : null;
    }

    /**
     * Safe label fallback for unknown roles.
     */
    private static function fallbackLabel(string $role): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $role));
    }
}