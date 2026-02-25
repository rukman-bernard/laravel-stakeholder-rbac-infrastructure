<?php

namespace App\Livewire\Sysadmin\Spatie;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;
use App\Constants\Guards;
use App\Constants\Permissions;
use App\Traits\AuthorizesWithPermissions;

class RoleForm extends Component
{
    use AuthorizesWithPermissions;

    public $roleId;
    public $name = '';
    public $guard_name = Guards::WEB;
    public $permissions = [];

    public $allPermissions = [];
    public $mode = 'create';

    protected $rules = [
        'name' => 'required|string|min:3|unique:roles,name',
        'guard_name' => 'required|string',
        'permissions' => 'array',
    ];

    protected $listeners = [
        'createRole' => 'create',
        'editRole' => 'edit',
    ];

    public function mount(): void
    {
        $this->allPermissions = Permission::all();
    }

    public function create(): void
    {
        $this->authorizePermission(Permissions::CREATE_ROLES, 'You do not have permission to create roles.');


        $this->reset(['roleId', 'name', 'guard_name', 'permissions']);
        $this->mode = 'create';
        // $this->dispatch('showModal', 'roleModal');
        $this->dispatch('modal:show', modalId: 'roleModal');


    }

    public function edit(int $id): void
    {
        $this->authorizePermission(Permissions::EDIT_ROLES, 'You do not have permission to edit roles.');

        $role = Role::findOrFail($id);
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->guard_name = $role->guard_name;
        $this->permissions = $role->permissions->pluck('name')->toArray();
        $this->mode = 'edit';
        // $this->dispatch('showModal', 'roleModal');
        $this->dispatch('modal:show', modalId: 'roleModal');
    }

    public function closeModal(): void
    {
        // $this->dispatch('hideModal', 'roleModal');
        $this->dispatch('modal:hide', modalId: 'roleModal');
    }

    public function save(): void
    {
        // Save is used for both create + edit.
        $this->authorizePermission(
            $this->roleId ? Permissions::EDIT_ROLES : Permissions::CREATE_ROLES,
            'You do not have permission to save roles.'
        );

        $validated = $this->validate([
            'name' => [
                'required', 'string', 'min:3',
                Rule::unique('roles')->ignore($this->roleId),
            ],
            'guard_name' => 'required|string',
            'permissions' => 'array',
        ]);

        $role = Role::updateOrCreate(
            ['id' => $this->roleId],
            ['name' => $this->name, 'guard_name' => $this->guard_name]
        );

        $role->syncPermissions($this->permissions);

        session()->flash('message', $this->roleId ? 'Role updated!' : 'Role created!');

        $this->dispatch('modal:hide', modalId: 'roleModal');
        $this->dispatch('roleUpdated');
    }

    public function render()
    {
        return view('livewire.sysadmin.spatie.role-form');
    }
}
