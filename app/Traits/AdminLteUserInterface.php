<?php

namespace App\Traits;

use App\Constants\Guards;
use App\Constants\Roles;
use App\Services\Auth\GuardResolver;

trait AdminLteUserInterface
{
    /**
     * Return the user's primary Spatie role key (e.g., 'sysadmin') or null.
     *
     * Notes:
     * - Not all authenticatable models use Spatie roles (e.g., student/employer might not).
     * - We guard the call to keep this trait reusable across all stakeholder models.
     */
    public function getPrimaryRoleKey(): ?string
    {
        if (! method_exists($this, 'getRoleNames')) {
            return null;
        }

        $role = $this->getRoleNames()->first();

        return (is_string($role) && $role !== '') ? $role : null;
    }

    /**
     * Return a human-readable label for the AdminLTE user menu.
     *
     * Priority:
     * 1) Role label (when Spatie roles exist)
     * 2) Guard label (student/employer/web, etc.)
     */
    public function getPrimaryRoleLabel(): string
    {
        if ($roleKey = $this->getPrimaryRoleKey()) {
            return Roles::label($roleKey);
        }

        $guard = app(GuardResolver::class)->detect() ?? Guards::WEB;

        return Guards::label($guard);
    }

    /**
     * AdminLTE profile link shown in the user dropdown.
     */
    public function adminlte_profile_url(): string
    {
        return route('profile');
    }

    /**
     * AdminLTE avatar image URL for the user dropdown.
     *
     * Assumes the model exposes `profile_image_url` via an accessor
     * (recommended for consistent behaviour across User/Student/Employer).
     */
    public function adminlte_image(): string
    {
        return (string) ($this->profile_image_url ?? asset('images/default-avatar.png'));
    }

    /**
     * AdminLTE "description" shown under the name (role/guard label).
     */
    public function adminlte_desc(): string
    {
        return $this->getPrimaryRoleLabel();
    }

    /**
     * AdminLTE expects this method in some integration flows.
     */
    public function adminlte_user(): static
    {
        return $this;
    }
}