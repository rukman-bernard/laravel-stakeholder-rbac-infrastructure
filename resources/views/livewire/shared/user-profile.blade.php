<div class="row justify-content-center">
    <div class="col-md-8">
        @if (session()->has('success'))
            <x-adminlte-alert theme="success" title="Success" dismissable>
                {{ session('success') }}
            </x-adminlte-alert>
        @endif

        <div class="card card-widget widget-user">
            <div class="widget-user-header bg-gradient-primary">
                <h3 class="widget-user-username">{{ $name }}</h3>
                <h5 class="widget-user-desc">{{ ucfirst($guard) }} User</h5>
            </div>

            <div class="widget-user-image">
                <img
                    class="img-circle elevation-2"
                    src="{{ $profileImageUrl }}"
                    alt="User Avatar"
                    style="object-fit: cover; width: 90px; height: 90px;"
                >
            </div>

            <div class="card-footer">
                <div class="row">
                    <div class="col-sm-12">
                        <form wire:submit.prevent="updateProfile">
                            <div class="form-group">
                                <label for="profile_name">Name</label>
                                <input
                                    id="profile_name"
                                    type="text"
                                    wire:model.live="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                >
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="profile_email">Email</label>
                                <input
                                    id="profile_email"
                                    type="email"
                                    wire:model.live="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                >
                                @error('email')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8 mt-3">
        <livewire:shared.profile-photo-uploader />
    </div>
</div>