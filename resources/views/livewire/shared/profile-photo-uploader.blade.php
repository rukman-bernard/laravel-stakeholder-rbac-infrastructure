<form wire:submit.prevent="uploadPhoto" enctype="multipart/form-data">
    <div class="form-group">
        <label for="photo">Choose a photo (Max: 1MB)</label>

        <input
            id="photo"
            type="file"
            wire:model="photo"
            class="form-control-file"
            accept="image/*"
        >

        <div wire:loading wire:target="photo" class="text-muted mt-2">
            Reading image...
        </div>

        @error('photo')
            <span class="text-danger d-block mt-1">{{ $message }}</span>
        @enderror

        @error('auth')
            <span class="text-danger d-block mt-1">{{ $message }}</span>
        @enderror
    </div>

    @if ($photo)
        <p class="mt-3 mb-2 font-weight-bold">Preview:</p>
        <img
            src="{{ $photo->temporaryUrl() }}"
            class="img-fluid shadow"
            style="width: 300px; height: 300px; object-fit: cover;"
            alt="Profile photo preview"
        >
    @endif

    <button
        type="submit"
        class="btn btn-primary mt-3"
        @disabled(! $photo)
        wire:loading.attr="disabled"
        wire:target="uploadPhoto,photo"
    >
        <span wire:loading.remove wire:target="uploadPhoto">
            <i class="fas fa-upload"></i> Upload
        </span>

        <span wire:loading wire:target="uploadPhoto">
            <i class="fas fa-spinner fa-spin"></i> Uploading...
        </span>
    </button>
</form>