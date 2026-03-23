<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-camera mr-1"></i> Update Profile Photo
        </h3>
    </div>

    <form wire:submit.prevent="uploadPhoto" enctype="multipart/form-data">
        <div class="card-body">

            {{-- File Input --}}
            <div class="form-group mb-4">
                <label for="photo">Choose a photo</label>

                <input
                    id="photo"
                    type="file"
                    wire:model="photo"
                    class="form-control-file @error('photo') is-invalid @enderror"
                    accept="image/*"
                >

                <small class="form-text text-muted">
                    Max size: 1MB
                </small>

                @error('photo')
                    <span class="text-danger d-block mt-1">{{ $message }}</span>
                @enderror

                @error('auth')
                    <span class="text-danger d-block mt-1">{{ $message }}</span>
                @enderror
            </div>

            {{-- Preview (Stable, No Layout Shift) --}}
            <div class="text-center">
                <p class="mb-2 font-weight-bold">Preview</p>

                <div
                    class="position-relative d-inline-block"
                    style="width: 120px; height: 120px;"
                >
                    <img
                        src="{{ $photo ? $photo->temporaryUrl() : asset('images/default-avatar.png') }}"
                        alt="Profile Preview"
                        class="img-circle elevation-2 shadow"
                        style="width: 120px; height: 120px; object-fit: cover;"
                    >

                    <div
                        wire:loading.flex
                        wire:target="photo"
                        class="align-items-center justify-content-center"
                        style="
                            display: none;
                            position: absolute;
                            inset: 0;
                            z-index: 10;
                            border-radius: 50%;
                            background: rgba(255, 255, 255, 0.35);
                        "
                    >
                        <i class="fas fa-spinner fa-spin text-primary"></i>
                    </div>
                </div>
            </div>

        </div>

        <div class="card-footer">
            <button
                type="submit"
                class="btn btn-primary"
                @disabled(! $photo)
                wire:loading.attr="disabled"
                wire:target="uploadPhoto"
            >
                <span wire:loading.remove wire:target="uploadPhoto">
                    <i class="fas fa-upload mr-1"></i> Upload Photo
                </span>

                <span wire:loading wire:target="uploadPhoto">
                    <i class="fas fa-spinner fa-spin mr-1"></i> Uploading...
                </span>
            </button>
        </div>
    </form>
</div>