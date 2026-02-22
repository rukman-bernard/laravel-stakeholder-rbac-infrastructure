<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

use App\Constants\Guards;
use App\Constants\Permissions;
use App\Constants\Roles;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Always clear cache before seeding roles/permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = Guards::WEB;

        /*
        |--------------------------------------------------------------------------
        | 1) Ensure all permissions exist
        |--------------------------------------------------------------------------
        */
        foreach (Permissions::all() as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => $guard,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2) Define role -> permissions map
        |--------------------------------------------------------------------------
        | Keep this as the only place where roles are granted permissions.
        */
        $rolePermissions = [
            Roles::SYSTEM_ADMIN => Permissions::all(),

            // Keep roles created even if they currently have no explicit perms
            Roles::SUPER_ADMIN => [],
            Roles::ADMIN       => [],
        ];

        /*
        |--------------------------------------------------------------------------
        | 3) Create roles and sync permissions
        |--------------------------------------------------------------------------
        */
        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => $guard,
            ]);

            // syncPermissions([]) is valid and intentionally clears stray perms
            $role->syncPermissions($permissions);
        }

        // Optional: clear cache again to ensure app sees latest assignments immediately
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}