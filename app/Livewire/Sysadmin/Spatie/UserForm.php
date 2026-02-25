<?php

namespace App\Livewire\Sysadmin\Spatie;

use App\Constants\Permissions;
use App\Traits\AuthorizesWithPermissions;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\Attributes\Layout;

// #[Layout('sysadmin.users')]
class UserForm extends Component
{
    use AuthorizesWithPermissions;

    public string $header_title = 'Permissions';
    public string $subtitle = '';


    public $user = null; // Null if creating a new user
    public $name;
    public $email;
    public $password;
    public $roles = [];


    // You can pass an existing user from another component or route
    public function mount(User $user): void
    {
        if ($user->id) {
            $this->authorizePermission(Permissions::EDIT_USERS, 'You do not have permission to edit users.');

            $this->user = $user;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->roles = $user->roles()->pluck('name')->toArray(); // If using Spatie
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
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . ($this->user?->id ?? 'NULL')],
            'password' => $this->user ? 'nullable|min:6' : 'required|min:6',
            'roles' => 'array'
        ];
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

        // Assign roles (if using Spatie)
        $user->syncRoles($this->roles);

        session()->flash('message', 'User saved successfully!');

        // Optional: Redirect or emit event
        $this->redirect(route('sysadmin.users')); // or $this->dispatch('user-saved');
    }

    public function render()
    {
        // Pass roles from DB (if using Spatie)
        $allRoles = \Spatie\Permission\Models\Role::pluck('name', 'id');

        
        return view('livewire.sysadmin.spatie.user-form', [
            'allRoles' => $allRoles
        ]);
    }
}
