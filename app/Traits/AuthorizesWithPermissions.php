<?php

namespace App\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * AuthorizesWithPermissions
 *
 * Minimal helper for Livewire components that use Spatie permission strings
 * (stored in App\Constants\Permissions).
 *
 * Why this exists:
 * - Keeps authorisation checks consistent across components.
 * - Avoids repeating try/catch + abort(403) boilerplate.
 * - Makes it obvious where to place authorisation (render() for view, actions for mutations).
 */
trait AuthorizesWithPermissions
{
    use AuthorizesRequests;

    /**
     * Authorize a permission and abort with a readable 403 message.
     */
    protected function authorizePermission(string $permission, ?string $message = null): void
    {
        try {
            $this->authorize($permission);
        } catch (AuthorizationException) {
            abort(403, $message ?? 'You do not have permission to perform this action.');
        }
    }
}
