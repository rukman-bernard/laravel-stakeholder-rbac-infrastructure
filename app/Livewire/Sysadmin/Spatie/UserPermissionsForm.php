<?php

namespace App\Livewire\Sysadmin\Spatie;

use App\Constants\Guards;
use App\Constants\Permissions as PermissionKeys;
use App\Models\User;
use App\Traits\AuthorizesWithPermissions;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserPermissionsForm extends Component
{
    use AuthorizesWithPermissions;

    public $userId;
    public $user;

    public $roles = [];
    public $allPermissions = [];

    public $selectedRoles = [];
    public $selectedPermissions = [];

    private string $guard = Guards::WEB;

    public function mount(User $user): void
    {
        $this->authorizePermission(
            PermissionKeys::VIEW_USERS,
            'You do not have permission to view users.'
        );

        $this->user = $user;
        $this->userId = $user->id;

        $this->roles = Role::query()
            ->where('guard_name', $this->guard)
            ->get();

        $this->allPermissions = Permission::query()
            ->where('guard_name', $this->guard)
            ->get();

        $this->selectedRoles = $this->user->roles()
            ->where('guard_name', $this->guard)
            ->pluck('id')
            ->toArray();

        $this->selectedPermissions = $this->user->permissions()
            ->where('guard_name', $this->guard)
            ->pluck('id')
            ->toArray();
    }

    public function getInheritedPermissionsProperty()
    {
        if (empty($this->selectedRoles)) {
            return collect();
        }

        return Permission::query()
            ->where('guard_name', $this->guard)
            ->whereHas('roles', function ($query) {
                $query->whereIn('roles.id', $this->selectedRoles);
            })
            ->orderBy('name')
            ->get()
            ->unique('id')
            ->values();
    }

    public function getSelectablePermissionsProperty()
    {
        $inheritedIds = $this->inheritedPermissions->pluck('id')->all();

        return $this->allPermissions
            ->reject(fn ($permission) => in_array($permission->id, $inheritedIds, true))
            ->sortBy('name')
            ->values();
    }

    public function updatedSelectedRoles(): void
    {
        $inheritedIds = $this->inheritedPermissions->pluck('id')->all();

        // Remove any direct selections that are now inherited via roles
        $this->selectedPermissions = array_values(array_diff(
            $this->selectedPermissions,
            $inheritedIds
        ));
    }

    public function save(): void
    {
        $this->authorizePermission(
            PermissionKeys::EDIT_USERS,
            'You do not have permission to edit users.'
        );

        $roles = Role::query()
            ->where('guard_name', $this->guard)
            ->whereIn('id', $this->selectedRoles)
            ->get();

        $this->user->syncRoles($roles);

        $permissions = Permission::query()
            ->where('guard_name', $this->guard)
            ->whereIn('id', $this->selectedPermissions)
            ->get();

        $this->user->syncPermissions($permissions);

        session()->flash('message', 'Roles and direct permissions updated successfully!');
    }

    public function render()
    {
        return view('livewire.sysadmin.spatie.user-permissions-form', [
            'inheritedPermissions' => $this->inheritedPermissions,
            'selectablePermissions' => $this->selectablePermissions,
        ]);
    }
}