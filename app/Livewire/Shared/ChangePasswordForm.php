<?php

namespace App\Livewire\Shared;

use App\Constants\Guards;
use App\Services\Auth\GuardResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

final class ChangePasswordForm extends Component
{
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    /**
     * Guard resolved from the current authenticated session (single-session model).
     */
    public string $guard = Guards::WEB;

    public function mount(GuardResolver $guardResolver): void
    {
        // Resolve the currently-authenticated guard deterministically.
        // If nothing is authenticated, fall back to web (or you can abort 401).
        $resolved = $guardResolver->detect(Guards::session());

        $this->guard = is_string($resolved) ? $resolved : Guards::WEB;
    }

    protected function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password:' . $this->guard],

            // Stronger rule than just min:8 (still fine if you want to keep min:8 only)
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    public function changePassword(): void
    {
        $this->validate();

        $user = Auth::guard($this->guard)->user();

        // Defensive: if guard resolution drifted or session expired mid-request
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $user->forceFill([
            'password' => Hash::make($this->new_password),
        ])->save();

        session()->flash('success', 'Password updated successfully!');
        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
    }

    public function render()
    {
        return view('livewire.shared.change-password-form');
    }
}