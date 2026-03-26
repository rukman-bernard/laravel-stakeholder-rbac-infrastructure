<?php

namespace App\Livewire\Sysadmin\Spatie;

use App\Constants\Permissions;
use App\Traits\AuthorizesWithPermissions;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UserForm extends Component
{
    use AuthorizesWithPermissions;

    public string $header_title = 'Permissions';
    public string $subtitle = '';

    public $user = null;
    public $name;
    public $email;
    public $password;
    public $roles = [];

    protected string $guard = 'web'; // 🔥 enforce guard

    public function mount(User $user): void
    {
        if ($user->id) {
            $this->authorizePermission(Permissions::EDIT_USERS, 'You do not have permission to edit users.');

            $this->user = $user;
            $this->name = $user->name;
            $this->email = $user->email;

            // Only roles from web guard
            $this->roles = $user->roles()
                ->where('guard_name', $this->guard)
                ->pluck('name')
                ->toArray();

            $this->subtitle = 'Edit';
        } else {
            $this->authorizePermission(Permissions::CREATE_USERS, 'You do not have permission to create users.');
            $this->subtitle = 'Create';
        }
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . ($this->user?->id ?? 'NULL')
            ],
            'password' => $this->user ? 'nullable|min:6' : 'required|min:6',

            // 🔥 validate roles belong to web guard
            'roles' => [
                'array',
                Rule::in($this->availableRoleNames())
            ]
        ];
    }

    public function availableRoleNames(): array
    {
        return \Spatie\Permission\Models\Role::where('guard_name', $this->guard)
            ->pluck('name')
            ->toArray();
    }

    public function save(): void
    {
        $this->authorizePermission(
            $this->user?->id ? Permissions::EDIT_USERS : Permissions::CREATE_USERS,
            'You do not have permission to save users.'
        );

        $this->validate();

        $user = $this->user ?? new User();

        $user->name = $this->name;
        $user->email = $this->email;

        if ($this->password) {
            $user->password = Hash::make($this->password);
        }

        $user->save();

        // 🔥 safe assignment (web guard only)
        $user->syncRoles($this->roles);

        session()->flash('message', 'User saved successfully!');

        $this->redirect(route('sysadmin.users'));
    }

    public function render()
    {
        $allRoles = \Spatie\Permission\Models\Role::where('guard_name', $this->guard)
            ->pluck('name', 'id');

        return view('livewire.sysadmin.spatie.user-form', [
            'allRoles' => $allRoles
        ]);
    }
}