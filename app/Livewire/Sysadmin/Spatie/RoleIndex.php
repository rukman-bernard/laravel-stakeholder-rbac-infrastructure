<?php

namespace App\Livewire\Sysadmin\Spatie;

use App\Constants\Permissions;
use App\Traits\AuthorizesWithPermissions;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class RoleIndex extends Component
{
    use AuthorizesWithPermissions;

    /** @var \Illuminate\Support\Collection<int, Role> */
    public $roles;

    protected $listeners = ['roleUpdated' => 'loadRoles'];

    public function mount(): void
    {
        $this->authorizePermission(Permissions::VIEW_ROLES, 'You do not have permission to view roles.');
        $this->loadRoles();
    }

    public function loadRoles(): void
    {
        $this->roles = Role::all();
    }

    public function deleteRole(int $roleId): void
    {
        $this->authorizePermission(Permissions::DELETE_ROLES, 'You do not have permission to delete roles.');

        $role = Role::find($roleId);

        if (!$role) {
            session()->flash('error', 'Role not found.');
            return;
        }


        $role->delete();

        session()->flash('message', 'Role deleted successfully.');

        $this->loadRoles();
    }

    public function render()
    {
        return view('livewire.sysadmin.spatie.role-index');
    }
}
