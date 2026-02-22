<?php

namespace App\Constants;

/**
 * Spatie Permission Constants
 *
 * Keep permission string values stable.
 * If you change a value, you must also update existing DB records.
 */
final class Permissions
{
    private function __construct()
    {
        // constants-only
    }

    // =====================================================================
    // SYSADMIN (System Admin)
    // =====================================================================

    /** Menu heading visibility (AdminLTE sidebar grouping) */
    public const VIEW_SYSTEM_ADMIN_MENU_HEADING = 'view sysadmin menu heading';

    /** Sysadmin dashboard access */
    public const VIEW_SYSTEM_ADMIN_DASHBOARD = 'view sysadmin dashboard';

    // Users
    public const VIEW_USERS   = 'view users';
    public const CREATE_USERS = 'create users';
    public const EDIT_USERS   = 'edit users';
    public const DELETE_USERS = 'delete users';

    // Roles
    public const VIEW_ROLES   = 'view roles';
    public const CREATE_ROLES = 'create roles';
    public const EDIT_ROLES   = 'edit roles';
    public const DELETE_ROLES = 'delete roles';

    // Permissions
    public const VIEW_PERMISSIONS   = 'view permissions';
    public const CREATE_PERMISSIONS = 'create permissions';
    public const EDIT_PERMISSIONS   = 'edit permissions';
    public const DELETE_PERMISSIONS = 'delete permissions';

    /**
     * Single source of truth for "all permissions currently used by seeders".
     * Your seeders should call this instead of duplicating arrays.
     *
     * @return array<int, string>
     */
    public static function all(): array
    {
        return [
            self::VIEW_SYSTEM_ADMIN_MENU_HEADING,
            self::VIEW_SYSTEM_ADMIN_DASHBOARD,

            self::VIEW_USERS,
            self::CREATE_USERS,
            self::EDIT_USERS,
            self::DELETE_USERS,

            self::VIEW_ROLES,
            self::CREATE_ROLES,
            self::EDIT_ROLES,
            self::DELETE_ROLES,

            self::VIEW_PERMISSIONS,
            self::CREATE_PERMISSIONS,
            self::EDIT_PERMISSIONS,
            self::DELETE_PERMISSIONS,
        ];
    }
}