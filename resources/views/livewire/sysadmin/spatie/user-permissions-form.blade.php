<div>
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Manage Roles & Permissions for {{ $user->name }}</h3>
        </div>

        <div class="card-body">
            @if (session()->has('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif

            <div class="row">
                <!-- Roles -->
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Roles</label>
                        <div class="row">
                            @foreach($roles as $role)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="checkbox"
                                               class="form-check-input"
                                               id="role-{{ $role->id }}"
                                               value="{{ $role->id }}"
                                               wire:model.live="selectedRoles">
                                        <label class="form-check-label" for="role-{{ $role->id }}">
                                            {{ $role->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Inherited Permissions -->
                <div class="col-md-6 d-flex">
                    <div class="form-group w-100 d-flex flex-column">
                        <label class="mb-1">Inherited Permissions (Read Only)</label>
                        <small class="form-text text-muted mb-3">
                            These permissions come from selected roles.
                        </small>

                        <div class="border rounded p-3 w-100 permission-box flex-grow-1">
                            @forelse($inheritedPermissions as $permission)
                                <span class="badge badge-info mb-2 mr-1">{{ $permission->name }}</span>
                            @empty
                                <p class="text-muted mb-0">No inherited permissions.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Direct Permissions -->
                <div class="col-md-6 d-flex">
                    <div class="form-group w-100 d-flex flex-column">
                        <label class="mb-1">Direct Permissions</label>
                        <small class="form-text text-muted mb-3">
                            Only permissions not inherited from roles are shown.
                        </small>

                        <div class="border rounded p-3 w-100 permission-box flex-grow-1">
                            @forelse($selectablePermissions as $permission)
                                <div class="form-check mb-2">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           id="perm-{{ $permission->id }}"
                                           value="{{ $permission->id }}"
                                           wire:model.live="selectedPermissions">
                                    <label class="form-check-label" for="perm-{{ $permission->id }}">
                                        {{ $permission->name }}
                                    </label>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No direct permissions available.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <button wire:click="save" class="btn btn-primary">Save</button>
            <a href="{{ route('sysadmin.users') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <style>
        .permission-box {
            height: 320px;
            min-height: 320px;
            max-height: 320px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .permission-box .form-check {
            min-height: 24px;
        }

        .permission-box .badge {
            font-size: 0.85rem;
        }
    </style>
</div>