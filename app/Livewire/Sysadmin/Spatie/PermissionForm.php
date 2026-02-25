<?php

namespace App\Livewire\Sysadmin\Spatie;

use App\Constants\Guards;
use App\Constants\Permissions;
use App\Traits\AuthorizesWithPermissions;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

final class PermissionForm extends Component
{
    use AuthorizesWithPermissions;

    public ?int $permissionId = null;
    public string $name = '';
    public string $guard_name = Guards::WEB;
    public string $mode = 'create';

    protected $listeners = [
        'permission:open' => 'open',
    ];

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'guard_name' => ['required', 'string', 'max:255'],
        ];
    }

    public function open(string $mode, ?int $id = null): void
    {
        $this->authorizePermission(
            $mode === 'edit' ? Permissions::EDIT_PERMISSIONS : Permissions::CREATE_PERMISSIONS,
            'You do not have permission to manage permissions.'
        );

        $this->resetValidation();
        $this->reset(['permissionId', 'name', 'guard_name', 'mode']);

        $this->mode = $mode;

        if ($mode === 'edit' && $id) {
            $permission = Permission::query()->findOrFail($id);

            $this->permissionId = $permission->id;
            $this->name = $permission->name;
            $this->guard_name = $permission->guard_name;
        }

        $this->dispatch('modal:show', modalId: 'permissionModal');
    }

    public function closeModal(): void
    {
        $this->dispatch('modal:hide', modalId: 'permissionModal');
    }

    public function save(): void
    {
        $this->authorizePermission(
            $this->permissionId ? Permissions::EDIT_PERMISSIONS : Permissions::CREATE_PERMISSIONS,
            'You do not have permission to save permissions.'
        );

        $this->validate();

        Permission::updateOrCreate(
            ['id' => $this->permissionId],
            [
                'name' => $this->name,
                'guard_name' => $this->guard_name,
            ]
        );

        $this->dispatch('modal:hide', modalId: 'permissionModal');
        $this->dispatch('permissions:refresh');
        session()->flash('message', $this->permissionId ? 'Permission updated!' : 'Permission created!');
    }

    public function render()
    {
        return view('livewire.sysadmin.spatie.permission-form');
    }
}