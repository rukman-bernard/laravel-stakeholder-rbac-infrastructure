<?php

namespace App\Livewire\Sysadmin\Spatie;

use App\Constants\Permissions;
use App\Traits\AuthorizesWithPermissions;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

final class PermissionIndex extends Component
{
    use AuthorizesWithPermissions;

    public string $header_title = 'Permissions';
    public string $subtitle = 'Index';

    public array $permissions = [];

    protected $listeners = [
        'permissions:refresh' => 'loadPermissions',
    ];

    public function mount(): void
    {
        $this->loadPermissions();
    }

    public function loadPermissions(): void
    {
        $this->permissions = Permission::query()->orderBy('id')->get()->all();
    }

    public function create(): void
    {
        $this->authorizePermission(Permissions::CREATE_PERMISSIONS, 'You do not have permission to create permissions.');

        // Tell the form to open in create mode
        $this->dispatch('permission:open', mode: 'create');
    }

    public function edit(int $id): void
    {
        $this->authorizePermission(Permissions::EDIT_PERMISSIONS, 'You do not have permission to edit permissions.');

        // Tell the form to open in edit mode
        $this->dispatch('permission:open', mode: 'edit', id: $id);
    }

    public function delete(int $id): void
    {
        $this->authorizePermission(Permissions::DELETE_PERMISSIONS, 'You do not have permission to delete permissions.');

        Permission::query()->whereKey($id)->delete();
        $this->loadPermissions();
    }

    public function render()
    {
        $this->authorizePermission(Permissions::VIEW_PERMISSIONS, 'You do not have permission to view permissions.');

        return view('livewire.sysadmin.spatie.permission-index');
    }
}