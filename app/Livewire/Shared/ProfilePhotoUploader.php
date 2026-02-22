<?php

namespace App\Livewire\Shared;

use App\Services\Auth\GuardResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProfilePhotoUploader extends Component
{
    use WithFileUploads;

    public $photo = null;
    public string $guard = 'web';

    public function mount(GuardResolver $guardResolver): void
    {
        $this->guard = $guardResolver->detect() ?? 'web';
    }

    public function photoUpload(): void
    {
        $this->validate([
            'photo' => ['required', File::image()->max(1024)],
        ]);

        $user = Auth::guard($this->guard)->user();

        if (! $user) {
            $this->addError('auth', 'Your session has expired. Please sign in again.');
            return;
        }

        // Delete old image if exists
        if ($user->image_path && Storage::disk('public')->exists($user->image_path)) {
            Storage::disk('public')->delete($user->image_path);
        }

        // Store new image (storage/app/public/avatars)
        $path = $this->photo->store('avatars', 'public');

        // Persist
        $user->forceFill(['image_path' => $path])->save();

        // Reset the upload input + preview
        $this->reset('photo');

        // Tell profile component to refresh image without full reload (optional UX improvement)
        $this->dispatch('profile-photo-updated');

        session()->flash('success', 'Profile photo updated!');
    }

    public function render()
    {
        return view('livewire.shared.profile-photo-uploader');
    }
}