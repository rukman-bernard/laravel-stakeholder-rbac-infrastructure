<?php

namespace App\Livewire\Shared;

use App\Constants\Guards;
use App\Services\Auth\GuardResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Livewire\Component;
use Livewire\WithFileUploads;

final class ProfilePhotoUploader extends Component
{
    use WithFileUploads;

    /**
     * Livewire temporary uploaded file.
     *
     * @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null
     */
    public $photo = null;

    /**
     * Resolved guard for the current authenticated session (single-session model).
     */
    public string $guard = Guards::WEB;

    public function mount(GuardResolver $guardResolver): void
    {
        // Resolve the active authenticated guard deterministically.
        $resolved = $guardResolver->detect(Guards::session());
        $this->guard = is_string($resolved) ? $resolved : Guards::WEB;
    }

    protected function rules(): array
    {
        return [
            // 1024 KB = 1 MB (same as your original)
            'photo' => ['required', File::image()->max(1024)],
        ];
    }

    public function uploadPhoto(): void
    {
        $this->validate();

        $user = Auth::guard($this->guard)->user();

        if (! $user) {
            $this->addError('auth', 'Your session has expired. Please sign in again.');
            return;
        }

        // Store first (safer). If storage fails, we won't delete the old image.
        $newPath = $this->photo->store('avatars', 'public');

        // Remember the old image for cleanup after DB update
        $oldPath = $user->image_path;

        // Persist the new path
        $user->forceFill(['image_path' => $newPath])->save();

        // Best-effort cleanup of old image
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        // Reset upload input (clears preview)
        $this->reset('photo');

        // Let any listening components refresh UI (e.g., navbar avatar)
        $this->dispatch('profile-photo-updated');

        session()->flash('success', 'Profile photo updated!');
    }

    public function render()
    {
        return view('livewire.shared.profile-photo-uploader');
    }
}