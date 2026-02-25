<?php

namespace App\Livewire\Shared;

use App\Constants\Guards;
use App\Services\Auth\GuardResolver;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

final class UserProfile extends Component
{
    public string $guard = Guards::WEB;

    public string $name = '';
    public string $email = '';
    public string $profileImageUrl = '';

    public function mount(GuardResolver $guardResolver): void
    {
        // Resolve the active guard from the single-session model.
        // If resolution fails, fall back to web.
        $identity = $guardResolver->identity();
        $this->guard = is_array($identity) && ! empty($identity['guard'])
            ? (string) $identity['guard']
            : Guards::WEB;

        $this->hydrateFromAuthUser();
    }

    /**
     * Keep this method as the only place that reads current user -> component state.
     * This avoids duplicated logic across mount/update/refresh.
     */
    private function hydrateFromAuthUser(): void
    {
        $user = $this->authUser();

        $this->name  = $user?->name ?? '';
        $this->email = $user?->email ?? '';

        // Uses the accessor on User/Student/Employer models (profile_image_url)
        $this->profileImageUrl = $user?->profile_image_url
            ?? asset('images/default-avatar.png');
    }

    private function authUser()
    {
        return Auth::guard($this->guard)->user();
    }

    protected function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    #[On('profile-photo-updated')]
    public function refreshProfilePhoto(): void
    {
        $this->hydrateFromAuthUser();
    }

    public function updateProfile(): void
    {
        $user = $this->authUser();

        if (! $user) {
            $this->addError('auth', 'Your session has expired. Please sign in again.');
            return;
        }

        $validated = $this->validate();

        // If you want email changes to require re-verification later,
        // this is where you'd add that logic.
        $user->forceFill($validated)->save();

        $this->hydrateFromAuthUser();

        session()->flash('success', 'Profile updated successfully.');
    }

    public function render()
    {
        return view('livewire.shared.user-profile');
    }
}