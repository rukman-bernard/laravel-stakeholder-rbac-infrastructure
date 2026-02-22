<div class="row justify-content-center">
    <div class="col-md-12">
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="card card-info shadow">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-camera mr-1"></i> Upload Profile Photo
                </h3>
            </div>

            <div class="card-body">
                <form wire:submit.prevent="photoUpload" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="photo">Choose a photo (Max: 1MB)</label>
                        <input id="photo" type="file" wire:model="photo" class="form-control-file" accept="image/*">
                        @error('photo')
                            <span class="text-danger d-block mt-1">{{ $message }}</span>
                        @enderror
                        @error('auth')
                            <span class="text-danger d-block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    @if ($photo)
                        <p class="mt-3 mb-2 font-weight-bold">Preview:</p>
                        <img src="{{ $photo->temporaryUrl() }}"
                             class="img-fluid shadow"
                             style="width: 300px; height: 300px; object-fit: cover;">
                    @endif

                    <button type="submit" class="btn btn-primary mt-3" @disabled(! $photo)>
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>