<?php

namespace App\Livewire\Sysadmin\Spatie;

use App\Constants\Guards;
use App\Constants\Permissions;
use App\Traits\AuthorizesWithPermissions;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleForm extends Component
{
    use AuthorizesWithPermissions;

    public $roleId;
    public $name = '';
    public $guard_name = Guards::WEB;
    public $permissions = [];

    public $allPermissions = [];
    public $mode = 'create';

    protected $listeners = [
        'createRole' => 'create',
        'editRole' => 'edit',
    ];

    public function availableGuards(): array
    {
        return Guards::configured();
    }

    public function availablePermissionNames(): array
    {
        return Permission::query()
            ->where('guard_name', $this->guard_name)
            ->pluck('name')
            ->toArray();
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                Rule::unique('roles', 'name')
                    ->where(fn ($query) => $query->where('guard_name', $this->guard_name))
                    ->ignore($this->roleId),
            ],
            'guard_name' => [
                'required',
                'string',
                Rule::in($this->availableGuards()),
            ],
            'permissions' => ['array'],
            'permissions.*' => [
                'string',
                Rule::in($this->availablePermissionNames()),
            ],
        ];
    }

    public function mount(): void
    {
        $this->loadPermissions();
    }

    public function updatedGuardName(): void
    {
        $this->permissions = [];
        $this->loadPermissions();
    }

    private function loadPermissions(): void
    {
        $this->allPermissions = Permission::query()
            ->where('guard_name', $this->guard_name)
            ->get();
    }

    public function create(): void
    {
        $this->authorizePermission(
            Permissions::CREATE_ROLES,
            'You do not have permission to create roles.'
        );

        $this->reset(['roleId', 'name', 'guard_name', 'permissions']);
        $this->guard_name = Guards::WEB;
        $this->loadPermissions();

        $this->mode = 'create';
        $this->dispatch('modal:show', modalId: 'roleModal');
    }

    public function edit(int $id): void
    {
        $this->authorizePermission(
            Permissions::EDIT_ROLES,
            'You do not have permission to edit roles.'
        );

        $role = Role::query()->findOrFail($id);

        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->guard_name = $role->guard_name;
        $this->permissions = $role->permissions->pluck('name')->toArray();

        $this->loadPermissions();

        $this->mode = 'edit';
        $this->dispatch('modal:show', modalId: 'roleModal');
    }

    public function closeModal(): void
    {
        $this->dispatch('modal:hide', modalId: 'roleModal');
    }

    public function save(): void
    {
        $this->authorizePermission(
            $this->roleId ? Permissions::EDIT_ROLES : Permissions::CREATE_ROLES,
            'You do not have permission to save roles.'
        );

        $this->validate();

        $role = Role::updateOrCreate(
            ['id' => $this->roleId],
            [
                'name' => $this->name,
                'guard_name' => $this->guard_name,
            ]
        );

        $role->syncPermissions($this->permissions);

        session()->flash('message', $this->roleId ? 'Role updated!' : 'Role created!');

        $this->dispatch('modal:hide', modalId: 'roleModal');
        $this->dispatch('roleUpdated');
    }

    public function render()
    {
        return view('livewire.sysadmin.spatie.role-form', [
            'guards' => $this->availableGuards(),
        ]);
    }
}