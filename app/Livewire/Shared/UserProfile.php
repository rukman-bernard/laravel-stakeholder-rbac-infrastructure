<?php

namespace App\Livewire\Shared;

use App\Services\Auth\GuardResolver;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class UserProfile extends Component
{
    public string $name = '';
    public string $email = '';
    public string $profile_image_url = '';
    public string $guard = 'web';

    public function mount(GuardResolver $guardResolver): void
    {
        ['guard' => $guard] = $guardResolver->identity();
        $this->guard = $guard ?? $this->guard;

        $this->loadUser();
    }

    private function loadUser(): void
    {
        $user = Auth::guard($this->guard)->user();

        $this->name = $user?->name ?? '';
        $this->email = $user?->email ?? '';

        // ✅ Use accessor (works for both User + Student consistently)
        $this->profile_image_url = $user?->profile_image_url
            ?? asset('images/default-avatar.png');
    }

    #[On('profile-photo-updated')]
    public function refreshAfterPhotoUpload(): void
    {
        $this->loadUser();
    }

    public function updateProfile(): void
    {
        $user = Auth::guard($this->guard)->user();

        if (! $user) {
            $this->addError('auth', 'Your session has expired. Please sign in again.');
            return;
        }

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
        ]);

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        // Reload so UI always reflects DB source-of-truth
        $this->loadUser();

        session()->flash('success', 'Profile updated successfully.');
    }

    public function render()
    {
        return view('livewire.shared.user-profile');
    }
}