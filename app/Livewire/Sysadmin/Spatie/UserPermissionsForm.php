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
    public $permissions = [];

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

        $this->permissions = Permission::query()
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

        session()->flash('message', 'Roles and permissions updated successfully!');
    }

    public function render()
    {
        return view('livewire.sysadmin.spatie.user-permissions-form');
    }
}